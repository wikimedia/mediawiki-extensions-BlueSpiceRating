<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingRemoveArchivedMaintenanceScript extends LoadExtensionSchemaUpdates {

	/**
	 * @inheritDoc
	 */
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance( \BSRatingRemoveArchived::class );
		return true;
	}

}
