<?php

namespace BlueSpice\Rating;

use MediaWiki\MediaWikiServices;

class ClientConfig {

	/**
	 * @return array
	 */
	public static function makeConfigJson(): array {
		$services = MediaWikiServices::getInstance();
		$config = $services->getConfigFactory()->makeConfig( 'bsg' );

		$configs = $scripts = [];
		$registry = $services->getService( 'BSRatingRegistry' );
		$configFactory = $services->getService(
			'BSRatingConfigFactory'
		);
		foreach ( $registry->getRegisterdTypeKeys() as $key ) {
			$config = $configFactory->newFromType( $key );
			$configs[$key] = $config->jsonSerialize();
			$ratingConfigScripts = $config->get( 'ModuleScripts' );
			if ( $ratingConfigScripts ) {
				$scripts = array_merge( $scripts, $ratingConfigScripts );
			}
		}
		$scripts = array_values( array_unique( $scripts ) );

		return [
			'ratingConfig' => $configs,
			'ratingModules' => $scripts
		];
	}
}
