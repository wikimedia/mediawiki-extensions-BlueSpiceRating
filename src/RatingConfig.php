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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

namespace BlueSpice\Rating;

/**
 * RatingConfig class for Rating extension
 * @package BlueSpiceFoundation
 */
abstract class RatingConfig implements \JsonSerializable, \Config {
	protected $type = '';

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var array
	 */
	protected $defaults = [];

	/**
	 *
	 * @param type $config
	 */
	public function __construct( $config, $type, $defaults = [] ) {
		$this->config = $config;
		$this->type = $type;
		$this->defaults = array_merge(
			$this->addGetterDefaults(),
			$defaults
		);
	}

	/**
	 * RatingConfig factory
	 * @deprecated since version 3.0.0 - Use MediaWikiService
	 * 'RatingConfigFactory' instead
	 * @param string $type - Rating type
	 * @return RatingConfig - or null
	 */
	public static function factory( $type ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		$configFactory = MediaWikiServices::getInstance()->getService(
			'BSRatingConfigFactory'
		);
		return $configFactory->newFromType( $type );
	}

	protected function getDefault( $sOption ) {
		if( isset( $this->defaults[$sOption] ) ) {
			return $this->defaults[$sOption];
		}
		return $this->getConfig()->has( $sOption )
			? $this->getConfig()->get( $sOption )
			: false
		;
	}

	public function getType() {
		return $this->type;
	}

	/**
	 * Getter for config methods
	 * @param string $method
	 * @return mixed - The return value of the internaly called method or the
	 * default
	 */
	public function get( $method ) {
		$method = "get_$method";
		if( !is_callable( array($this, $method) ) ) {
			$this->getDefault( $method );
		}
		return $this->$method();
	}

	/**
	 * check for config methods
	 * @param string $method
	 * @return bool
	 */
	public function has( $method ) {
		$method = "get_$method";
		if( is_callable( array($this, $method) ) ) {
			return true;
		}
		if( isset( $this->defaults[$method] ) ) {
			return true;
		}
		return $this->config->has( $method );
	}

	/**
	 * Returns a json serializable object
	 * @return stdClass
	 */
	public function jsonSerialize() {
		$aConfig = array();
		foreach( get_class_methods( $this ) as $method ) {
			if( strpos($method, 'get_') !== 0 ) {
				continue;
			}
			//remove the get_
			$sVarName = substr( $method, 4 );
			$aConfig[$sVarName] = $this->$method();
		}
		return (object) array_merge(
			$this->defaults,
			$aConfig
		);
	}

	protected function addGetterDefaults() {
		return [];
	}
	abstract protected function get_RatingClass();
	abstract protected function get_TypeMsgKey();

	protected function get_StoreClass() {
		return "\\BlueSpice\\Rating\\Data\\Store";
	}
	protected function get_RatingSetClass() {
		return "\\BlueSpice\\Rating\\Data\\RatingSet";
	}
	protected function get_ModuleScripts() {
		return [ 'ext.bluespice.rating', 'ext.bluespice.ratingItem' ];
	}
	protected function get_ModuleStyles() {
		return [ 'ext.bluespice.rating.styles' ];
	}
	protected function get_AllowedValues() {
		return [ 1 ]; // basic like
	}
	protected function get_UserCanRemoveVote() {
		return true;
	}
	protected function get_MultiValue() {
		return false;
	}
	protected function get_ReadPermission() {
		return 'rating-read';
	}
	protected function get_UpdatePermission() {
		return 'rating-write';
	}
	protected function get_DeletePermission() {
		return 'rating-write';
	}
	protected function get_DeleteOthersPermission() {
		return 'rating-archive';
	}
	protected function get_UpdateOthersPermission() {
		return 'rating-archive';
	}
	protected function get_PermissionTitleRequired() {
		return false;
	}
	protected function get_IsAnonymous() {
		return true;
	}
	protected function get_HTMLTag() {
		return 'div';
	}
	protected function get_HTMLTagOptions() {
		return [
			'class' => ['bs-rating'],
			'data-type' => $this->type,
		];
	}
}