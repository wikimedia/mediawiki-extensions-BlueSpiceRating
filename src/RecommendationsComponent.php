<?php

namespace BlueSpice\Rating;

use BsArticleHelper;
use IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;

class RecommendationsComponent extends Literal {

	/**
	 * @var MediaWikiServices
	 */
	private $services;

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @param Title $title
	 * @param MediaWikiServices $services
	 */
	public function __construct( $title, $services ) {
		parent::__construct(
			'bs-recommendations-component',
			''
		);

		$this->services = $services;
		$this->title = $title;
	}

	/**
	 * @inheritDoc
	 */
	public function getHtml(): string {
		$factory = $this->services->getService( 'BSRatingFactoryArticleLike' );
		$rating = $factory->newFromTitle( $this->title );

		return $rating->getTag();
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ): bool {
		$request = $context->getRequest();
		$action = $request->getVal( 'action', 'view' );
		if ( !in_array( $action, [ 'view', 'submit' ] ) ) {
			return false;
		}

		$title = $context->getTitle();
		if ( !$title ) {
			return false;
		}

		if ( $title->isRedirect() ) {
			if ( $request->getVal( 'redirect' ) != 'no' ) {
				$title = BsArticleHelper::getInstance( $title )
					->getTitleFromRedirectRecurse();
			}
			if ( !$title || !$title->exists() || $title->isRedirect() ) {
				return false;
			}
		}
		if ( $title->getNamespace() === NS_SPECIAL ) {
			return false;
		}

		$registry = $this->services->getService( 'BSRatingRegistry' );
		if ( !$registry->isRegisteredType( 'articlelike' ) ) {
			return false;
		}

		$prop = $this->services->getService( 'BSUtilityFactory' )
			->getPagePropHelper( $title )->getPageProp( 'bs_norating' );
		if ( $prop !== null ) {
			return false;
		}

		$config = $this->services->getConfigFactory()->makeConfig( 'bsg' );

		$enabledNamespaces = $config->get(
			'RatingArticleLikeEnabledNamespaces'
		);
		if ( !in_array( $title->getNamespace(), $enabledNamespaces ) ) {
			return false;
		}

		$factory = $this->services->getService(
			'BSRatingFactoryArticleLike'
		);
		$rating = $factory->newFromTitle( $title );
		if ( !$rating ) {
			return false;
		}
		$oStatus = $rating->userCan(
			$context->getUser(),
			'read',
			$title
		);
		if ( !$oStatus->isOK() ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getRequiredRLStyles(): array {
		return [];
	}
}
