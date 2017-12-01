<?php

use MediaWiki\MediaWikiServices;

return [

	'BSRatingRegistry' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Rating\RatingRegistry(
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSRatingConfigFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Rating\RatingConfigFactory(
			$services->getService( 'BSRatingRegistry' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSRatingFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\RatingFactory(
			$services->getService( 'BSRatingRegistry' ),
			$services->getService( 'BSRatingConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];
