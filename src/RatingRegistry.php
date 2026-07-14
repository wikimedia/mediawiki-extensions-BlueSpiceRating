<?php

namespace BlueSpice\Rating;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;

class RatingRegistry {
	/** @var array|null */
	protected $ratingDefinitions = null;

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @param Config $config
	 */
	public function __construct( $config ) {
		$this->config = $config;
	}

	/**
	 * @param bool $forceReload
	 * @return true
	 */
	protected function runRegister( $forceReload = false ) {
		if ( $this->ratingDefinitions && !$forceReload ) {
			return true;
		}

		$extRegistry = ExtensionRegistry::getInstance();
		$this->ratingDefinitions = $extRegistry->getAttribute(
			'BlueSpiceRatingRatingRegistry'
		);

		// This hook is deprecated - Use attributes mechanism in extension.json
		// to register entities
		MediaWikiServices::getInstance()->getHookContainer()->run( 'RatingRegister', [
			&$this->ratingDefinitions
		] );

		return true;
	}

	/**
	 * Returns all registered entities ( type => RatingConfigClass )
	 * @return array
	 */
	public function getRatingDefinitions() {
		if ( !$this->runRegister() ) {
			return [];
		}
		return $this->ratingDefinitions;
	}

	/**
	 * Checks if given type is a registered Rating
	 * @param string $sType
	 * @return bool
	 */
	public function isRegisteredType( $sType ) {
		return in_array(
			$sType,
			$this->getRegisterdTypeKeys()
		);
	}

	/**
	 * Returns a registered rating by given type
	 * @param string $sType
	 * @return array
	 */
	public function getRegisteredRatingByType( $sType ) {
		if ( !$this->isRegisteredType( $sType ) ) {
			return [];
		}
		return $this->ratingDefinitions[$sType];
	}

	/**
	 * Returns all registered rating types
	 * @return array
	 */
	public function getRegisterdTypeKeys() {
		return array_keys(
			$this->getRatingDefinitions()
		);
	}
}
