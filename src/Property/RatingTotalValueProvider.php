<?php

namespace BlueSpice\Rating\Property;

use SESP\AppFactory;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem;
use SMWDINumber;

class RatingTotalValueProvider extends RatingAverageValueProvider {

	/**
	 *
	 * @return string
	 */
	public function getAliasMessageKey() {
		return "bs-rating-sesp-rating-total";
	}

	/**
	 *
	 * @return string
	 */
	public function getDescriptionMessageKey() {
		return "bs-rating-sesp-rating-total-desc";
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
		return '_BS_RATING_TOTAL';
	}

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return "Ratings/total";
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
			$property, new SMWDINumber( $set ? $set->getTotal() : 0 )
		);
	}
}
