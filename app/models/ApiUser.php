<?php namespace uk\co\la1tv\website\models;

class ApiUser extends MyEloquent {

	protected $table = 'api_users';
	protected $fillable = array('enabled', 'owner', 'information', 'key', 'can_view_stream_uris', 'can_view_vod_uris', 'can_use_webhooks', 'webhook_url', 'last_request_time');
	
	public function canViewVodUris() {
		return (boolean) $this->can_view_vod_uris;
	}
	
	public function canViewStreamUris() {
		return (boolean) $this->can_view_stream_uris;
	}

	public function canUseWebhooks() {
		return (boolean) $this->can_use_webhooks;
	}

	public function scopeSearch($q, $value) {
		return $value === "" ? $q : $q->whereContains(array("owner", "information"), $value);
	}

	public function getDates() {
		return array_merge(parent::getDates(), array('last_request_time'));
	}
}