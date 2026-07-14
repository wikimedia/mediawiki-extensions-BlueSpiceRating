<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingMigrateRatedCommentsScript extends LoadExtensionSchemaUpdates {

	/**
	 * @inheritDoc
	 */
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance( \BSRatingMigrateRatedComments::class );
		return true;
	}

}
