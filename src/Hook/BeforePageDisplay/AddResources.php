<?php

namespace BlueSpice\Rating\Hook\BeforePageDisplay;

use BlueSpice\Rating\RatingConfigFactory;
use BlueSpice\Rating\RatingRegistry;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Registration\ExtensionRegistry;

class AddResources implements BeforePageDisplayHook {

	/** @var ConfigFactory */
	private $configFactory;

	/** @var RatingRegistry */
	private $ratingRegistry;

	/** @var RatingConfigFactory */
	private $ratingConfigFactory;

	/**
	 * @param ConfigFactory $configFactory
	 * @param RatingRegistry $ratingRegistry
	 * @param RatingConfigFactory $ratingConfigFactory
	 */
	public function __construct(
		ConfigFactory $configFactory, RatingRegistry $ratingRegistry, RatingConfigFactory $ratingConfigFactory
	) {
		$this->configFactory = $configFactory;
		$this->ratingRegistry = $ratingRegistry;
		$this->ratingConfigFactory = $ratingConfigFactory;
	}

	/**
	 * @param OutputPage $out
	 * @return bool
	 */
	protected function shouldSkipProcessing( OutputPage $out ): bool {
		if ( ExtensionRegistry::getInstance()->isLoaded( 'BlueSpiceSocialRating' ) ) {
			return false;
		}

		$title = $out->getTitle();
		if ( !$title ) {
			return true;
		}

		$config = $this->configFactory->makeConfig( 'bsg' );
		$enabledRatingNamespaces = $config->get( 'RatingArticleEnabledNamespaces' );
		$enabledRecommendNamespaces = $config->get( 'RatingArticleLikeEnabledNamespaces' );
		$namespace = $title->getNamespace();
		if (
			!in_array( $namespace, $enabledRatingNamespaces ) &&
			!in_array( $namespace, $enabledRecommendNamespaces )
		) {
			return true;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $this->shouldSkipProcessing( $out ) ) {
			return;
		}

		$out->addModuleStyles( 'ext.bluespice.rating.icons' );

		$scripts = $styles = [];
		foreach ( $this->ratingRegistry->getRegisterdTypeKeys() as $key ) {
			$config = $this->ratingConfigFactory->newFromType( $key );
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
			$out->addModules( $scripts );
		}
		if ( !empty( $styles ) ) {
			$out->addModuleStyles( $styles );
		}
	}
}
