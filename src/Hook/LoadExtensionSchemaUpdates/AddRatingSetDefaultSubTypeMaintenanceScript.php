<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingSetDefaultSubTypeMaintenanceScript extends LoadExtensionSchemaUpdates {

	/**
	 * @inheritDoc
	 */
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance( \BSRatingSetDefaultSubType::class );
		return true;
	}

}
