<?php

namespace BlueSpice\Rating\MetaItemProvider;

use BlueSpice\Discovery\IMetaItemProvider;
use BlueSpice\Rating\RecommendationsComponent;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use RequestContext;

class RecommendationProvider implements IMetaItemProvider {

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'recommendations';
	}

	/**
	 *
	 * @return IComponent
	 */
	public function getComponent(): IComponent {
		$services = MediaWikiServices::getInstance();
		$context = RequestContext::getMain();
		$title = $context->getTitle();
		return new RecommendationsComponent( $title, $services );
	}
}
