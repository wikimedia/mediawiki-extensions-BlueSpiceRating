<?php

namespace BlueSpice\Rating\Special;

use Html;
use SpecialPage;

class Rating extends SpecialPage {

	public function __construct() {
		parent::__construct( 'Rating', 'rating-viewspecialpage', true );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'bs-rating-special-rating-heading' )->plain() );
		$out->addModules( [
			'ext.bluespice.ratingItemArticle',
			'ext.bluespice.rating.specialRating',
			'ext.bluespice.rating.styles'
		] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-rating-special-rating-container' ] ) );
	}
}
