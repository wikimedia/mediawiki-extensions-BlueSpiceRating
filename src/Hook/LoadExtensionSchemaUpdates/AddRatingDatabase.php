<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingDatabase extends LoadExtensionSchemaUpdates {
	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_rating',
			"$dir/maintenance/db/sql/$dbType/rating-generated.sql"
		);

		if ( $dbType == 'mysql' ) {
			$this->updater->addExtensionField(
				'bs_rating',
				'rat_subtype',
				"$dir/maintenance/db/bs_rating.newfield.rat_subtype.sql"
			);
			$this->updater->addExtensionField(
				'bs_rating',
				'rat_context',
				"$dir/maintenance/db/bs_rating.newfield.rat_context.sql"
			);
		}
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}
