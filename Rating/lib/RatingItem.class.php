<?php
/**
 * RatingItem class for extension Rating
 *
 * Provides a rating item.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    0.9.2
 * @version    $Id: Rating.class.php 7033 2012-10-26 16:22:58Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * RatingItem class for Rating extension
 * @package BlueSpice_Extensions
 * @subpackage Rating
 */
class RatingItem {

	private static $aRatingItems = array();

	private $sRefType			= 'article';
	private $sRef				= '';
	private $aRatings			= array();
	private $aUserRated			= array();
	private $iTotal				= 0;
	private $aAllowedValues		= array();

	/**
	 * Contructor of the Rating class
	 */
	private function __construct( $sRefType, $sRef, $aRegisteredRefType ) {
		$this->sRefType = $sRefType;
		$this->sRef = $sRef;
		$this->aAllowedValues = $aRegisteredRefType['allowedvalues'];
		$this->loadRating();
	}
	
	public static function getInstance( $sRefType, $sRef, $bForceReload = false ) {
		if( empty($sRef) || empty($sRefType) ) return null;

		$aRatingItems = self::$aRatingItems;
		if( key_exists($sRefType, $aRatingItems) && !$bForceReload) {
			if( key_exists($sRef, $aRatingItems[$sRefType]) ) {
				return self::$aRatingItems[$sRefType][$sRef];
			}
		}

		$aRegisteredRefTypes = BsConfig::get( 'MW::Rating::RatingTypes' );
		if( !isset($aRegisteredRefTypes[$sRefType]) ) return null;

		$oInstance = new RatingItem( $sRefType, $sRef, $aRegisteredRefTypes[$sRefType] );
		self::$aRatingItems[$sRefType][$sRef] = $oInstance;

		return $oInstance;
	}

	/**
	 * loads the ratings from the bs_rating table
	 * @return boolean
	 */
	public function loadRating() {
		$aRatings = array();
		$aUserRated = array();
		$sRef = $this->sRef;
		$sRefType = $this->sRefType;

		$sRef = (string) $sRef;

		$aConditions = array( 'rat_reftype' => $sRefType, 'rat_archived' => '0' );

		$dbr = wfGetDB( DB_SLAVE );

		if( !empty($sRef) ) {
			$aConditions['rat_ref'] = $sRef;
		}

		//Abort query when hook-handler returns false
		if( !wfRunHooks('BSRatingBeforeLoadRatingQuery', array($sRef, $sRefType, &$aConditions)) ) {
			return true;
		}

		$res = $dbr->select( 'bs_rating', '*', $aConditions );
		if( !$res ) {
			$this->aRatings = $aRatings;
			return true;
		}
		
		$iTotal = 0;
		while($row = $dbr->fetchObject($res)) {
			$aRatings[$row->rat_id] = array(
				'id'		=> $row->rat_id,
				'reftype'	=> $row->rat_reftype,
				'ref'		=> $row->rat_ref,
				'userid'	=> $row->rat_userid,
				'userip'	=> $row->rat_userip,
				'value'		=> $row->rat_value,
				'created'	=> $row->rat_created,
				'touched'	=> $row->rat_touched,
			);
			$mUser = empty($row->rat_userid) ? $row->rat_userip : $row->rat_userid;
			$aUserRated[$row->rat_id] = $mUser;
			$iTotal += $row->rat_value;
		}

		$this->aUserRated = $aUserRated;
		$this->aRatings = $aRatings;
		$this->iTotal = $iTotal;
		return true;
	}

	/**
	 * inserts a single rating to the bs_rating table
	 * @global User $wgUser
	 * @param string $sRef
	 * @param int $iValue
	 * @param string $sRefType
	 * @param int $iUserID
	 * @param string $sUserIP
	 * @return boolean
	 */
	public function setRating( $sRef, $iValue, $sRefType = 'article', $iUserID = 0, $sUserIP = '') {
		$sRef = $this->sRef;
		$sRefType = $this->sRefType;
		$oUserVote = null;

		wfRunHooks( 'BSRatingCustomTypSetRating', array(&$sRef, &$iValue, $sRefType, $iUserID, $sUserIP));

		if( empty($iValue) || empty($sRef)) return false;
		if( empty($iUserID) && empty($sUserIP) ) return false;

		$sRef = (string) $sRef;
		$iValue = (int) $iValue;

		if( !in_array($iValue, $this->aAllowedValues) ) return false;

		//global $wgUser;
		//if( !$wgUser->isAllowed('rating-write') ) return false;

		$aConditions = array('rat_ref' => $sRef, 'rat_reftype' => $sRefType, 'rat_archived' => '0');

		if( !empty($iUserID) ) {
			$aConditions['rat_userid'] = $iUserID;
		}
		else {
			$aConditions['rat_userip'] = $sUserIP;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'bs_rating', '*', $aConditions );

		if(!$res) return false;

		$oUserVote = $dbr->fetchObject($res);

		if( is_object( $oUserVote ) ) {
			$b = $dbr->update( 
				'bs_rating', 
				array('rat_value' => $iValue, 'rat_touched'	=> wfTimestampNow()), 
				array('rat_id' => $oUserVote->rat_id) 
			);
			$oUserVote->rat_value = $iValue;
			$this->addRating( $oUserVote );
			return $b;
		}

		$aInsertFields = array();

		$aInsertFields['rat_userid'] = $iUserID;
		if( !empty($sUserIP) ) {
			$aInsertFields['rat_userip'] = $sUserIP;
		}
		$aInsertFields['rat_value']		= $iValue;
		$aInsertFields['rat_ref']		= $sRef;
		$aInsertFields['rat_reftype']	= $sRefType;
		$aInsertFields['rat_created']	= wfTimestampNow();
		$aInsertFields['rat_touched']	= wfTimestampNow();

		$b = $dbr->insert( 'bs_rating', $aInsertFields);

		$res = $dbr->select( 'bs_rating', '*', $aConditions );
		if(!$res) return false;
		$oUserVote = $dbr->fetchObject($res);
		
		$this->addRating( $oUserVote );
		return $b;
	}
	
	/**
	 * Archives this RatingItem - When User given: Archives rating of given user in this RatingItem
	 * @param User $oUser
	 * @return Boolean - true or false
	 */
	public function archiveRating($oUser = null) {
		$aConditions = array(
			'rat_ref' => $this->sRef,
			'rat_reftype' => $this->sRefType,
		);

		if( !is_null($oUser) ) {
			if( $oUser->getId() === 0 ) {
				$aConditions['rat_userip'] = $oUser->getName();
			} else {
				$aConditions['rat_userid'] = $oUser->getId();
			}
		}

		$dbr = wfGetDB( DB_SLAVE );

		$b = $dbr->update( 
			'bs_rating', 
			array('rat_archived' => '1', 'rat_touched' => wfTimestampNow()), 
			$aConditions 
		);
		return $b;
	}

	/**
	 * updates additional class variables
	 * @param stdClass $oUserVote
	 * @return boolean
	 */
	private function addRating( $oUserVote ) {
		$aRatings = $this->aRatings;
		$aRatings[$oUserVote->rat_id] = array(
			'id'		=> $oUserVote->rat_id,
			'reftype'	=> $oUserVote->rat_reftype,
			'ref'		=> $oUserVote->rat_ref,
			'userid'	=> $oUserVote->rat_userid,
			'userip'	=> $oUserVote->rat_userip,
			'value'		=> $oUserVote->rat_value,
			'created'	=> $oUserVote->rat_created,
			'touched'	=> $oUserVote->rat_touched,
		);
		$this->aRatings = $aRatings;
		
		$aUserRated = $this->aUserRated;
		$mUser = empty($oUserVote->rat_userid) ? $oUserVote->rat_userip : $oUserVote->rat_userid;
		$aUserRated[$oUserVote->rat_id] = $mUser;
		$this->aUserRated = $aUserRated;
		
		$iTotal = 0;
		foreach($aRatings as $aRating) {
			$iTotal += $aRating['value'];
		}
		$this->iTotal = $iTotal;
		return true;
	}

	public function getRatings() {
		return $this->aRatings;
	}
	
	public function getRefType() {
		return $this->sRefType;
	}

	public function getRef() {
		return $this->sRef;
	}

	public function countRatings() {
		return count($this->getRatings());
	}
	
	public function getTotal() {
		return $this->iTotal;
	}
	
	public function getAllowedValues() {
		return $this->aAllowedValues;
	}

	/**
	 * returns if the user has already rated
	 * @param User $oUser
	 * @param boolean $return
	 * @return boolean - true or false
	 */
	public function hasUserRated( $oUser, $return = false ) {
		if( !is_object($oUser) ) return $return;
		$iUserID = $oUser->getId();
		//use name as ip for anonymous
		if( empty($iUserID) ) {
			$iUserID = $oUser->getName();
		}
		if( in_array($iUserID, $this->aUserRated) ) {
			$return = true;
		}
		return $return;
	}
	
	public function getRatedUserIDs() {
		$aUserRated = $this->aUserRated;
		
		$aReturn = array();
		foreach($aUserRated as $key => $value) {
			$aReturn[] = $value;
		}
		
		return $aReturn;
	}

	public function getRatingOfSpecificUser( $oUser, $return = array() ) {
		if( !is_object($oUser) ) return $return;
		$iUserID = $oUser->getId();
		if( empty($iUserID) ) {
			$iUserID = $oUser->getName();
		}
		if( in_array($iUserID, $this->aUserRated) ) {
			$return = $this->aRatings[
				array_search(
					$iUserID,
					$this->aUserRated
				)
			];
		}
		return $return;
	}
	
	public function getValueFilteredRatings( $iValue = 0, $return = array() ) {
		if( !in_array($iValue, $this->getAllowedValues()) ) return $return;

		$aFilteredRatings = array();
		foreach( $this->getRatings() as $iKey => $aRating) {
			if($aRating['value'] != $iValue) continue;
			$aFilteredRatings[$iKey] = $aRating;
		}

		return $aFilteredRatings;
	}

	public function getView($oUserOnly = null, $sForceThisView = '') {
		$aRegisteredRefTypes = BsConfig::get( 'MW::Rating::RatingTypes' );
		if( !isset($aRegisteredRefTypes[$this->sRefType]) ) {
			return null;
		}

		$sViewName = $sForceThisView;
		if( empty($sViewName) ) {
			if( empty($aRegisteredRefTypes[$this->sRefType]['view']) ) {
				return null;
			}
			$sViewName = $aRegisteredRefTypes[$this->sRefType]['view'];
		}

		$oView = new $sViewName();
		$oView->setOptions( $aRegisteredRefTypes[$this->sRefType] );
		$oView->setRatingItem( $this );

		if( is_object($oUserOnly) ){
			$oView->setUser( $oUserOnly );
		}

		return $oView;
	}

	public function renderView( $oUserOnly = null, $sForceThisView = '' ) {
		$oView = $this->getView( $oUserOnly, $sForceThisView );
		return $oView->execute();
	}
}