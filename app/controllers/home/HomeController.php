<?php namespace uk\co\la1tv\website\controllers\home;

use View;
use uk\co\la1tv\website\models\MediaItem;
use Carbon;
use Auth;
use PlayerHelpers;
use Config;

class HomeController extends HomeBaseController {

	public function getIndex() {
	
		$promoMediaItem = MediaItem::find(15); // TODO get this from somewhere
		if (!$promoMediaItem->getIsAccessible()) {
			// shouldn't be accessible
			$promoMediaItem = null;
		}
		else {
			$liveStreamItem = $promoMediaItem->liveStreamItem;
			if (!is_null($liveStreamItem) && !$liveStreamItem->getIsAccessible()) {
				$liveStreamItem = null;
			}
			$videoItem = $promoMediaItem->videoItem;
			if (!is_null($videoItem) && !$videoItem->getIsAccessible()) {
				$videoItem = null;
			}

			$shouldShowItem = false;
			// if there is a live stream which is in the "not live" state the player won't display the vod
			// even if there is one. It will show the countdown to the start of the live stream.
			if (is_null($liveStreamItem) || !$liveStreamItem->isNotLive()) {
				if (!is_null($videoItem) && $videoItem->getIsLive()) {
					$shouldShowItem = true;
				}
				else if (!is_null($liveStreamItem) && $liveStreamItem->hasWatchableContent()) {
					$shouldShowItem = true;
				}
			}
			if (!$shouldShowItem) {
				$promoMediaItem = null;
			}
		}

		$promoPlaylist = null;
		if (!is_null($promoMediaItem)) {
			$promoPlaylist = $promoMediaItem->getDefaultPlaylist();
		}

		$promotedItems = MediaItem::getCachedPromotedItems();
		$promotedItemsData = array();

		// if there is an item to promote insert it at the start of the carousel
		if (!is_null($promoMediaItem)) {
			$coverArtResolutions = Config::get("imageResolutions.coverArt");
			$isLiveShow = !is_null($promoMediaItem->liveStreamItem) && !$promoMediaItem->liveStreamItem->isOver();
			$liveNow = $isLiveShow && $promoMediaItem->liveStreamItem->isLive();
			$promotedItemsData[] = array(
				"coverArtUri"	=> $promoPlaylist->getMediaItemCoverArtUri($promoMediaItem, $coverArtResolutions['full']['w'], $coverArtResolutions['full']['h']),
				"name"			=> $promoMediaItem->name,
				"seriesName"	=> !is_null($promoPlaylist->show) ? $promoPlaylist->generateName() : null,
				"availableMsg"	=> $liveNow ? "Live Now!" : $this->buildTimeStr($isLiveShow, $promoMediaItem->scheduled_publish_time),
				"uri"			=> $promoPlaylist->getMediaItemUri($promoMediaItem)
			);
		}
		
		foreach($promotedItems as $a) {
			$mediaItem = $a['mediaItem'];
			if (!is_null($promoMediaItem) && intval($mediaItem->id) === intval($promoMediaItem->id)) {
				// prevent duplicate
				continue;
			}
			$isLiveShow = !is_null($mediaItem->liveStreamItem) && !$mediaItem->liveStreamItem->isOver();
			$liveNow = $isLiveShow && $mediaItem->liveStreamItem->isLive();
			$promotedItemsData[] = array(
				"coverArtUri"	=> $a['coverArtUri'],
				"name"			=> $mediaItem->name,
				"seriesName"	=> $a['seriesName'],
				"availableMsg"	=> $liveNow ? "Live Now!" : $this->buildTimeStr($isLiveShow, $mediaItem->scheduled_publish_time),
				"uri"			=> $a['uri']
			);
		}
		
		$coverArtResolutions = Config::get("imageResolutions.coverArt");
		
		$recentlyAddedItems = MediaItem::getCachedRecentItems();
		$recentlyAddedTableData = array();
		foreach($recentlyAddedItems as $i=>$a) {
			$mediaItem = $a['mediaItem'];
			$recentlyAddedTableData[] = array(
				"uri"					=> $a['uri'],
				"active"				=> false,
				"title"					=> $mediaItem->name,
				"escapedDescription"	=> null,
				"playlistName"			=> $a['playlistName'],
				"episodeNo"				=> $i+1,
				"thumbnailUri"			=> $a['coverArtUri'],
				"thumbnailFooter"		=> null
			);
		}
		
		$mostPopularItems = MediaItem::getCachedMostPopularItems();
		$mostPopularTableData = array();
		foreach($mostPopularItems as $i=>$a) {
			$mediaItem = $a['mediaItem'];
			$mostPopularTableData[] = array(
				"uri"					=> $a['uri'],
				"active"				=> false,
				"title"					=> $mediaItem->name,
				"escapedDescription"	=> null,
				"playlistName"			=> $a['playlistName'],
				"episodeNo"				=> $i+1,
				"thumbnailUri"			=> $a['coverArtUri'],
				"thumbnailFooter"		=> null
			);
		}
		
		$view = View::make("home.index");
		
		$view->promotedItemsData = $promotedItemsData;
		$view->recentlyAddedPlaylistFragment = count($recentlyAddedTableData) > 0 ? View::make("fragments.home.playlist", array(
			"stripedTable"	=> true,
			"headerRowData"	=> null,
			"tableData"		=> $recentlyAddedTableData
		)) : null;
		$view->mostPopularPlaylistFragment = count($mostPopularTableData) > 0 ? View::make("fragments.home.playlist", array(
			"stripedTable"	=> true,
			"headerRowData"	=> null,
			"tableData"		=> $mostPopularTableData
		)) : null;
		$view->twitterWidgetId = Config::get("twitter.timeline_widget_id");

		$hasPromoItem = !is_null($promoMediaItem);
		$view->hasPromoItem = $hasPromoItem;
		if ($hasPromoItem) {
			$userHasMediaItemsPermission = false;
			if (Auth::isLoggedIn()) {
				$userHasMediaItemsPermission = Auth::getUser()->hasPermission(Config::get("permissions.mediaItems"), 0);
			}
			$view->promoPlayerInfoUri = PlayerHelpers::getInfoUri($promoPlaylist->id, $promoMediaItem->id);
			$view->promoRegisterWatchingUri = PlayerHelpers::getRegisterWatchingUri($promoPlaylist->id, $promoMediaItem->id);
			$view->promoRegisterLikeUri = PlayerHelpers::getRegisterLikeUri($promoPlaylist->id, $promoMediaItem->id);
			$view->promoAdminOverrideEnabled = $userHasMediaItemsPermission;
			$view->promoLoginRequiredMsg = "Please log in to use this feature.";
		}

		$this->setContent($view, "home", "home", array(), null, 200, array());
	}
	
	private function buildTimeStr($isLive, $time) {
		$liveStr = $isLive ? "Live" : "Available";
		
		if ($time->isPast()) {
			if (!$isLive) {
				return "Available On Demand Now";
			}
			else {
				return "Live Shortly";
			}
		}
		else if ($time->isToday()) {
			return $liveStr." Today at ".$time->format("H:i");
		}
		else if ($time->isTomorrow()) {
			return $liveStr." Tomorrow at ".$time->format("H:i");
		}
		else if (Carbon::now()->addYears(1)->timestamp <= $time->timestamp) {
			return "Coming Soon";
		}
		return $liveStr." at ".$time->format("H:i")." on ".$time->format("jS F");
	}
}
