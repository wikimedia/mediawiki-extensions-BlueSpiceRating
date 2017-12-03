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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

namespace BlueSpice\Rating;
/**
 * RatingItem class for Rating extension
 * @package BlueSpiceRating
 * @subpackage Rating
 */
class RatingItem implements \JsonSerializable {

	protected $config = null;
	protected $refType = '';
	protected $subType = 'default';
	protected $sRef = '';
	protected $ratings = null;

	/**
	 * Contructor of the Rating class
	 */
	private function __construct( \stdClass $data, RatingConfig $config ) {
		$this->refType = $data->reftype;
		$this->sRef = $data->ref;
		$this->subType = $data->subtype;
		$this->config = $config;
		$this->loadRating();
	}

	public function jsonSerialize() {
		$ratings = $this->getRatings();
		if( $this->getConfig()->get( 'IsAnonymous' ) ) {
			$ratings = $this->getAnonRatings( $ratings );
		}
		$aUserRatings = $this->getRatingsOfSpecificUser(
			\RequestContext::getMain()->getUser()
		);

		return [
			'reftype' => $this->getRefType(),
			'ref' => $this->getRef(),
			'subtype' => $this->getSubType(),
			'ratings' => $ratings,
			'userratings' => $aUserRatings,
		];
	}

	/**
	 * Returns the instance - Should not be used directly. This is a workaround
	 * as all rating __construct methods are protected. Use mediawiki service
	 * 'BSRatingFactory' instead
	 * @param \stdClass $data
	 * @param RatingConfig $config
	 * @param RatingFactory $ratingFactory
	 * @return static
	 */
	public static function newFromFactory( \stdClass $data, RatingConfig $config, RatingFactory $ratingFactory ) {
		return new static( $data, $config );
	}

	/**
	 * @param mixed $value
	 * @param integer $context
	 * @return \Status
	 */
	public function checkValue( $value = false, $context = 0 ) {
		if( $this->getConfig()->get( 'MultiValue' ) && empty( $context ) ) {
			return \Status::newFatal(
				'Context cannot be empty when multivalue!'
			);
		}
		if( $value === false ) {
			//stands for a delete
			return \Status::newGood( $value );
		}
		if( !$this->isAllowedValue( $value ) ) {
			return \Status::newFatal( 'Value not allowed' ); //TODO
		}
		return \Status::newGood( $value );
	}

	/**
	 * @param mixed $value
	 * @return \Status
	 */
	public function isAllowedValue( $value = false ) {
		if( $value === false ) {
			return \Status::newGood( $value );
		}
		$allowedValues = $this->getConfig()->get( 'AllowedValues' );
		return in_array( $value, $allowedValues )
			? \Status::newGood( $value )
			: \Status::newFatal( 'Value not allowed' ) //TODO
		;
	}

	protected function checkPermission( $action, \User $user, \Title $title = null ) {
		$action = ucfirst( $action );//...
		$permission = $this->getConfig()->get( "{$action}Permission" );
		if( !$permission ) {
			return false;
		}
		if( $title instanceof \Title ) {
			return $title->userCan( $permission, $user );
		}
		return $user->isAllowed( $permission );
	}

	/**
	 * @param \User $user
	 * @return \Status
	 */
	public function userCan( \User $user, $action = 'read', \Title $title = null ) {
		$bTitleRequired = $this->getConfig()->get( 'PermissionTitleRequired' );
		if( $bTitleRequired && !$title instanceof \Title ) {
			return \Status::newFatal( "Title Required" ); //TODO
		}
		if( !$this->checkPermission( $action, $user, $title ) ) {
			return \Status::newFatal( "User is not Allowed $action" ); //TODO
		}
		return \Status::newGood( $user );
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
		$this->ratings = [];
		$conditions = array( 
			'rat_reftype' => $this->getRefType(),
			'rat_subtype' => $this->getSubType(),
			'rat_ref' => $this->getRef()
		);

		//Abort query when hook-handler returns false
		$bReturn = \Hooks::run( 'BSRatingBeforeLoadRatingQuery', [
			$this->getRef(),
			$this->getRefType(),
			&$conditions
		]);
		if( !$bReturn ) {
			return true;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			'bs_rating',
			'*',
			$conditions,
			__METHOD__
		);
		if( !$res ) {
			return $this->ratings;
		}

		foreach( $res as $row ) {
			$this->ratings[$row->rat_id] = [
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
			];
		}

		return $this->ratings;
	}

	/**
	 * CRUD votes from the rating item. Use $value = false to delete
	 * @param \User $user - \User, that initiated this action
	 * @param mixed $value - use false to delete
	 * @param \User $owner - \User, that the vote is related to
	 * @param integer $context - context for multi value
	 * @param \Title $title - for permission check!
	 * @return \Status
	 */
	public function vote( \User $user, $value, \User $owner = null, $context = 0, \Title $title = null ) {
		if( !$owner instanceof \User ) {
			$owner = $user;
		}
		$status = $this->checkValue( $value, $context );
		if( !$status->isOK() ) {
			return $status;
		}
		$ratings = $this->getRatingsOfSpecificUser( $owner, $context );
		if( $value === false ) {
			$status = $this->userCan( $user, 'delete', $title );
			if( !$status->isOK() ) {
				return $status;
			}
			if( $owner->getId() != $user->getId() ) {
				$status = $this->userCan( $user, 'deleteOthers', $title );
				if( !$status->isOK() ) {
					return $status;
				}
			}
			if( empty($ratings) ) {
				return \Status::newFatal( 'Nothing to delete!' ); //TODO!
			}
			return $this->deleteRating( $owner, $context);
		}
		$status = $this->userCan( $user, 'update', $title );
		if( !$status->isOK() ) {
			return $status;
		}
		if( $owner->getId() != $user->getId() ) {
			$status = $this->userCan( $user, 'updateOthers', $title );
			if( !$status->isOK() ) {
				return $status;
			}
		}
		if( empty($ratings) ) {
			$status = $this->insertRating( $owner, $value, $context );
		} else {
			$ratings = array_values( $ratings );
			$status = $this->updateRating(
				$owner,
				$value,
				$ratings[0],
				$context
			);
		}
		\Hooks::run( 'BlueSpiceRatingItemVoteSaveComplete', [
			$this,
			$owner,
			$value,
			$context,
		]);

		return $status;
	}

	protected function insertRating( \User $owner, $value, $context = 0 ) {
		$status = \Status::newGood( $this );
		$id = 0;
		\Hooks::run( 'BlueSpiceRatingItemVoteSave', [
			$this,
			$owner,
			&$value,
			&$context,
			$status,
			$id,
		]);
		if( !$status->isOK() ) {
			return $status;
		}
		$aValues = array(
			'rat_value' => $value,
			'rat_ref' => $this->getRef(),
			'rat_reftype' => $this->getRefType(),
			'rat_userid'  => (int) $owner->getId(),
			'rat_userip'  => $owner->getName(),
			'rat_created' => wfTimestampNow(),
			'rat_touched' => wfTimestampNow(),
			'rat_subtype' => $this->getSubType(),
			'rat_context' => $context,
		);
		$success = wfGetDB( DB_MASTER )->insert(
			'bs_rating',
			$aValues,
			__METHOD__
		);
		if( !$success ) {
			return \Status::newFatal( 'insert database error' ); //TODO
		}
		return \Status::newGood( $this->invalidateCache() );
	}

	protected function updateRating( \User $owner, $value, $id, $context = 0 ) {
		$status = \Status::newGood( $this );
		\Hooks::run( 'BlueSpiceRatingItemVoteSave', [
			$this,
			$owner,
			&$value,
			&$context,
			$status,
			$id,
		]);
		if( !$status->isOK() ) {
			return $status;
		}
		$success = wfGetDB( DB_MASTER )->update(
			'bs_rating',
			array( 'rat_value' => $value ),
			array( 'rat_id' => $id ),
			__METHOD__
		);
		if( !$success ) {
			return \Status::newFatal( 'update database error' ); //TODO
		}
		return \Status::newGood( $this->invalidateCache() );
	}

	/**
	 * Deletes all user ratings for this RatingItem
	 * @return \Status
	 */
	public function deleteRatingItem() {
		return $this->deleteRating();
	}

	/**
	 * Deletes given \User rating or all ratings when no \User given
	 * @param \User $user
	 * @param integer $context
	 * @return Boolean - true or false
	 */
	protected function deleteRating( \User $user = null, $context = 0 ) {
		$conditions = array(
			'rat_ref' => $this->sRef,
			'rat_reftype' => $this->refType,
		);

		if( !empty($context) ) {
			$conditions['rat_context'] = $context;
		}

		if( $user instanceof \User ) {
			if( $user->getId() === 0 ) {
				$conditions['rat_userip'] = $user->getName();
			} else {
				$conditions['rat_userid'] = $user->getId();
			}
		}

		$dbr = wfGetDB( DB_SLAVE );

		$b = $dbr->delete( 
			'bs_rating', 
			$conditions 
		);
		return \Status::newGood( $this->invalidateCache() );
	}

	protected function filterRating( $a = [] ) {
		if( empty($a) ) {
			return $this->ratings;
		}
		return array_filter($this->ratings, function($e) use($a) {
			foreach( $a as $sKey => $value ) {
				if( !isset($e[$sKey]) ) {
					return false;
				}
				if( $e[$sKey] == $value ) {
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
	 * @param array $ratings
	 * @param integer $context
	 * @return array
	 */
	public function getAnonRatings( $ratings = false, $context = 0 ) {
		if( !$ratings ) {
			$ratings = $this->getRatings( $context );
		}
		array_walk( $ratings, function( &$e ) {
			$e['userid'] = 0;
			$e['userip'] = '';
		});
		return $ratings;
	}

	/**
	 * Returns an array containing all ratings row arrays filtered by context.
	 * @param integer $context
	 * @return array
	 */
	public function getRatings( $context = 0 ) {
		if( empty($context) ) {
			return $this->filterRating();
		}
		return $this->filterRating(array(
			'context' => $context
		));
	}

	public function getRefType() {
		return $this->refType;
	}

	public function getSubType() {
		return $this->subType;
	}

	public function getRef() {
		return $this->sRef;
	}

	/**
	 * @return RatingConfig
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * Total number of votes
	 * @param integer $context
	 * @return integer
	 */
	public function countRatings( $context = 0 ) {
		return count($this->getRatings( $context ));
	}

	/**
	 * Total sum of all rating. Note that this only works for integers!
	 * @param integer $context
	 * @return integer
	 */
	public function getTotal( $context = 0 ) {
		$filter = [];
		$total = 0;
		if( !empty($context) ) {
			$filter['context'] = $context;
		}
		$ratings = $this->filterRating( $filter );
		if( empty( $ratings )) {
			return $total;
		}
		foreach( $ratings as $id => $rating ) {
			$total += $rating['value'];
		}
		return $total;
	}

	/**
	 * Average vote value. Note that this only works for integers!
	 * @param integer $context
	 * @return float
	 */
	public function getAverage( $context ) {
		return $this->getTotal( $context ) / $this->countRatings( $context );
	}

	/**
	 * returns if the user has already rated
	 * @param \User $user
	 * @param boolean $return
	 * @return boolean - true or false
	 */
	public function hasUserRated( \User $user, $return = false, $context = 0 ) {
		//use name as ip for anonymous
		$userID = $user->getId();
		if( empty( $userID ) ) {
			$userID = $user->getName();
		}
		$aRatedUserIDs = $this->getRatedUserIDs( $context );
		if( in_array( $userID, $aRatedUserIDs ) ) {
			$return = true;
		}
		return $return;
	}

	public function getRatedUserIDs( $context = 0 ) {
		$userIDs = $filter = [];
		if( !empty( $context ) ) {
			$filter['context'] = $context;
		}
		$ratings = $this->filterRating( $filter );
		if( empty( $ratings ) ) {
			return $userIDs;
		}

		foreach( $ratings as $id => $rating ) {
			$userIDs[] = empty( $rating['userid'] )
				? $rating['userip']
				: $rating['userid']
			;
		}
		return $userIDs;
	}

	public function getRatingsOfSpecificUser( \User $user, $context = 0 ) {
		$filter = [];
		$iUserID = $user->getId();
		if( !empty($iUserID) ) {
			$filter['userid'] = $iUserID;
		} else {
			$filter['userip'] = $user->getName();
		}
		if( !empty($context) ) {
			$filter['context'] = $context;
		}
		$ratings = $this->filterRating( $filter );

		return $ratings;
	}

	public function getValueFilteredRatings( $value = false, $context = 0  ) {
		$filter = [
			'value' => $value,
		];
		if( !empty( $context ) ) {
			$filter['context'] = $context;
		}
		return $this->filterRating( $filter );
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
			$this->getConfig()->get( 'HTMLTagOptions' ),
			$this->getTagData()
		);
		return \HTML::element(
			$this->getConfig()->get( 'HTMLTag' ),
			$aOptions
		);
	}

	public function invalidateCache() {
		\MediaWiki\MediaWikiServices::getInstance()
			->getService( 'BSRatingFactory' )
			->invalidateCache( $this );
		$this->loadRating();
		return $this;
	}
}