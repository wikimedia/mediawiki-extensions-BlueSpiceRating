<?php

namespace BlueSpice\Rating\HookHandler;

use BlueSpice\Rating\GlobalActionsToolRating;
use BlueSpice\Rating\GlobalActionsToolRecommendations;
use BlueSpice\Rating\RatingComponent;
use BlueSpice\Rating\RecommendationsComponent;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;
use RequestContext;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsTools',
			[
				'special-bluespice-rating' => [
					'factory' => function () {
						return new GlobalActionsToolRating();
					}
				],
				'special-bluespice-recommendations' => [
					'factory' => function () {
						return new GlobalActionsToolRecommendations();
					}
				]
			]
		);

		$services = MediaWikiServices::getInstance();
		$context = RequestContext::getMain();
		$title = $context->getTitle();

		$registry->register(
			'ToolsAfterContent',
			[
				'rating' => [
					'factory' => function () use ( $title, $services ) {
						return new RatingComponent( $title, $services );
					}
				],
				'recommendations' => [
					'factory' => function () use ( $title, $services ) {
						return new RecommendationsComponent( $title, $services );
					}
				]
			]
		);
	}
}
