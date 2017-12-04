<?php

namespace BlueSpice\Rating\Data;

use BlueSpice\Data\ResultSet;

class RatingSet extends ResultSet {
	public function __construct($records, $total = false ) {
		if( !$total ) {
			$total = count( $records );
		}
		parent::__construct( $records, $total );
	}

	public function retrench( $ratings = false, $rules = [] ) {
		if( !$ratings ) {
			$ratings = $this->getRecords();
		}
		if( empty( $rules ) ) {
			return $ratings;
		}
		return array_filter( $ratings, function( Record $record ) use( $rules ) {
			foreach( $rules as $fieldName => $value ) {
				if( is_null( $record->get( $fieldName ) ) ) {
					return false;
				}
				if( $record->get( $fieldName ) == $value ) {
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
	 * @param Record[] $ratings
	 * @param integer $context
	 * @return Record[]
	 */
	public function getAnonRatings( $ratings = false, $context = 0 ) {
		if( !$ratings ) {
			$ratings = $this->getRatings( $context );
		}
		$ratingsCopy = [];
		array_walk( $ratings, function( Record &$record ) use ( &$ratingsCopy ) {
			$recordCopy = clone( $record );
			$recordCopy->set( Record::USERID, 0 );
			$recordCopy->set( Record::USERIP, '' );
			$ratingsCopy[] = &$recordCopy;
		});

		return $ratingsCopy;
	}

	/**
	 * Returns an array containing all ratings row arrays filtered by context.
	 * @param integer $context
	 * @return array
	 */
	public function getRatings( $context = 0 ) {
		$rules = [];
		if( !empty( $context ) ) {
			$rules[ Record::CONTEXT ] = $context;
		}
		return $this->retrench( $rules );
	}

	/**
	 * Total number of votes
	 * @param integer $context
	 * @return integer
	 */
	public function getTotal( $ratings = false, $context = 0 ) {
		if( !$ratings ) {
			return count( $this->getRatings( $context ) );
		}
		return count( $ratings );
	}

	/**
	 * Total sum of given or all ratings.
	 * Note that this only works for integers!
	 * @param integer $context
	 * @return integer
	 */
	public function getSum( $ratings = false, $context = 0 ) {
		if( !$ratings ) {
			$ratings = $this->getRatings( $context );
		}
		$total = 0;
		if( empty( $ratings )) {
			return $total;
		}
		foreach( $ratings as $rating ) {
			$total += $rating->get( Record::VALUE );
		}
		return $total;
	}

	/**
	 * Average vote value. Note that this only works for integers!
	 * @param integer $context
	 * @return float
	 */
	public function getAverage( $ratings = false, $context = 0 ) {
		if( !$ratings ) {
			$ratings = $this->getRatings( $context );
		}
		return $this->getSum( $ratings, $context ) / $this->getTotal( $ratings, $context );
	}

	/**
	 * returns if the user has already rated
	 * @param \User $user
	 * @param Records[] $ratings
	 * @return boolean - true or false
	 */
	public function hasUserRated( \User $user, $ratings = false, $context = 0 ) {
		return $this->getTotal(
			$this->getUserRatings( $user, $ratings, $context )
		) > 0;
	}

	/**
	 * Returns the users ratings
	 * @param \User $user
	 * @param Records[] $ratings
	 * @param integer $context
	 * @return Records[]
	 */
	public function getUserRatings( \User $user, $ratings = false, $context = 0 ) {
		if( !$ratings ) {
			$ratings = $this->getRatings( $context );
		}
		$rules = [];
		if( $user->getId() > 0 ) {
			$rules[Record::USERID] = $user->getId();
		} else {
			$rules[Record::USERIP] = $user->getName();
		}
		return $this->retrench( $ratings, $rules );
	}
}