<?php
/**
 * Renders the Rating special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpice Pro
 * @subpackage BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
namespace BlueSpice\Rating\Special;

/**
 * Rating SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage Rating
 */
class Rating extends \SpecialPage {

	function __construct() {
		parent::__construct( 'Rating', 'rating-viewspecialpage', true );
	}

	function execute( $param ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			$this->msg( 'bs-rating-special-rating-heading' )->plain()
		);

		$this->getOutput()->addHtml(
			\Html::element('div', ['id' => "bs-ratingarticle-grid"] )
		);

		$this->getOutput()->addModules( 'ext.bluespice.ratingItemArticle' );
		$this->getOutput()->addModules( 'ext.bluespice.specialRating' );
		$this->getOutput()->addModuleStyles( 'ext.bluespice.rating.styles' );
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}