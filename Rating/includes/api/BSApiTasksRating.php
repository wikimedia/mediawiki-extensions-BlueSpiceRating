<?php
/**
 * Provides the base api for Rating.
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
 * @package    Bluespice_pro
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpice_pro
 */
class BSApiTasksRating extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'vote',
		'reload',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array('taskname' => array('read', 'edit'))
	 * @return type
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'vote' => array( 'read' ),
			'reload' => array( 'read' ),
		);
	}

	public function task_vote( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		$oStatus = RatingItem::ensureBasicParams( $oTaskData );
		if( !$oStatus->isOK() ) {
			$oResult->message = $oStatus->getHTML();
			return $oResult;
		}
		$oRatingItem = RatingItem::newFromObject( $oTaskData );
		$oTitle = null;
		if( !isset($oTaskData->value) ) {
			$oTaskData->value = false;
		}
		if( !empty($oTaskData->articleid) ) {
			$oTitle = Title::newFromID( $oTaskData->articleid );
		}
		if( !empty($oTaskData->titletext) ) {
			$oTitle = Title::newFromText( $oTaskData->titletext );
		}

		$oStatus = $oRatingItem->vote(
			$this->getUser(),
			$oTaskData->value,
			$this->getUser(),
			0,
			$oTitle
		);
		if( !$oStatus->isOK() ) {
			$oResult->message = $oStatus->getHTML();
			return $oResult;
		}

		if( $oTitle ) {
			$oTitle->invalidateCache();
		}

		$oResult->success = true;
		$oResult->payload['data'] = json_encode( $oRatingItem );

		return $oResult;
	}

	public function task_reload( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		$oStatus = RatingItem::ensureBasicParams( $oTaskData );
		if( !$oStatus->isOK() ) {
			$oResult->message = $oStatus->getHTML();
			return $oResult;
		}
		$oRatingItem = RatingItem::newFromObject( $oTaskData );

		$oResult->success = true;
		$oResult->payload['data'] = json_encode( $oRatingItem );

		return $oResult;
	}

	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		return parent::getAllowedParams() + array(

		);
	}

	/**
	 * Returns the basic param descriptions
	 * @return array
	 */
	public function getParamDescription() {
		return parent::getParamDescription() + array(

		);
	}

	/**
	 * Returns the bsic description for this module
	 * @return type
	 */
	public function getDescription() {
		return array(
			'BSApiTasksBase: This should be implemented by subclass'
		);
	}

	/**
	 * Returns the basic example
	 * @return type
	 */
	public function getExamples() {
		return array(
			'api.php?action='.$this->getModuleName().'&task='.$this->aTasks[0].'&taskData={someKey:"someValue",isFalse:true}',
		);
	}
}