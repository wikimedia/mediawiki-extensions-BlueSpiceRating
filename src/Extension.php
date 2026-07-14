<?php

namespace BlueSpice\Rating;

class Extension extends \BlueSpice\Extension {

	/**
	 * Hook handler for BSMigrateSettingsFromDeviatingNames
	 * @param string $oldName
	 * @param string &$newName
	 * @return bool
	 */
	public static function onBSMigrateSettingsFromDeviatingNames( $oldName, &$newName ) {
		if ( $oldName === "MW::Rating::enRatingNS" ) {
			$newName = "RatingArticleEnabledNamespaces";
		}
		if ( $oldName === "MW::Rating::enArticleLikeNS" ) {
			$newName = "RatingArticleLikeEnabledNamespaces";
		}
		return true;
	}
}
