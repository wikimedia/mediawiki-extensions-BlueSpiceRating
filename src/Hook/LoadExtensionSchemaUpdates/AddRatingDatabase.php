<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingDatabase extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$dir = $this->getExtensionPath().'/maintenance/db';

		$this->updater->addExtensionTable(
			'bs_rating',
			"$dir/rating.sql"
		);
		$this->updater->addExtensionField(
			'bs_rating',
			'rat_subtype',
			"$dir/bs_rating.newfield.rat_subtype.sql"
		);
		$this->updater->addExtensionField(
			'bs_rating',
			'rat_context',
			"$dir/bs_rating.newfield.rat_context.sql"
		);
		return true;
	}

	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}