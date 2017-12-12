<?php
/**
 * RatingRegistry class for Rating extension
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
 * @package    BlueSpiceFoundation
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * RatingRegistry class for Rating extension
 * @package BlueSpiceRating
 */
class RatingRegistry {
	private function __construct() {}
	private static $bRatingsRegistered = false;
	private static $aRatings = array();

	protected static function runRegister( $bForceReload = false ) {
		if( static::$bRatingsRegistered && !$bForceReload ) {
			return true;
		}

		$b = wfRunHooks( 'RatingRegister', array(
			&self::$aRatings,
		));

		return $b ? static::$bRatingsRegistered = true : $b;
	}

	/**
	 * Returns all registered entities ( type => RatingConfigClass )
	 * @return array
	 */
	public static function getRegisteredRatings() {
		if( !self::runRegister() ) {
			return array();
		}
		return self::$aRatings;
	}

	/**
	 * Checks if given type is a registered Rating
	 * @param string $sType
	 * @return bool
	 */
	public static function isRegisteredType( $sType ) {
		return in_array(
			$sType,
			self::getRegisterdTypeKeys()
		);
	}

	/**
	 * Returns a registered rating by given type
	 * @param string $sType
	 * @return array
	 */
	public static function getRegisteredRatingByType( $sType ) {
		if( !self::isRegisteredType($sType) ) {
			return array();
		}
		return self::$aRatings[$sType];
	}

	/**
	 * Returns all registered rating types
	 * @return array
	 */
	public static function getRegisterdTypeKeys() {
		return array_keys(
			self::getRegisteredRatings()
		);
	}
}