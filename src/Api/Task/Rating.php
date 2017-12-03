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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_pro
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

namespace BlueSpice\Rating\Api\Task;
/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpice_pro
 */
class Rating extends \BSApiTasksBase {

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

	public function task_vote( $taskData, $aParams ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$ratingFactory = \MediaWiki\MediaWikiServices::getInstance()
			->getService( 'BSRatingFactory' );
		$status = $ratingFactory->ensureBasicParams( $taskData );
		if( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}
		$rating = $ratingFactory->newFromObject( $taskData );
		$title = null;
		if( !isset($taskData->value) ) {
			$taskData->value = false;
		}
		if( !empty($taskData->articleid) ) {
			$title = \Title::newFromID( $taskData->articleid );
		}
		if( !empty($taskData->titletext) ) {
			$title = \Title::newFromText( $taskData->titletext );
		}

		$status = $rating->vote(
			$this->getUser(),
			$taskData->value,
			$this->getUser(),
			0,
			$title
		);
		if( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}

		if( $title ) {
			$title->invalidateCache();
		}

		$result->success = true;
		$result->payload['data'] = \FormatJson::encode( $rating );

		return $result;
	}

	public function task_reload( $taskData, $aParams ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$ratingFactory = \MediaWiki\MediaWikiServices::getInstance()
			->getService( 'BSRatingFactory' );
		$status = $ratingFactory->ensureBasicParams( $taskData );
		if( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}
		$rating = $ratingFactory->newFromObject( $taskData );

		$result->success = true;
		$result->payload['data'] = \FormatJson::encode( $rating );

		return $result;
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