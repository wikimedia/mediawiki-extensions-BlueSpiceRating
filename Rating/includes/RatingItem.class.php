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
	private $sSubType			= '';
	private $sRef				= '';
	private $aRatings			= array();
	private $aAllowedValues		= array();

	/**
	 * Contructor of the Rating class
	 */
	private function __construct( $sRefType, $sRef, $sSubType, $aRegisteredRefType ) {
		$this->sRefType = $sRefType;
		$this->sRef = $sRef;
		$this->sSubType = $sSubType;
		$this->aAllowedValues = $aRegisteredRefType['allowedvalues'];
		$this->loadRating();
	}

	public static function getInstance( $sRefType, $sRef, $sSubType = '', $bForceReload = false ) {
		if( empty($sRef) || empty($sRefType) ) return null;

		$aRatingItems = self::$aRatingItems;
		if( !$bForceReload && key_exists($sRefType, $aRatingItems) ) {
			if( key_exists($sRef, $aRatingItems[$sRefType]) ) {
				if( key_exists($sSubType, $aRatingItems[$sRefType][$sRef]) ) {
					return self::$aRatingItems[$sRefType][$sRef][$sSubType];
				}
			}
		}

		$aRegisteredRefTypes = Rating::getRatingTypes();
		if( !isset($aRegisteredRefTypes[$sRefType]) ) return null;

		$oInstance = new RatingItem( $sRefType, $sRef, $sSubType, $aRegisteredRefTypes[$sRefType] );
		self::$aRatingItems[$sRefType][$sRef][$sSubType] = $oInstance;

		return $oInstance;
	}

	/**
	 * loads the ratings from the bs_rating table
	 * @return boolean
	 */
	protected function loadRating() {
		$aRatings = array();

		$sRef		= (string) $this->sRef;
		$sRefType	= $this->sRefType;
		$sSubType	= $this->sSubType;

		$aConditions = array( 
			'rat_reftype' => $sRefType,
			'rat_subtype' => $sSubType,
			'rat_archived' => '0'
		);

		if( !empty($sRef) ) {
			$aConditions['rat_ref'] = $sRef;
		}

		//Abort query when hook-handler returns false
		if( !wfRunHooks('BSRatingBeforeLoadRatingQuery', array($sRef, $sRefType, &$aConditions)) ) {
			return true;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select( 'bs_rating', '*', $aConditions );
		if( !$res ) {
			$this->aRatings = $aRatings;
			return true;
		}

		while($row = $dbr->fetchObject($res)) {
			$aRatings[$row->rat_id] = array(
				'id'      => $row->rat_id,
				'reftype' => $row->rat_reftype,
				'ref'     => $row->rat_ref,
				'userid'  => $row->rat_userid,
				'userip'  => $row->rat_userip,
				'value'   => $row->rat_value,
				'created' => $row->rat_created,
				'touched' => $row->rat_touched,
				'subtype' => $row->rat_subtype,
				'context' => $row->rat_subtype,
			);
		}

		$this->aRatings = $aRatings;
		return true;
	}

	/**
	 * inserts a single rating to the bs_rating table
	 * @param string $sRef
	 * @param int $iValue
	 * @param string $sRefType
	 * @param int $iUserID
	 * @param string $sUserIP
	 * @return boolean
	 */
	public function setRating( $sRef, $iValue, $notinuse = 'article', $iUserID = 0, $sUserIP = '', $iContext = 0 ) {
		$sRef = $this->sRef;
		$sRefType = $this->sRefType;
		$sSubType = $this->sSubType;
		$oUserVote = null;

		wfRunHooks( 'BSRatingCustomTypSetRating', array(
			&$sRef,
			&$iValue,
			$sRefType,
			$iUserID,
			$sUserIP,
			&$sSubType,
			&$iContext,
		));

		if( empty($iValue) || empty($sRef)) return false;
		if( empty($iUserID) && empty($sUserIP) ) return false;

		$sRef = (string) $sRef;
		$iValue = (int) $iValue;

		if( !in_array($iValue, $this->aAllowedValues) ) return false;

		$aConditions = array(
			'rat_ref' => $sRef, 
			'rat_reftype' => $sRefType, 
			'rat_archived' => '0',
			'rat_subtype' => $sSubType,
		);

		if( !empty($iUserID) ) {
			$aConditions['rat_userid'] = $iUserID;
		}
		else {
			$aConditions['rat_userip'] = $sUserIP;
		}
		if( !empty($iContext) ) {
			$aConditions['rat_context'] = $iContext;
		}

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->select( 'bs_rating', '*', $aConditions );

		if(!$res) return false;

		$oUserVote = $dbw->fetchObject($res);

		if( is_object( $oUserVote ) ) {
			$b = $dbw->update( 
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
		$aInsertFields['rat_value']   = $iValue;
		$aInsertFields['rat_ref']     = $sRef;
		$aInsertFields['rat_reftype'] = $sRefType;
		$aInsertFields['rat_created'] = wfTimestampNow();
		$aInsertFields['rat_touched'] = wfTimestampNow();
		$aInsertFields['rat_subtype'] = $sSubType;
		$aInsertFields['rat_context'] = $iContext;

		$b = $dbw->insert( 'bs_rating', $aInsertFields);

		$res = $dbw->select( 'bs_rating', '*', $aConditions );
		if(!$res) return false;
		$oUserVote = $dbw->fetchObject($res);

		$this->addRating( $oUserVote );
		return $b;
	}
	
	/**
	 * Archives this RatingItem - When User given: Archives rating of given user in this RatingItem
	 * @param User $oUser
	 * @param integer $iContext
	 * @return Boolean - true or false
	 */
	public function archiveRating($oUser = null, $iContext = 0) {
		$aConditions = array(
			'rat_ref' => $this->sRef,
			'rat_reftype' => $this->sRefType,
		);

		if( !empty($iContext) ) {
			$aConditions['rat_context'] = $iContext;
		}

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
	protected function addRating( $oUserVote ) {
		return $this->aRatings[$oUserVote->rat_id] = array(
			'id'		=> $oUserVote->rat_id,
			'reftype'	=> $oUserVote->rat_reftype,
			'ref'		=> $oUserVote->rat_ref,
			'userid'	=> $oUserVote->rat_userid,
			'userip'	=> $oUserVote->rat_userip,
			'value'		=> $oUserVote->rat_value,
			'created'	=> $oUserVote->rat_created,
			'touched'	=> $oUserVote->rat_touched,
			'subtype'	=> $oUserVote->rat_subtype,
			'context'	=> $oUserVote->rat_context,
		);
	}

	protected function filterRating( $a = array() ) {
		if( empty($a) ) {
			return $this->aRatings;
		}
		return array_filter($this->aRatings, function($e) use($a) {
			foreach( $a as $sKey => $mValue ) {
				if( !isset($e[$sKey]) ) {
					return false;
				}
				if( $e[$sKey] == $mValue ) {
					continue;
				}
				return false;
			}
			return true;
		});
	}

	public function getRatings( $iContext = 0 ) {
		if( empty($iContext) ) {
			return $this->filterRating();
		}
		return $this->filterRating(array(
			'context' => $iContext
		));
	}

	public function getRefType() {
		return $this->sRefType;
	}

	public function getSubType() {
		return $this->sSubType;
	}

	public function getRef() {
		return $this->sRef;
	}

	public function countRatings( $iContext = 0 ) {
		return count($this->getRatings( $iContext ));
	}

	public function getTotal( $iContext = 0 ) {
		$aFilter = array();
		$iTotal = 0;
		if( !empty($iContext) ) {
			$aFilter['context'] = $iContext;
		}
		$aRatings = $this->filterRating( $aFilter );
		if( empty( $aRatings )) {
			return $iTotal;
		}
		foreach( $aRatings as $iID => $aRating ) {
			$iTotal += $aRating['value'];
		}
		return $iTotal;
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
	public function hasUserRated( User $oUser, $return = false, $iContext = 0 ) {
		if( !is_object($oUser) ) return $return;
		$iUserID = $oUser->getId();
		//use name as ip for anonymous
		if( empty($iUserID) ) {
			$iUserID = $oUser->getName();
		}
		$aRatedUserIDs = $this->getRatedUserIDs( $iContext );
		if( in_array($iUserID, $aRatedUserIDs) ) {
			$return = true;
		}
		return $return;
	}

	public function getRatedUserIDs( $iContext = 0 ) {
		$aUserIDs = $aFilter = array();
		if( !empty($iContext) ) {
			$aFilter['context'] = $iContext;
		}
		$aRatings = $this->filterRating( $aFilter );
		if( empty($aRatings) ) {
			return $aUserIDs;
		}

		foreach( $aRatings as $iID => $aRating ) {
			$aUserIDs[] = empty($aRating['userid'])
				? $aRating['userip']
				: $aRating['userid']
			;
		}
		return $aUserIDs;
	}

	/**
	 * DEPRECATED single rating return
	 * @deprecated backward compatibility - use getRatingsOfSpecificUser instead
	 * @param User $oUser
	 * @param integer $iContext
	 * @return array
	 */
	public function getRatingOfSpecificUser( User $oUser, $iContext = 0 ) {
		$aRatings = $this->getRatingsOfSpecificUser( $oUser, $iContext );
		if( empty($aRatings) ) {
			return $aRatings;
		}
		$aRatings = array_values($aRatings);
		return $aRatings[0];
	}

	public function getRatingsOfSpecificUser( User $oUser, $iContext = 0 ) {
		$aFilter = array();
		$iUserID = $oUser->getId();
		if( !empty($iUserID) ) {
			$aFilter['userid'] = $iUserID;
		} else {
			$aFilter['userip'] = $oUser->getName();
		}
		if( !empty($iContext) ) {
			$aFilter['context'] = $iContext;
		}
		$aRatings = $this->filterRating( $aFilter );

		return $aRatings;
	}

	public function getValueFilteredRatings( $iValue = 0, $iContext = 0  ) {
		if( !in_array($iValue, $this->getAllowedValues()) ) {
			return array();
		}

		$aFilter = array(
			'value' => $iValue,
		);
		if( !empty($iContext) ) {
			$aFilter['context'] = $iContext;
		}
		return $this->filterRating( $aFilter );
	}

	public function getView($oUserOnly = null, $sForceThisView = '') {
		$aRegisteredRefTypes = Rating::getRatingTypes();
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