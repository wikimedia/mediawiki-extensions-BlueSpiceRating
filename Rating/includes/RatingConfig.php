<?php
/**
 * RatingConfig class for Rating extension
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
 * @package    BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * RatingConfig class for Rating extension
 * @package BlueSpiceFoundation
 */
abstract class RatingConfig {
	protected static $aRatingConfigs = null;
	protected static $aDefaults = array();
	protected $sType = '';

	/**
	 * RatingConfig factory
	 * @param string $sType - Rating type
	 * @return RatingConfig - or null
	 */
	public static function factory( $sType ) {
		if( !is_null(static::$aRatingConfigs) ) {
			if( !isset(static::$aRatingConfigs[$sType]) ) {
				return null;
			}
			return static::$aRatingConfigs[$sType];
		}
		//TODO: Check params and classes
		$aRegisteredEntities = RatingRegistry::getRegisteredRatings();
		foreach( $aRegisteredEntities as $sKey => $sConfigClass ) {
			static::$aRatingConfigs[$sKey] = new $sConfigClass();
			static::$aRatingConfigs[$sKey]->sType = $sKey;
			array_merge(
				static::$aDefaults,
				static::$aRatingConfigs[$sKey]->addGetterDefaults()
			);
		}
		Hooks::run( 'RatingConfigDefaults', array( &static::$aDefaults ) );
		return static::$aRatingConfigs[$sType];
	}

	protected static function getDefault( $sMethod ) {
		if( !isset(static::$aDefaults[$sMethod]) ) {
			return false;
		}
		return static::$aDefaults[$sMethod];
	}

	/**
	 * Getter for config methods
	 * @param string $sMethod
	 * @return mixed - The return value of the internaly called method or the
	 * default
	 */
	public function get( $sMethod ) {
		$sMethod = "get_$sMethod";
		if( !is_callable( array($this, $sMethod) ) ) {
			static::getDefault( $sMethod );
		}
		return $this->$sMethod();
	}

	protected function addGetterDefaults() {}
	abstract protected function get_RatingClass();
	abstract protected function get_TypeMsgKey();

	protected function get_AllowedValues() {
		return array( 1 ); // basic like
	}
	protected function get_UserCanRemoveVote() {
		return true;
	}
}