<?php

namespace BlueSpice\Rating;

use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;

class GlobalActionsOverviewRating extends RestrictedTextLink {

	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'ga-bs-rating';
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
		$tool = SpecialPage::getTitleFor( 'Rating' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'rating' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return Message::newFromKey( 'rating' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'rating' );
	}
}
