<?php
/**
 * Renders the Recommendations special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpice Pro
 * @subpackage BlueSpiceRecommendations
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */
namespace BlueSpice\Rating\Special;

/**
 * Recommendations SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage Recommendations
 */
class Recommendations extends \BsSpecialPage {

	function __construct() {
		parent::__construct( 'Recommendations', 'rating-viewspecialpage', true );
	}

	function execute( $param ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			$this->msg( 'bs-rating-special-recommendations-heading' )->plain()
		);

		$this->getOutput()->addHtml(
			\Html::element( 'div', [
				'id' => "bs-ratingarticlelike-grid",
				'class' => "bs-manager-container",
			])
		);

		$enabledNamespaces = $this->getConfig()->get(
			'RatingArticleLikeEnabledNamespaces'
		);
		$namespaces = [];
		foreach( $enabledNamespaces as $nsIdx ) {
			$namespaces[$nsIdx] = \BsNamespaceHelper::getNamespaceName(
				$nsIdx
			); 
		}

		$this->getOutput()->addJsConfigVars(
			'bsgRatingArticleLikeAcitveNamespaces',
			$namespaces
		);

		$this->getOutput()->addModules( 'ext.bluespice.ratingItemArticleLike' );
		$this->getOutput()->addModules( 'ext.bluespice.specialRecommendations' );
		$this->getOutput()->addModuleStyles(
			'ext.bluespice.ratingItemArticleLike.styles'
		);
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}