<?php namespace uk\co\la1tv\website\controllers\home\shows;

use uk\co\la1tv\website\controllers\home\HomeBaseController;
use View;
use Config;
use Carbon;
use URL;
use App;
use uk\co\la1tv\website\models\Show;

class ShowsController extends HomeBaseController {

	public function getIndex($pageNo=0) {
		$pageNo = intval($pageNo);
		
		$itemsPerPage = intval(Config::get("custom.num_shows_per_page"));
		
		$itemOffset = $pageNo*$itemsPerPage;
		
		$numPlaylists = Show::accessible()->count();
		$numPages = ceil($numPlaylists/$itemsPerPage);
		$shows = Show::accessible()->orderBy("name", "asc")->orderBy("description", "asc")->skip($itemOffset)->take($itemsPerPage)->get();
		
		if ($pageNo > 0 && $shows->count() === 0) {
			App::abort(404);
		}
		
		$coverArtResolutions = Config::get("imageResolutions.coverArt");
		
		$playlistTableData = array();
		
		foreach($shows as $i=>$item) {
			$thumbnailUri = $item->getCoverArtUri($coverArtResolutions['thumbnail']['w'], $coverArtResolutions['thumbnail']['h']);
			
			$playlistTableData[] = array(
				"uri"					=> $item->getUri(),
				"title"					=> $item->name,
				"escapedDescription"	=> !is_null($item->description) ? e($item->description) : null,
				"playlistName"			=> null,
				"episodeNo"				=> null,
				"thumbnailUri"			=> $thumbnailUri,
				"thumbnailFooter"		=> null,
				"active"				=> false
			);
		}
		
		$playlistFragment = View::make("fragments.home.playlist", array(
			"stripedTable"	=> true,
			"headerRowData"	=> null,
			"tableData"		=> $playlistTableData
		));
		
		$pageNumbers = array();
		for ($i=0; $i<$numPages; $i++) {
			$pageNumbers[] = array(
				"num"		=> $i+1,
				"uri"		=> URL::route("shows", array($i)),
				"active"	=> $i === $pageNo
			);
		}
		
		$openGraphProperties = array();
		$openGraphProperties[] = array("name"=> "video:release_date", "content"=> null);
		foreach($playlistTableData as $a) {
			$openGraphProperties[] = array("name"=> "og:see_also", "content"=> $a['uri']);
		}
		
		$view = View::make("home.shows.index");
		$view->playlistFragment = $playlistFragment;
		$view->pageSelectorFragment = View::make("fragments.home.pageSelector", array(
			"nextUri" 	=> $pageNo < $numPages-1 ? URL::route("shows", array($pageNo+1)) : null,
			"prevUri"	=> $pageNo > 0 ? URL::route("shows", array($pageNo-1)) : null,
			"numbers"	=> $pageNumbers
		));
		$this->setContent($view, "shows", "shows", $openGraphProperties);
	}
	
	public function missingMethod($parameters=array()) {
		// redirect /[integer]/[anything] to /index/[integer]/[anything]
		if (count($parameters) >= 1 && ctype_digit($parameters[0])) {
			return call_user_func_array(array($this, "getIndex"), $parameters);
		}
		else {
			return parent::missingMethod($parameters);
		}
	}
}
