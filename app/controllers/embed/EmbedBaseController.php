<?php namespace uk\co\la1tv\website\controllers\embed;

use uk\co\la1tv\website\controllers\BaseController;
use URL;
use Csrf;
use Config;
use Facebook;
use DebugHelpers;

class EmbedBaseController extends BaseController {

	protected $layout = "layouts.embed.master";
	
	protected function setContent($content, $cssPageId, $title=NULL) {
		$this->layout->baseUrl = URL::to("/");
		$this->layout->cssPageId = $cssPageId;
		$this->layout->title = !is_null($title) ? $title : "LA1:TV";
		$this->layout->description = "";
		$this->layout->content = $content;
		$this->layout->allowRobots = false;
		$this->layout->cssBootstrap = asset("assets/css/bootstrap/embed.css");
		$this->layout->requireJsBootstrap = asset("assets/scripts/bootstrap/embed.js");	
		$this->layout->pageData = array(
			"baseUrl"		=> URL::to("/"),
			"cookieDomain"	=> Config::get("cookies.domain"),
			"cookieSecure"	=> Config::get("ssl.enabled"),
			"assetsBaseUrl"	=> asset(""),
			"logUri"		=> Config::get("custom.log_uri"),
			"debugId"		=> DebugHelpers::getDebugId(),
			"csrfToken"		=> Csrf::getToken(),
			"loggedIn"		=> Facebook::isLoggedIn(),
			"gaEnabled"		=> Config::get("googleAnalytics.enabled")
		);
	}

}
