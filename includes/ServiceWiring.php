<?php

use MediaWiki\MediaWikiServices;

return [

	'BSRatingRegistry' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Rating\RatingRegistry(
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSRatingConfigFactory' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Rating\RatingConfigFactory(
			$services->getService( 'BSRatingRegistry' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSRatingFactory' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Rating\RatingFactory(
			$services->getService( 'BSRatingRegistry' ),
			$services->getService( 'BSRatingConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSRatingFactoryArticle' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Rating\RatingFactory\Article(
			$services->getService( 'BSRatingRegistry' ),
			$services->getService( 'BSRatingConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSRatingFactoryArticleLike' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Rating\RatingFactory\ArticleLike(
			$services->getService( 'BSRatingRegistry' ),
			$services->getService( 'BSRatingConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];
