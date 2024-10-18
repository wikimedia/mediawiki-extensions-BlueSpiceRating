<?php

namespace BlueSpice\Rating;

use Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;
use SpecialPage;

class GlobalActionsOverviewRecommendations extends RestrictedTextLink {

	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'ga-bs-recommendations';
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		$permissions = [
			'rating-viewspecialpage'
		];
		return $permissions;
	}

	/**
	 *
	 * @return string
	 */
	public function getHref(): string {
		$tool = SpecialPage::getTitleFor( 'Recommendations' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'recommendations' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return Message::newFromKey( 'recommendations' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'recommendations' );
	}
}
