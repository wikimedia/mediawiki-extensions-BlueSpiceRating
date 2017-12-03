<?php
/**
 * Rating extension for BlueSpice
 *
 * Provides a rating system.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

namespace BlueSpice\Rating;

/**
 * Base class for Rating extension
 * @package BlueSpice_pro
 * @subpackage Rating
 */
class Rating extends \BlueSpice\Extension {

	public function __construct( array $definition, \IContextSource $context, \Config $config) {
		parent::__construct( $definition, $context, $config );

		$core = \BsCore::getInstance();
		$core->registerBehaviorSwitch( 'bs_norating' );
	}
	/**
	 * Initialization of Rating extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsConfig::registerVar(
			'MW::Rating::enRatingNS',
			array( NS_MAIN ),
			BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_INT | BsConfig::USE_PLUGIN_FOR_PREFS,
			'bs-rating-toc-enratingns',
			'multiselectex'
		);

		BsConfig::registerVar(
			'MW::Rating::enArticleLikeNS',
			[],
			BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_INT | BsConfig::USE_PLUGIN_FOR_PREFS,
			'bs-rating-toc-enarticlelikens',
			'multiselectex'
		);

		wfProfileOut( 'BS::'.__METHOD__ );
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