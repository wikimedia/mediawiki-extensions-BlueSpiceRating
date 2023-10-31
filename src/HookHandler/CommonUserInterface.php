<?php

namespace BlueSpice\Rating\HookHandler;

use BlueSpice\Rating\GlobalActionsOverviewRating;
use BlueSpice\Rating\GlobalActionsOverviewRecommendations;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsOverview',
			[
				'special-bluespice-rating' => [
					'factory' => static function () {
						return new GlobalActionsOverviewRating();
					}
				],
				'special-bluespice-recommendations' => [
					'factory' => static function () {
						return new GlobalActionsOverviewRecommendations();
					}
				]
			]
		);
	}
}
