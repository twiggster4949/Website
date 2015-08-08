<?php namespace uk\co\la1tv\website\helpers\reorderableList;

use uk\co\la1tv\website\models\MediaItemProductionRole;

class MediaItemCreditsReorderableList extends CreditsReorderableList {

	protected function getProductionRoleModel() {
		return new MediaItemProductionRole();
	}
	
}