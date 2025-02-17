<?php

namespace BlueSpice\Rating\Property;

use BlueSpice\Rating\Data\RatingSet;
use BlueSpice\Rating\RatingFactory\Article;
use BlueSpice\SMWConnector\PropertyValueProvider;
use MediaWiki\MediaWikiServices;
use SESP\AppFactory;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem;
use SMWDINumber;

class RatingAverageValueProvider extends PropertyValueProvider {

	public static function factory() {
		return [ new static( MediaWikiServices::getInstance()->getService( 'BSRatingFactoryArticle' ) ) ];
	}

	/**
	 * @param Article $ratingFactory
	 */
	public function __construct(
		private readonly Article $ratingFactory
	) {
	}

	/**
	 *
	 * @return string
	 */
	public function getAliasMessageKey() {
		return "bs-rating-sesp-rating-average";
	}

	/**
	 *
	 * @return string
	 */
	public function getDescriptionMessageKey() {
		return "bs-rating-sesp-rating-average-desc";
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
		return '_BS_RATING_AVERAGE';
	}

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return "Ratings/average";
	}

	/**
	 * @param AppFactory $appFactory
	 * @param DIProperty $property
	 * @param SemanticData $semanticData
	 * @return void
	 */
	public function addAnnotation( $appFactory, $property, $semanticData ) {
		$set = $this->getSet( $semanticData );
		$semanticData->addPropertyObjectValue(
			$property, new SMWDINumber( $set ? $set->getAverage() : 0 )
		);
	}

	/**
	 * @param SemanticData $semanticData
	 * @return RatingSet|null
	 */
	protected function getSet( SemanticData $semanticData ): ?RatingSet {
		$title = $semanticData->getSubject()->getTitle();
		if ( $title === null ) {
			return null;
		}
		$rating = $this->ratingFactory->newFromTitle( $title );
		if ( !$rating ) {
			return null;
		}
		return $rating->getRatingSet();
	}
}
