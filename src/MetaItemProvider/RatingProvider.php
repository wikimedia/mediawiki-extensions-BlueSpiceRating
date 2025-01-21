<?php

namespace BlueSpice\Rating\MetaItemProvider;

use BlueSpice\Discovery\IMetaItemProvider;
use BlueSpice\Rating\RatingComponent;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

class RatingProvider implements IMetaItemProvider {

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'rating';
	}

	/**
	 *
	 * @return IComponent
	 */
	public function getComponent(): IComponent {
		$services = MediaWikiServices::getInstance();
		$context = RequestContext::getMain();
		$title = $context->getTitle();
		return new RatingComponent( $title, $services );
	}
}
