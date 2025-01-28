<?php

namespace BlueSpice\Rating\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class Recommendations extends SpecialPage {

	public function __construct() {
		parent::__construct( 'Recommendations', 'rating-viewspecialpage', true );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

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
