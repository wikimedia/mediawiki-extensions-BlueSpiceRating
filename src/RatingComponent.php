<?php

namespace BlueSpice\Rating;

use BsArticleHelper;
use IContextSource;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;

class RatingComponent extends Literal {

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 * @var Title
	 */
	private $title = null;

	/**
	 *
	 * @param Title $title
	 * @param MediaWikiServices $services
	 */
	public function __construct( $title, $services ) {
		parent::__construct(
			'bs-rating-component',
			''
		);

		$this->services = $services;
		$this->title = $title;
	}

	/**
	 * Raw HTML string
	 *
	 * @return string
	 */
	public function getHtml() : string {
		$factory = $this->services->getService( 'BSRatingFactoryArticle' );
		$rating = $factory->newFromTitle( $this->title );

		return $rating->getTag();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ) : bool {
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
		if ( !$registry->isRegisteredType( 'article' ) ) {
			return false;
		}

		$prop = $this->services->getService( 'BSUtilityFactory' )
			->getPagePropHelper( $title )->getPageProp( 'bs_norating' );
		if ( $prop !== null ) {
			return false;
		}

		$config = $this->services->getConfigFactory()->makeConfig( 'bsg' );

		$enabledNamespaces = $config->get( 'RatingArticleEnabledNamespaces' );
		if ( !in_array( $title->getNamespace(), $enabledNamespaces ) ) {
			return false;
		}

		$factory = $this->services->getService( 'BSRatingFactoryArticle' );
		$rating = $factory->newFromTitle( $title );
		if ( !$rating ) {
			return false;
		}

		$oStatus = $rating->userCan(
			$context->getUser(),
			'read',
			$title
		);

		if ( $oStatus->isOK() ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getRequiredRLStyles(): array {
		return [ 'ext.bluespice.rating.discovery.styles' ];
	}
}
