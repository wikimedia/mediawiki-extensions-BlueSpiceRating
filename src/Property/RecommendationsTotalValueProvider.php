<?php

namespace BlueSpice\Rating\Property;

use BlueSpice\Rating\RatingFactory\ArticleLike as ArticleLikeFactory;
use BlueSpice\Rating\RatingItem\ArticleLike;
use BlueSpice\SMWConnector\PropertyValueProvider;
use MediaWiki\MediaWikiServices;
use SESP\AppFactory;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem;
use SMWDINumber;

class RecommendationsTotalValueProvider extends PropertyValueProvider {

	public static function factory() {
		return [ new static( MediaWikiServices::getInstance()->getService( 'BSRatingFactoryArticleLike' ) ) ];
	}

	/**
	 * @param ArticleLikeFactory $ratingFactory
	 */
	public function __construct(
		private readonly ArticleLikeFactory $ratingFactory
	) {
	}

	/**
	 *
	 * @return string
	 */
	public function getAliasMessageKey() {
		return "bs-rating-sesp-recommendations-total";
	}

	/**
	 *
	 * @return string
	 */
	public function getDescriptionMessageKey() {
		return "bs-rating-sesp-recommendations-total-desc";
	}

	/**
	 *
	 * @return int
	 */
	public function getType() {
		return SMWDataItem::TYPE_NUMBER;
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return '_BS_RECOMMENDATIONS_TOTAL';
	}

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return "Recommendations/total";
	}

	/**
	 * @param AppFactory $appFactory
	 * @param DIProperty $property
	 * @param SemanticData $semanticData
	 * @return void
	 */
	public function addAnnotation( $appFactory, $property, $semanticData ) {
		$title = $semanticData->getSubject()->getTitle();
		if ( $title === null ) {
			return;
		}
		/** @var ArticleLike $recommendations */
		$recommendations = $this->ratingFactory->newFromTitle( $title );
		if ( !$recommendations instanceof ArticleLike ) {
			return;
		}
		$total = $recommendations->getRatingSet()?->getTotal() ?? 0;
		$semanticData->addPropertyObjectValue(
			$property, new SMWDINumber( $total )
		);
	}
}
