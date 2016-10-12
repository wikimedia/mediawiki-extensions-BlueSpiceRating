<?php
/**
 * Renders the Rating special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: SpecialRating.class.php 9041 2013-03-27 13:08:24Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Rating SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage Rating
 */
class SpecialRating extends SpecialPage {

	function __construct() {
		parent::__construct( 'Rating', 'rating-viewspecialpage', true );
	}

	function execute($sParam) {
		//parent::execute($sParam);
		$this->checkPermissions();
		BsExtensionManager::setContext('MW::SpecialRating');

		$this->getOutput()->setPageTitle( wfMessage( 'bs-rating-special-rating-heading' )->plain() );

		$this->getOutput()->addHtml('<div id="bs-rating-grid"></div>');

		$this->getOutput()->addModules('ext.bluespice.rating');
		$this->getOutput()->addModules('ext.bluespice.specialRating');
		$this->getOutput()->addModuleStyles('ext.bluespice.rating.styles');
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}