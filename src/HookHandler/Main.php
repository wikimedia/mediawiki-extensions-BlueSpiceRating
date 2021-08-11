<?php

namespace BlueSpice\Rating\HookHandler;

use BlueSpice\Rating\GlobalActionsToolRating;
use BlueSpice\Rating\GlobalActionsToolRecommendations;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class Main implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsTools',
			[
				'special-bluespice-rating' => [
					'factory' => static function () {
						return new GlobalActionsToolRating();
					}
				],
				'special-bluespice-recommendations' => [
					'factory' => static function () {
						return new GlobalActionsToolRecommendations();
					}
				]
			]

		);
	}
}
