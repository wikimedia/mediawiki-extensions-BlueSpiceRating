<?php
/**
 * Rating extension for BlueSpice
 *
 * Provides a rating system.
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
 * @package    BlueSpice Pro
 * @subpackage BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

namespace BlueSpice\Rating;

/**
 * Base class for Rating extension
 * @package BlueSpice_pro
 * @subpackage Rating
 */
class Extension extends \BlueSpice\Extension {

	/**
	 *
	 * @param array $definition
	 * @param \IContextSource $context
	 * @param \Config $config
	 */
	public function __construct( array $definition, \IContextSource $context, \Config $config) {
		parent::__construct( $definition, $context, $config );

		$core = \BsCore::getInstance();
		$core->registerBehaviorSwitch( 'bs_norating' );
	}

	/**
	 * Hook handler for BSMigrateSettingsFromDeviatingNames
	 * @param string $oldName
	 * @param string $newName
	 * @return boolean
	 */
	public static function onBSMigrateSettingsFromDeviatingNames( $oldName, &$newName ) {
		if( $oldName === "MW::Rating::enRatingNS" ) {
			$newName = "RatingArticleEnabledNamespaces";
		}
		if( $oldName === "MW::Rating::enArticleLikeNS" ) {
			$newName = "RatingArticleLikeEnabledNamespaces";
		}
		return true;
	}
	/**
	* Hook handler for UnitTestList
	*
	* @param array $paths
	* @return boolean
	*/
	public static function onUnitTestsList( &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}