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
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.27.0
 * @package    BlueSpiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * RatingItem class for Rating extension
 * @package BlueSpiceRating
 * @subpackage Rating
 */
abstract class RatingItem {
	const REFTYPE = '';
	protected static $aRatingItems = array();

	protected $oConfig = null;
	protected $sRefType = '';
	protected $sSubType = '';
	protected $sRef = '';
	protected $aRatings = array();

	/**
	 * Contructor of the Rating class
	 */
	private function __construct( stdClass $oData, RatingConfig $oConfig ) {
		$this->sRefType = $oData->reftype;
		$this->sRef = $oData->ref;
		$this->sSubType = $oData->subtype;
		$this->loadRating();
	}

	protected static function factory( $sType, $oData ) {
		$oConfig = RatingConfig::factory( $sType );
		if( !$oConfig instanceof RatingConfig ) {
			//TODO: Return a DummyEntity instead of null.
			return null;
		}

		$sItemClass = $oConfig->get( 'RatingClass' );
		$oInstance = new $sItemClass( $oData, $oConfig );
		return static::appendCache( $oInstance );
	}

	/**
	 * @param stdClass $oData
	 * @return Status
	 */
	protected static function ensureBasicParams( stdClass $oData = null ) {
		if( is_null($oData) ) {
			return Status::newFatal( 'No Data Given' ); //TODO
		}
		if( empty($oData->ref) ) {
			return Status::newFatal( 'No reference Given' ); //TODO
		}
		if( empty($oData->reftype) ) {
			return Status::newFatal( 'No reference type Given' ); //TODO
		}
		if( empty($oData->subtype) ) {
			$oData->subtype = 'default';
		}
		return Status::newGood( $oData );
	}

	/**
	 * TODO: real object cache!
	 * @param stdClass $oData
	 * @return RatingItem - or null
	 */
	protected static function getInstanceFromCache( stdClass $oData ) {
		$aItems = static::$aRatingItems;
		if( !isset($aItems) ) {
			return null;
		}
		if( !isset($aItems[$oData->reftype]) ) {
			return null;
		}
		if( !isset($aItems[$oData->reftype][$oData->ref]) ) {
			return null;
		}
		if( !isset($aItems[$oData->reftype][$oData->ref][$oData->subtype]) ) {
			return null;
		}
		return $aItems[$oData->reftype][$oData->ref][$oData->subtype];
	}

	protected static function appendCache( RatingItem $oInstance ) {
		static::$aRatingItems
			[$oInstance->getRefType()]
			[$oInstance->getRef()]
			[$oInstance->getSubType()]
		= $oInstance;
		return $oInstance;
	}

	/**
	 * RatingItem from a set of data
	 * @param stdClass $oData
	 * @return \RatingItem
	 */
	public static function newFromObject( stdClass $oData ) {
		$oStatus = static::ensureBasicParams( $oData );
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}
		$oInstance = self::getInstanceFromCache(
			$oStatus->getValue()
		);
		if( $oInstance instanceof RatingItem ) {
			return $oInstance;
		}
		return static::factory( $oData->reftype, $oData );
	}

	/**
	 * loads the ratings from the bs_rating table
	 * @return boolean
	 */
	protected function loadRating() {
		$aConditions = array( 
			'rat_reftype' => $this->getRefType(),
			'rat_subtype' => $this->getSubType(),
			'rat_ref' => $this->getRef(),
			'rat_archived' => '0'
		);

		//Abort query when hook-handler returns false
		$bReturn = wfRunHooks('BSRatingBeforeLoadRatingQuery', array(
			$this->getRef(),
			$this->getRefType(),
			&$aConditions
		));
		if( !$bReturn ) {
			return true;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			'bs_rating',
			'*',
			$aConditions,
			__METHOD__
		);
		if( !$res ) {
			return $this->aRatings;
		}

		while($row = $dbr->fetchObject($res)) {
			$this->aRatings[$row->rat_id] = array(
				'id'      => $row->rat_id,
				'reftype' => $row->rat_reftype,
				'ref'     => $row->rat_ref,
				'userid'  => $row->rat_userid,
				'userip'  => $row->rat_userip,
				'value'   => $row->rat_value,
				'created' => $row->rat_created,
				'touched' => $row->rat_touched,
				'subtype' => $row->rat_subtype,
				'context' => $row->rat_context,
			);
		}

		return $this->aRatings;
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
		return static::REFTYPE;
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