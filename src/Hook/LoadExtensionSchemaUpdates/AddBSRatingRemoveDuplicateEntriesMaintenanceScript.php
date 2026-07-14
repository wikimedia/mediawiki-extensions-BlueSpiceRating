<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddBSRatingRemoveDuplicateEntriesMaintenanceScript extends LoadExtensionSchemaUpdates {

	/**
	 * @inheritDoc
	 */
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance( \BSRatingRemoveDuplicateEntries::class );
		return true;
	}

}
