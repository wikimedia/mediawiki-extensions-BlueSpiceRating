<?php

namespace BlueSpice\Rating\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSGridSpecialPage;

class Recommendations extends OOJSGridSpecialPage {

	public function __construct() {
		parent::__construct( 'Recommendations', 'rating-viewspecialpage', true );
	}

	/**
	 * @inheritDoc
	 */
	public function doExecute( $subPage ) {
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'bs-rating-special-recommendations-heading' )->plain() );
		$out->addModules( [
			'ext.bluespice.ratingItemArticleLike',
			'ext.bluespice.rating.specialRecommendations',
			'ext.bluespice.ratingItemArticleLike.styles'
		] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-rating-special-recommendations-container' ] ) );
	}
}
