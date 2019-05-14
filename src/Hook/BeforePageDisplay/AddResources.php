<?php

namespace BlueSpice\Rating\Hook\BeforePageDisplay;
use BlueSpice\Hook\BeforePageDisplay;

class AddResources extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.rating.icons' );

		$configs = $scripts = $styles = [];
		$registry = $this->getServices()->getService( 'BSRatingRegistry' );
		$configFactory = $this->getServices()->getService(
			'BSRatingConfigFactory'
		);
		foreach( $registry->getRegisterdTypeKeys() as $key ) {
			$config = $configFactory->newFromType( $key );
			$configs[$key] = $config->jsonSerialize();
			if( $a = $config->get( 'ModuleStyles' ) ) {
				$styles = array_merge( $styles, $a );
			}
			if( $a = $config->get( 'ModuleScripts' ) ) {
				$scripts = array_merge( $scripts, $a );
			}
		}

		//Make sure to have arrays in JS!
		$scripts = array_values( array_unique( $scripts ) );
		$styles = array_values( array_unique( $styles ) );

		if( !empty( $scripts ) ) {
			$this->out->addModules( $scripts );
		}
		if( !empty( $styles ) ) {
			$this->out->addModuleStyles(  $styles );
		}
		if( !empty( $configs ) ) {
			$this->out->addJsConfigVars( 'BSRatingConfig', $configs );
		}
		$this->out->addJsConfigVars(
			'BSRatingModules',
			$scripts
		);
		return true;
	}
}