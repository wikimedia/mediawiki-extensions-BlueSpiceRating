<?php
/**
 * RatingItem class for extension Rating
 *
 * Provides a rating item.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * RatingItem class for Rating extension
 * @package BlueSpiceRating
 * @subpackage Rating
 */
class RatingItem implements JsonSerializable {
	protected static $aRatingItems = array();

	protected $oConfig = null;
	protected $sRefType = '';
	protected $sSubType = '';
	protected $sRef = '';
	protected $aRatings = null;

	/**
	 * Contructor of the Rating class
	 */
	private function __construct( stdClass $oData, RatingConfig $oConfig ) {
		$this->sRefType = $oData->reftype;
		$this->sRef = $oData->ref;
		$this->sSubType = $oData->subtype;
		$this->oConfig = $oConfig;
		$this->loadRating();
	}

	public function jsonSerialize() {
		$aRatings = $this->getRatings();
		if( $this->getConfig()->get( 'IsAnonymous' ) ) {
			$aRatings = $this->getAnonRatings( $aRatings );
		}
		$aUserRatings = $this->getRatingsOfSpecificUser(
			RequestContext::getMain()->getUser()
		);

		return [
			'reftype' => $this->getRefType(),
			'ref' => $this->getRef(),
			'subtype' => $this->getSubType(),
			'ratings' => $aRatings,
			'userratings' => $aUserRatings,
		];
	}

	/**
	 * @param string $sType
	 * @param stdClass $oData
	 * @return \RatingItem
	 */
	protected static function factory( $sType, stdClass $oData ) {
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
	public static function ensureBasicParams( stdClass $oData = null ) {
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

	/**
	 * @param RatingItem $oInstance
	 * @return \RatingItem
	 */
	protected static function appendCache( RatingItem $oInstance ) {
		static::$aRatingItems
			[$oInstance->getRefType()]
			[$oInstance->getRef()]
			[$oInstance->getSubType()]
		= $oInstance;
		return $oInstance;
	}

	protected function invalidateCache() {
		$oInstance = static::detachFromCache( $this );
		$oInstance->loadRating();
		return $oInstance;
	}

	/**
	 * @param RatingItem $oInstance
	 * @return \RatingItem
	 */
	protected static function detachFromCache( RatingItem $oInstance ) {
		unset( static::$aRatingItems
			[$oInstance->getRefType()]
			[$oInstance->getRef()]
			[$oInstance->getSubType()]
		);
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
			return null;
		}
		$oInstance = static::getInstanceFromCache(
			$oStatus->getValue()
		);
		if( $oInstance instanceof RatingItem ) {
			return $oInstance;
		}
		return static::factory( $oData->reftype, $oData );
	}

	/**
	 * @param mixed $mValue
	 * @return Status
	 */
	public function checkValue( $mValue = false, $iContext = 0 ) {
		if( $this->getConfig()->get( 'MultiValue' ) && empty( $iContext ) ) {
			return Status::newFatal(
				'Context cannot be empty when multivalue!'
			);
		}
		if( $mValue === false ) {
			//stands for a delete
			return Status::newGood( $mValue );
		}
		if( !$this->isAllowedValue( $mValue ) ) {
			return Status::newFatal( 'Value not allowed' ); //TODO
		}
		return Status::newGood( $mValue );
	}

	/**
	 * @param mixed $mValue
	 * @return Status
	 */
	public function isAllowedValue( $mValue = false ) {
		if( $mValue === false ) {
			return Status::newGood( $mValue );
		}
		$aAllowedValues = $this->getConfig()->get( 'AllowedValues' );
		return in_array( $mValue, $aAllowedValues )
			? Status::newGood( $mValue )
			: Status::newFatal( 'Value not allowed' ) //TODO
		;
	}

	protected function checkPermission( $sAction, User $oUser, Title $oTitle = null ) {
		$sAction = ucfirst( $sAction );//...
		$sPermission = $this->getConfig()->get( "{$sAction}Permission" );
		if( !$sPermission ) {
			return false;
		}
		if( $oTitle instanceof Title ) {
			return $oTitle->userCan( $sPermission, $oUser );
		}
		return $oUser->isAllowed( $sPermission );
	}

	/**
	 * @param User $oUser
	 * @return Status
	 */
	public function userCan( User $oUser, $sAction = 'read', Title $oTitle = null ) {
		$bTitleRequired = $this->getConfig()->get( 'PermissionTitleRequired' );
		if( $bTitleRequired && !$oTitle instanceof Title ) {
			return Status::newFatal( "Title Required" ); //TODO
		}
		if( !$this->checkPermission( $sAction, $oUser, $oTitle ) ) {
			return Status::newFatal( "User is not Allowed $sAction" ); //TODO
		}
		return Status::newGood( $oUser );
	}

	/**
	 * @return Message
	 */
	public function getTypeMessage() {
		return Message::newFromKey( $this->getConfig()->get( 'TypeMsgKey' ) );
	}

	/**
	 * loads the ratings from the bs_rating table
	 * @return boolean
	 */
	protected function loadRating() {
		$this->aRatings = array();
		$aConditions = array( 
			'rat_reftype' => $this->getRefType(),
			'rat_subtype' => [$this->getSubType(), ''],
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

		foreach( $res as $row ) {
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
	 * CRUD votes from the rating item. Use $mValue = false to delete
	 * @param User $oUser - User, that initiated this action
	 * @param mixed $mValue - use false to delete
	 * @param User $oOwner - User, that the vote is related to
	 * @param integer $iContext - context for multi value
	 * @param Title $oTitle - for permission check!
	 * @return Status
	 */
	public function vote( User $oUser, $mValue, User $oOwner = null, $iContext = 0, Title $oTitle = null ) {
		if( !$oOwner instanceof User ) {
			$oOwner = $oUser;
		}
		$oStatus = $this->checkValue( $mValue, $iContext );
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}
		$aRatings = $this->getRatingsOfSpecificUser( $oOwner, $iContext );
		if( $mValue === false ) {
			$oStatus = $this->userCan( $oUser, 'delete', $oTitle );
			if( !$oStatus->isOK() ) {
				return $oStatus;
			}
			if( $oOwner->getId() != $oUser->getId() ) {
				$oStatus = $this->userCan( $oUser, 'deleteOthers', $oTitle );
				if( !$oStatus->isOK() ) {
					return $oStatus;
				}
			}
			if( empty($aRatings) ) {
				return Status::newFatal( 'Nothing to delete!' ); //TODO!
			}
			return $this->deleteRating( $oOwner, $iContext);
		}
		$oStatus = $this->userCan( $oUser, 'update', $oTitle );
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}
		if( $oOwner->getId() != $oUser->getId() ) {
			$oStatus = $this->userCan( $oUser, 'updateOthers', $oTitle );
			if( !$oStatus->isOK() ) {
				return $oStatus;
			}
		}
		if( empty($aRatings) ) {
			$oStatus = $this->insertRating( $oOwner, $mValue, $iContext );
		} else {
			$aRatings = array_values( $aRatings );
			$oStatus = $this->updateRating(
				$oOwner,
				$mValue,
				$aRatings[0],
				$iContext
			);
		}
		Hooks::run( 'BlueSpiceRatingItemVoteSaveComplete', [
			$this,
			$oOwner,
			$mValue,
			$iContext,
		]);

		return $oStatus;
	}

	protected function insertRating( User $oOwner, $mValue, $iContext = 0 ) {
		$oStatus = Status::newGood( $this );
		$iID = 0;
		Hooks::run( 'BlueSpiceRatingItemVoteSave', [
			$this,
			$oOwner,
			&$mValue,
			&$iContext,
			$oStatus,
			$iID,
		]);
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}
		$aValues = array(
			'rat_value' => $mValue,
			'rat_ref' => $this->getRef(),
			'rat_reftype' => $this->getRefType(),
			'rat_userid'  => (int) $oOwner->getId(),
			'rat_userip'  => $oOwner->getName(),
			'rat_created' => wfTimestampNow(),
			'rat_touched' => wfTimestampNow(),
			'rat_subtype' => $this->getSubType(),
			'rat_context' => $iContext,
		);
		$bSuccess = wfGetDB( DB_MASTER )->insert(
			'bs_rating',
			$aValues,
			__METHOD__
		);
		if( !$bSuccess ) {
			return Status::newFatal( 'insert database error' ); //TODO
		}
		return Status::newGood( $this->invalidateCache() );
	}

	protected function updateRating( User $oOwner, $mValue, $iID, $iContext = 0 ) {
		$oStatus = Status::newGood( $this );
		Hooks::run( 'BlueSpiceRatingItemVoteSave', [
			$this,
			$oOwner,
			&$mValue,
			&$iContext,
			$oStatus,
			$iID,
		]);
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}
		$bSuccess = wfGetDB( DB_MASTER )->update(
			'bs_rating',
			array( 'rat_value' => $mValue ),
			array( 'rat_id' => $iID ),
			__METHOD__
		);
		if( !$bSuccess ) {
			return Status::newFatal( 'update database error' ); //TODO
		}
		return Status::newGood( $this->invalidateCache() );
	}

	/**
	 * Deletes the RatingItem and archives all user ratings
	 * @return Status
	 */
	public function deleteRatingItem() {
		return $this->deleteRating();
	}

	/**
	 * Archives this RatingItem - When User given: Archives rating of given user in this RatingItem
	 * @param User $oUser
	 * @param integer $iContext
	 * @return Boolean - true or false
	 */
	protected function deleteRating( User $oUser = null, $iContext = 0 ) {
		$aConditions = array(
			'rat_ref' => $this->sRef,
			'rat_reftype' => $this->sRefType,
		);

		if( !empty($iContext) ) {
			$aConditions['rat_context'] = $iContext;
		}

		if( $oUser instanceof User ) {
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
		return Status::newGood( $this->invalidateCache() );
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

	/**
	 * Make given ratings anon by removing userid and userip or request ratings
	 * with context and make the result anon.
	 * @param array $aRatings
	 * @param integer $iContext
	 * @return array
	 */
	public function getAnonRatings( $aRatings = false, $iContext = 0 ) {
		if( !$aRatings ) {
			$aRatings = $this->getRatings( $iContext );
		}
		array_walk( $aRatings, function( &$e ) {
			$e['userid'] = 0;
			$e['userip'] = '';
		});
		return $aRatings;
	}

	/**
	 * Returns an array containing all ratings row arrays filtered by context.
	 * @param integer $iContext
	 * @return array
	 */
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

	/**
	 * @return RatingConfig
	 */
	public function getConfig() {
		return $this->oConfig;
	}

	/**
	 * Total number of votes
	 * @param integer $iContext
	 * @return integer
	 */
	public function countRatings( $iContext = 0 ) {
		return count($this->getRatings( $iContext ));
	}

	/**
	 * Total sum of all rating. Note that this only works for integers!
	 * @param integer $iContext
	 * @return integer
	 */
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

	/**
	 * Average vote value. Note that this only works for integers!
	 * @param integer $iContext
	 * @return float
	 */
	public function getAverage( $iContext ) {
		return $this->getTotal( $iContext ) / $this->countRatings( $iContext );
	}

	/**
	 * returns if the user has already rated
	 * @param User $oUser
	 * @param boolean $return
	 * @return boolean - true or false
	 */
	public function hasUserRated( User $oUser, $return = false, $iContext = 0 ) {
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

	public function getValueFilteredRatings( $mValue = false, $iContext = 0  ) {
		$aFilter = array(
			'value' => $mValue,
		);
		if( !empty($iContext) ) {
			$aFilter['context'] = $iContext;
		}
		return $this->filterRating( $aFilter );
	}

	public function getTagData() {
		return array(
			'data-ref' => $this->getRef(),
			'data-subtype' => $this->getSubType(),
			'data-item' => json_encode( $this ),
		);
	}

	public function getTag() {
		$aOptions = array_merge_recursive(
			$this->getConfig()->get('HTMLTagOptions'),
			$this->getTagData()
		);
		return HTML::element(
			$this->getConfig()->get('HTMLTag'),
			$aOptions
		);
	}
}