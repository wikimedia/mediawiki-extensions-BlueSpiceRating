<?php

namespace BlueSpice\Rating;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;

class RatingConfigFactory {
	/** @var array|null */
	protected $ratingConfigs = null;

	/** @var Config */
	protected $config = null;
	/** @var \RatingRegistry */
	protected $ratingRegistry = null;

	/**
	 * @param \RatingRegistry $ratingRegistry
	 * @param Config $config
	 */
	public function __construct( $ratingRegistry, $config ) {
		$this->ratingRegistry = $ratingRegistry;
		$this->config = $config;
	}

	/**
	 * RatingConfig factory
	 * @param string $type Rating type
	 * @return RatingConfig|null
	 */
	public function newFromType( $type ) {
		if ( $this->ratingConfigs ) {
			if ( !isset( $this->ratingConfigs[$type] ) ) {
				return null;
			}
			return $this->ratingConfigs[$type];
		}
		$this->ratingConfigs = [];

		$ratingDefinitions = $this->ratingRegistry->getRatingDefinitions();
		$defaults = [];

		// Deprecated: This hook should not be used anymore - Use the bluespice
		// global config mechanism instead
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSRatingConfigDefaults', [
			&$defaults
		] );
		foreach ( $ratingDefinitions as $key => $sConfigClass ) {
			$this->ratingConfigs[$key] = new $sConfigClass(
				$this->config,
				$key,
				$defaults
			);
		}

		if ( !isset( $this->ratingConfigs[$type] ) ) {
			return null;
		}
		return $this->ratingConfigs[$type];
	}
}
