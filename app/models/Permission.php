<?php namespace uk\co\la1tv\website\models;

class Permission extends MyEloquent {

	protected $table = 'permissions';
	protected $fillable = array('id', 'name', 'description');

	public function groups() {
		return $this->belongsToMany(self::$p.'PermissionGroup', 'permission_to_group', 'permission_id', 'group_id')->withPivot('permission_flag');
	}
}