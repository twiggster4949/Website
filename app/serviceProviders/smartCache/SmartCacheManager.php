<?php namespace uk\co\la1tv\website\serviceProviders\smartCache;

use Cache;
use Carbon;
use Event;
use Queue;
use Redis;
use Closure;
use Config;
use malkusch\lock\mutex\PredisMutex;
use Jeremeamia\SuperClosure\SerializableClosure;

class SmartCacheManager {
	
	// if the object is cached and not old return cached version.
	// otherwise cache object and return it
	// $forceRefresh will force cache to be updated in the current request
	// if it is older than half of the timeout period.
	// The cache will automatically be update in the background if it is
	// older than half the timeout period.
	public function get($key, $seconds, $closure, $forceRefresh=false) {
		// the first time the : must appear must be straight before $key
		// otherwise there could be conflicts
		$keyStart = "smartCache";
		$fullKey = $keyStart . ":" . $key;
	
		if (!$forceRefresh) {
			// if there is already a version in the cache return it
			// synchronisation only needed to update cache not read it
			$responseAndTime = $this->getResponseAndTime($fullKey, $seconds);
			if (!is_null($responseAndTime)) {
				if (!$this->shouldForceRefresh($responseAndTime, $seconds)) {
					// just return cached version
					return $responseAndTime["response"];
				}
			}
		}

		// a cache update is needed. this needs to be synchronized so only one process is updating the cache
		$mutex = new PredisMutex([Redis::connection()], $fullKey, Config::get("predisMutex.timeout"));
		return $mutex->synchronized(function() use (&$fullKey, &$forceRefresh, &$seconds, &$key, &$closure) {
			// get an updated cached version now in synchronized block
			$responseAndTime = $this->getResponseAndTime($fullKey, $seconds);
			if ($forceRefresh && !is_null($responseAndTime)) {
				$forceRefresh = $this->shouldForceRefresh($responseAndTime, $seconds);
			}
			
			if (!is_null($responseAndTime)) {
				if (Carbon::now()->timestamp - $responseAndTime["time"] > $seconds / 2) {
					// refresh the cache in the background as > half the time has passed
					// before a refresh would be required
					// the app.finish event is fired after the response has been returned to the user.
					Event::listen('app.finish', function() use (&$key, &$seconds, &$closure) {
						Queue::push("uk\co\la1tv\website\serviceProviders\smartCache\SmartCacheQueueJob", [
							"key"			=> $key,
							"seconds"		=> $seconds,
							"closure"		=> serialize(new SerializableClosure($closure))
						]);
					});
				}
			}

			if (is_null($responseAndTime) || $forceRefresh) {
				$responseAndTime = [
					"time"		=> Carbon::now()->timestamp,
					"response"	=> $closure()
				];
				// the cache driver only works in minutes
				Cache::put($fullKey, $responseAndTime, ceil($seconds/60));
			}
			return $responseAndTime["response"];
		});
	}

	private function getResponseAndTime($key, $seconds) {
		// get the cached version if there is one
		$responseAndTime = Cache::get($key, null);
		if (!is_null($responseAndTime)) {
			// check it hasn't expired
			// cache driver only works in minutes which is why this is necessary
			if ($responseAndTime["time"] < Carbon::now()->timestamp - $seconds) {
				// it's expired. pretend it's not in the cache
				$responseAndTime = null;
			}
		}
		return $responseAndTime;
	}

	private function shouldForceRefresh($responseAndTime, $seconds) {
		return !(Carbon::now()->timestamp - $responseAndTime["time"] <= $seconds / 2);
	}
}