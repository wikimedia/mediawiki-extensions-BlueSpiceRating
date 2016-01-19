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
 * For further information visit http://www.blue-spice.org
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_pro
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
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
		'reloadRating',
	);

	public function task_vote( $vTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		if( !$this->getUser()->isAllowed('rating-write') || !$this->getUser()->isAllowed('rating-read') ) {
			$oResult->message = wfMessage( 'badaccess-groups' );
			return $oResult;
		}
		if( empty($vTaskData->refType) || empty($vTaskData->ref) || !isset($vTaskData->value) ) {
			//$oResult->message = wfMessage( 'badaccess-groups' );
			return $oResult;
		}

		$oRatingItem = RatingItem::getInstance(
			$vTaskData->refType,
			$vTaskData->ref,
			isset( $vTaskData->subType ) ? $vTaskData->subType : ''
		);
		wfRunHooks( 'BSRatingBeforeVote', array(&$oRatingItem) );

		$oResult->success = $oRatingItem->setRating(
			$vTaskData->ref,
			$vTaskData->value,
			$vTaskData->refType,
			$this->getUser()->getID(),
			$this->getUser()->getName()
		);

		$sViewName = '';
		if( !empty($vTaskData->view) ) {
			$sViewName = $vTaskData->view;
		}

		$oView = $oRatingItem->getView(
			empty( $vTaskData->userID )
				? null
				: User::newFromId( $vTaskData->userID ),
			empty( $vTaskData->view )
				? ''
				: $vTaskData->view
		);
		$oView->setVotable(
			isset( $vTaskData->votable ) && $vTaskData->votable == "true"
				? true
				: false
		);

		$oTitle = null;
		if( !empty($vTaskData->articleID) ) {
			$oTitle = Title::newFromID( $vTaskData->articleID );
			$oTitle->invalidateCache();
		}

		wfRunHooks( 'BSRatingVoteComplete', array(
			$oRatingItem,
			$oView,
			$oTitle
		));
		$oResult->payload['view'] = $oView->execute();

		return $oResult;
	}

	public function task_reloadRating( $vTaskData, $aParams) {
		//$sRefType, $sRef, $sViewName = '', $sVotable = "", $iUserID = 0, $sSubType = ""
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		if( !$this->getUser()->isAllowed('rating-read') ) {
			$oResult->message = wfMessage( 'badaccess-groups' );
			return $oResult;
		}

		if( empty($vTaskData->refType) || empty($vTaskData->ref) || !isset($vTaskData->value) ) {
			//$oResult->message = wfMessage( 'badaccess-groups' );
			return $oResult;
		}

		$oRatingItem = RatingItem::getInstance(
			$vTaskData->refType,
			$vTaskData->ref,
			isset( $vTaskData->subType ) ? $vTaskData->subType : ''
		);

		$sViewName = '';
		if( !empty($vTaskData->view) ) {
			$sViewName = $vTaskData->view;
		}

		$oView = $oRatingItem->getView(
			empty( $vTaskData->userID )
				? null
				: User::newFromId( $vTaskData->userID ),
			empty( $vTaskData->view )
				? ''
				: $vTaskData->view
		);
		$oView->setVotable(
			isset( $vTaskData->votable ) && $vTaskData->votable == "true"
				? true
				: false
		);

		$oResult->payload['view'] = $oView->execute();

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