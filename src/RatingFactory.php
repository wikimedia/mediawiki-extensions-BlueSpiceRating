<?php
/**
 * RatingFactory class for BlueSpice
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

class RatingFactory {
	/**
	 *
	 * @var array
	 */
	protected $ratingItems = [];

	/**
	 *
	 * @var RatingRegistry
	 */
	protected $ratingRegistry = null;

	/**
	 *
	 * @var RatingConfigFactory
	 */
	protected $configFactory = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 * @param RatingRegistry $ratingRegistry
	 * @param RatingConfigFactory $configFactory
	 * @param \Config $config
	 * @return Rating | null
	 */
	public function __construct( $ratingRegistry, $configFactory, $config ) {
		$this->ratingRegistry = $ratingRegistry;
		$this->configFactory = $configFactory;
		$this->config = $config;
	}

	protected function factory( $type, $data ) {
		$ratingConfig = $this->configFactory->newFromType( $type );
		if( !$ratingConfig instanceof RatingConfig ) {
			//TODO: Return a DummyRating instead of null.
			return null;
		}

		$ratingClass = $ratingConfig->get( 'RatingClass' );
		return $ratingClass::newFromFactory( $data, $ratingConfig, $this );
	}

	/**
	 * @param \stdClass $data
	 * @return Status
	 */
	public function ensureBasicParams( \stdClass $data = null ) {
		if( is_null($data) ) {
			return \Status::newFatal( 'No Data Given' ); //TODO
		}
		if( empty($data->ref) ) {
			return \Status::newFatal( 'No reference Given' ); //TODO
		}
		if( empty($data->reftype) ) {
			return \Status::newFatal( 'No reference type Given' ); //TODO
		}
		if( empty($data->subtype) ) {
			$data->subtype = 'default';
		}
		return \Status::newGood( $data );
	}

	/**
	 * RatingItem from a set of data
	 * @param \stdClass $data
	 * @return \RatingItem
	 */
	public function newFromObject( \stdClass $data ) {
		$status = $this->ensureBasicParams( $data );
		if( !$status->isOK() ) {
			return null;
		}
		$instance = $this->getInstanceFromCache(
			$status->getValue()
		);
		if( $instance instanceof RatingItem ) {
			return $instance;
		}
		return $this->factory( $data->reftype, $data );
	}

	/**
	 * TODO: real object cache!
	 * @param \stdClass $data
	 * @return RatingItem - or null
	 */
	protected function getInstanceFromCache( \stdClass $data ) {
		if( !isset($this->ratingItems[$data->reftype]) ) {
			return null;
		}
		if( !isset($this->ratingItems[$data->reftype][$data->ref]) ) {
			return null;
		}
		if( !isset($this->ratingItems[$data->reftype][$data->ref][$data->subtype]) ) {
			return null;
		}
		return $this->ratingItems[$data->reftype][$data->ref][$data->subtype];
	}

	/**
	 * @param RatingItem $instance
	 * @return \RatingItem
	 */
	protected function appendCache( RatingItem $instance ) {
		$this->ratingItems
			[$instance->getRefType()]
			[$instance->getRef()]
			[$instance->getSubType()]
		= $instance;
		return $instance;
	}

	/**
	 *
	 * @param RatingItem $instance
	 * @return bool
	 */
	public function invalidateCache( RatingItem $instance ) {
		if( !isset($this->ratingItems[$instance->getRefType()]) ) {
			return false;
		}
		if( !isset($this->ratingItems[$instance->getRefType()][$instance->getRef()]) ) {
			return false;
		}
		if( !isset($this->ratingItems[$instance->getRefType()][$instance->getRef()][$instance->getSubType()]) ) {
			return false;
		}
		unset( $this->ratingItems
			[$instance->getRefType()]
			[$instance->getRef()]
			[$instance->getSubType()]
		);
		return true;
	}
}
