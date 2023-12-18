<?php

namespace BlueSpice\Rating\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use MediaWiki\MediaWikiServices;

class AddResources extends BeforePageDisplay {

	protected function skipProcessing() {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$enabledNamespaces = $config->get( 'RatingArticleEnabledNamespaces' );
		if ( !in_array( $this->out->getTitle()->getNamespace(), $enabledNamespaces ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.rating.icons' );

		$scripts = $styles = [];
		$registry = $this->getServices()->getService( 'BSRatingRegistry' );
		$configFactory = $this->getServices()->getService(
			'BSRatingConfigFactory'
		);
		foreach ( $registry->getRegisterdTypeKeys() as $key ) {
			$config = $configFactory->newFromType( $key );
			$ratingConfigStyles = $config->get( 'ModuleStyles' );
			if ( $ratingConfigStyles ) {
				$styles = array_merge( $styles, $ratingConfigStyles );
			}
			$ratingConfigScripts = $config->get( 'ModuleScripts' );
			if ( $ratingConfigScripts ) {
				$scripts = array_merge( $scripts, $ratingConfigScripts );
			}
		}

		// Make sure to have arrays in JS!
		$scripts = array_values( array_unique( $scripts ) );
		$styles = array_values( array_unique( $styles ) );

		if ( !empty( $scripts ) ) {
			$this->out->addModules( $scripts );
		}
		if ( !empty( $styles ) ) {
			$this->out->addModuleStyles( $styles );
		}

		return true;
	}
}
