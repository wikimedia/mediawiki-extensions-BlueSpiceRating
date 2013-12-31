<?php
/**
 * Renders the Rating special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    $Id: SpecialRating.class.php 9041 2013-03-27 13:08:24Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Rating SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage Rating
 */
class SpecialRating extends BsSpecialPage {

	function __construct() {
		parent::__construct( 'SpecialRating', 'rating-viewspecialpage' );
	}

	function execute($sParam) {
		parent::execute($sParam);
		BsExtensionManager::setContext('MW::SpecialRating');
		global $wgOut;

		$wgOut->setPageTitle( wfMsg( 'bs-rating-special-rating-heading' ) );

		$wgOut->addHtml('<div id="bs-rating-grid"></div>');
	}

	/**
	 * AJAX interface for BlueSpice Rating dialog on SpecialRating
	 * @return string The JSON formatted response
	 */
	public static function ajaxGetAllRatings( ) {
		global $wgUser;
		$aResult = array(
			'success' => false,
			'message' => '',
			'errors' => array(),
			'total' => 0,
			'ratings' => array(),
		);

		if( !$wgUser->isAllowed('rating-viewspecialpage') ) {
			$aResult['message'] = wfMessage('bs-specialrating-err-permissiondenied')->plain();
			return json_encode( $aResult );
		}

		$iStart		= BsCore::getParam('start', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT);
		$iLimit		= BsCore::getParam('limit', 15, BsPARAM::REQUEST | BsPARAMTYPE::INT);
		$sOrderBy	= BsCore::getParam('sort', 'vote', BsPARAM::REQUEST | BsPARAMTYPE::STRING);
		$sDir		= BsCore::getParam('dir', 'DESC', BsPARAM::REQUEST | BsPARAMTYPE::STRING);
		$sRefType	= BsCore::getParam('reftype', 'article', BsPARAM::REQUEST | BsPARAMTYPE::STRING);

		$aTables = array( 
			'bs_rating' 
		);
		$aFields = array( 
			'rat_ref as ref',
			'rat_reftype as reftype', 
			'ROUND(AVG( rat_value ),1) AS vote', 
			'COUNT(rat_value) as votes' 
		);
		$aOptions = array(
			'LIMIT'	=> $iLimit,
			'OFFSET' => $iStart,
			'ORDER BY' => $sOrderBy.' '.$sDir,
			'GROUP BY' => 'rat_reftype, rat_ref',
		);
		$aConditions = array(
			'rat_reftype' => $sRefType,
			'rat_archived' => '0', 
		);

		$oRatingInstance = BsExtensionManager::getExtension('Rating');
		$oRatingInstance->runRegisterCustomTypes();

		$aRatingTypes			= BsConfig::get( 'MW::Rating::RatingTypes' );
		$aSpecialRatingTypes	= BsConfig::get( 'MW::Rating::SpecialRatingTypes' );

		if( !in_array($sRefType, $aSpecialRatingTypes) || !array_key_exists($sRefType, $aRatingTypes) ) {
			$aResult['message'] = wfMessage('bs-specialrating-err-invalidreftype')->plain();
			return json_encode( $aResult );
		}

		if( !wfRunHooks( 'BSRatingCustomTypListRatings', array($sRefType, &$aTables, &$aFields, &$aOptions, &$aConditions, &$aResult)) ) {
			return json_encode( $aResult );
		}

		$dbr = wfGetDB( DB_SLAVE );

		$total = $dbr->select( 
			'bs_rating', 
			'COUNT(rat_ref) as total', 
			$aConditions, 
			__METHOD__, 
			array( 'GROUP BY' => 'rat_reftype, rat_ref') 
		);
		if( !$total ) return json_encode( $aResult );

		$aResult['success'] = true;
		$aResult['total'] = $total->numRows();
		if( $aResult['total'] < 1 ) {
			return json_encode( $aResult );
		}

		$res = $dbr->select( $aTables, $aFields, $aConditions, __METHOD__, $aOptions );
		while($row = $dbr->fetchObject($res)) {
			$aResult['ratings'][] = $row;
		}

		if($sRefType == 'article') {
			foreach($aResult['ratings'] as $iKey => $oRating) {
				$oTitle = Title::newFromID($oRating->ref);

				$aResult['ratings'][$iKey]->refcontent = 
					$oTitle ? '<a href="'.$oTitle->getFullUrl().'">'.$oTitle->getPrefixedText().'</a>'
							: '';
			}
		}

		return json_encode( $aResult );
	}

	/**
	 * AJAX interface for BlueSpice Rating type filter on special page
	 * @return string The JSON formatted response
	 */
	public static function ajaxGetRatingTypes( ) {
		global $wgUser;
		$aResult = array(
			'success' => false,
			'message' => '',
			'errors' => array(),
			'types' => array(),
		);

		if( !$wgUser->isAllowed('rating-viewspecialpage') ) {
			$aResult['message'] = wfMessage('bs-specialrating-err-permissiondenied')->plain();
			return json_encode( $aResult );
		}

		$oRatingInstance = BsExtensionManager::getExtension('Rating');
		$oRatingInstance->runRegisterCustomTypes();

		$aRatingTypes = BsConfig::get( 'MW::Rating::RatingTypes' );
		$aSpecialRatingTypes = BsConfig::get( 'MW::Rating::SpecialRatingTypes' );

		$aResult['success'] = true;
		foreach($aSpecialRatingTypes as $value) {
			if( !array_key_exists($value, $aRatingTypes) ) continue;

			$oRatingTypes = new stdClass();
			$oRatingTypes->reftype = $value;
			$oRatingTypes->reftypei18n = $aRatingTypes[$value]['displaytitle'];

			$aResult['types'][] = $oRatingTypes;
		}

		return json_encode( $aResult );
	}
}