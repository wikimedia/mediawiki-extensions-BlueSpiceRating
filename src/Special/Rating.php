<?php

namespace BlueSpice\Rating\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSGridSpecialPage;

class Rating extends OOJSGridSpecialPage {

	public function __construct() {
		parent::__construct( 'Rating', 'rating-viewspecialpage', true );
	}

	/**
	 * @inheritDoc
	 */
	public function doExecute( $subPage ) {
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
