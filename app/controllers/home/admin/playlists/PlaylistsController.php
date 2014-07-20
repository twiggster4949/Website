<?php namespace uk\co\la1tv\website\controllers\home\admin\playlists;

use View;

class PlaylistsController extends PlaylistsBaseController {

	public function getIndex() {
		$this->setContent(View::make('home.admin.playlists.index'), "playlists", "playlists");
	}
	
	public function anyEdit($id=null) {
		$this->setContent(View::make('home.admin.playlists.edit'), "playlists", "playlists-edit");
	}
}
