<?php

namespace BlueSpice\Rating\Hook;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerBeforePersistSettingsHook;

class WriteNamespaceConfiguration implements NamespaceManagerBeforePersistSettingsHook {

	/**
	 * @inheritDoc
	 */
	public function onNamespaceManagerBeforePersistSettings(
		array &$configuration, int $id, array $definition, array $mwGlobals
	): void {
		$this->writeConfiguration(
			"RatingArticleEnabledNamespaces", "rating", $configuration, $id, $definition, $mwGlobals
		);
		$this->writeConfiguration(
			"RatingArticleLikeEnabledNamespaces", "recommendations", $configuration, $id, $definition, $mwGlobals
		);
	}

	/**
	 * @param string $bsgGlobal
	 * @param string $settingName
	 * @param array &$configuration
	 * @param int $id
	 * @param array $definition
	 * @param array $mwGlobals
	 *
	 * @return void
	 */
	private function writeConfiguration(
		string $bsgGlobal, string $settingName, array &$configuration,
		int $id, array $definition, array $mwGlobals
	) {
		$enabledNamespaces = $mwGlobals["bsg$bsgGlobal"] ?? [];
		$currentlyActivated = in_array( $id, $enabledNamespaces );

		$explicitlyDeactivated = false;
		if ( isset( $definition[$settingName] ) && $definition[$settingName] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $definition[$settingName] ) && $definition[$settingName] === true ) {
			$explicitlyActivated = true;
		}

		if ( ( $currentlyActivated && !$explicitlyDeactivated ) || $explicitlyActivated ) {
			$configuration["bsg$bsgGlobal"][] = $id;
		}
	}
}
