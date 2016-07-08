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

	/**
	 * AJAX interface for BlueSpice Rating dialog on SpecialRating
	 * @return string The JSON formatted response
	 */
	public static function ajaxGetAllRatings( ) {
		global $wgUser, $wgRequest;
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

		$oStoreParams = BsExtJSStoreParams::newFromRequest();
		$iLimit = $oStoreParams->getLimit();
		$iStart = $oStoreParams->getStart();
		$sOrderBy = $oStoreParams->getSort('rat_ref');
		$sDir = $oStoreParams->getDirection();
		$aFilters = $oStoreParams->getFilter();

		$sRefType	= $wgRequest->getVal('reftype', 'article');

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

		if( $sOrderBy == 'ref' ) {
			$aTables[] = 'page';
			$aOptions['ORDER BY'] = 'page_title '.$sDir;
			$aConditions[] = 'page_id = rat_ref';
		} elseif( $sOrderBy == 'vote' ) {
			$aOptions['ORDER BY'] = 'ROUND(AVG( rat_value ),1) '.$sDir;
		} elseif( $sOrderBy == 'votes' ) {
			$aOptions['ORDER BY'] = 'COUNT(rat_value) '.$sDir;
		}

		$aHaving = array();
		if( !empty($aFilters) ) {
			foreach($aFilters as $oFilter) {
				if( $sRefType == 'article' && !in_array('page', $aTables) ) {
					$aTables[] = 'page';
					$aConditions[] = 'page_id = rat_ref';
				}
				switch($oFilter->field) {
					case 'ref':
						$aConditions[] = "page_title LIKE '%".trim($oFilter->value)."%'";
						break;
					case 'vote':
						if($oFilter->comparison == 'gt')
							$aHaving[] = "ROUND(AVG( rat_value ),1) > '".trim($oFilter->value)."'";
						if($oFilter->comparison == 'lt')
							$aHaving[] = "ROUND(AVG( rat_value ),1) < '".trim($oFilter->value)."'";
						if($oFilter->comparison == 'eq')
							$aHaving[] = "ROUND(AVG( rat_value ),1) = '".trim($oFilter->value)."'";
						break;
					case 'votes':
						if($oFilter->comparison == 'gt')
							$aHaving[] = "COUNT(rat_value) > '".trim($oFilter->value)."'";
						if($oFilter->comparison == 'lt')
							$aHaving[] = "COUNT(rat_value) < '".trim($oFilter->value)."'";
						if($oFilter->comparison == 'eq')
							$aHaving[] = "COUNT(rat_value) = '".trim($oFilter->value)."'";
						break;
				}
			}
		}

		if( !empty($aHaving) ) {
			$aOptions['HAVING'] = implode( ' AND ', $aHaving );
		}

		$aRatingTypes = Rating::getRatingTypes();
		$aSpecialRatingTypes = Rating::getSpecialRatingTypes();

		if( !in_array($sRefType, $aSpecialRatingTypes) || !array_key_exists($sRefType, $aRatingTypes) ) {
			$aResult['message'] = wfMessage('bs-specialrating-err-invalidreftype')->plain();
			return json_encode( $aResult );
		}

		if( !wfRunHooks( 'BSRatingCustomTypListRatings', array($sRefType, &$aTables, &$aFields, &$aOptions, &$aConditions, &$aResult)) ) {
			return json_encode( $aResult );
		}

		$dbr = wfGetDB( DB_SLAVE );

		$total = $dbr->select( 
			$aTables, 
			'COUNT(rat_ref) as total', 
			$aConditions, 
			__METHOD__, 
			$aOptions
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