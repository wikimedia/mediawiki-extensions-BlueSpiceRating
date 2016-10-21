<?php
/**
 * Provides the RatedComments api for BlueSpice.
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
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * RatedComments Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksRatedComments extends BSApiTasksShoutBox {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'updateRatedComment',
		'insertRatedComment',
		'archiveRatedComment',
		'getMessageForm',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'updateRatedComment' => array( 'writeshoutbox', 'rating-write' ),
			'insertRatedComment' => array( 'writeshoutbox', 'rating-write' ),
			'archiveRatedComment' => array( 'writeshoutbox', 'rating-write' ),
			'getMessageForm' => array( 'writeshoutbox', 'rating-write' ),
		);
	}

	protected function task_insertRatedComment( $oTaskData, $aParams ) {
		$oReturn = $this->makeStandardReturn();
		$iArticleId = isset( $oTaskData->articleId )
			? (int) $oTaskData->articleId
			: 0
		;
		if( $iArticleId < 1 ) {
			return $oReturn; //TODO: error message
		}

		$sMessage = isset( $oTaskData->message )
			? (string) $oTaskData->message
			: ''
		;
		if( empty($sMessage) ) {
			return $oReturn; //TODO: error message
		}
		if( strlen($sMessage) > BsConfig::get( 'MW::RatedComments::MaxMessageLength' ) ) {
			$sMessage = substr(
				$sMessage,
				0,
				BsConfig::get( 'MW::RatedComments::MaxMessageLength' )
			);
		}
		BsConfig::set(
			'MW::ShoutBox::MaxMessageLength',
			BsConfig::get( 'MW::RatedComments::MaxMessageLength' )
		);

		$iRatingValue = isset( $oTaskData->rating )
			? (int) $oTaskData->rating
			: 0
		;
		if( $iRatingValue < 1 ) {
			return $oReturn; //TODO: error message
		}

		$sTitle = isset( $oTaskData->title )
			? (string) $oTaskData->title
			: ''
		;
		if( empty($sTitle) ) {
			return $oReturn; //TODO: error message
		}
		$sTitle = htmlspecialchars( $sTitle, ENT_QUOTES, 'UTF-8' );
		if( strlen($sTitle) > BsConfig::get( 'MW::RatedComments::MaxTitleLength' ) ) { //TODO: add to settings
			$sTitle = substr(
				$sTitle,
				0,
				BsConfig::get( 'MW::RatedComments::MaxTitleLength' )
			);
		}

		$oReturn = $this->task_insertShout( $oTaskData, $aParams );
		if( !$oReturn->success || empty($oReturn->payload['sb_id']) ) {
			return $oReturn;
		}

		$oReturn->success = $this->getDB( DB_MASTER )->update(
			'bs_shoutbox',
			array( 'sb_title' => $sTitle, 'sb_touched' => wfTimestampNow() ),
			array( 'sb_id' => (int)$oReturn->payload['sb_id']),
			__METHOD__
		);
		if( !$oReturn->success ) {
			return $oReturn;
		}
		$oRatingItem = RatingItem::getInstance( 'article', $iArticleId );
		$oReturn->success = $oRatingItem->setRating(
			$iArticleId,
			$iRatingValue,
			'article',
			$this->getUser()->getID(),
			$this->getUser()->getName()
		);

		return $oReturn;
	}

	protected function task_updateRatedComment( $oTaskData, $aParams ) {
		$oReturn = $this->makeStandardReturn();

		$iShoutID = isset( $oTaskData->shoutId )
			? (int)$oTaskData->shoutId
			: 0
		;
		if( $iShoutID < 1 ) {
			return $oReturn; //TODO: error message
		}

		$iArticleId = isset( $oTaskData->articleId )
			? (int) $oTaskData->articleId
			: 0
		;
		if( $iArticleId < 1 ) {
			return $oReturn; //TODO: error message
		}

		$sMessage = isset( $oTaskData->message )
			? (string) $oTaskData->message
			: ''
		;
		if( empty($sMessage) ) {
			return $oReturn; //TODO: error message
		}
		if( strlen($sMessage) > BsConfig::get( 'MW::RatedComments::MaxMessageLength' ) ) {
			$sMessage = substr(
				$sMessage,
				0,
				BsConfig::get( 'MW::RatedComments::MaxMessageLength' )
			);
		}
		BsConfig::set(
			'MW::ShoutBox::MaxMessageLength',
			BsConfig::get( 'MW::RatedComments::MaxMessageLength' )
		);

		$iRatingValue = isset( $oTaskData->rating )
			? (int) $oTaskData->rating
			: 0
		;
		if( $iRatingValue < 1 ) {
			return $oReturn; //TODO: error message
		}

		$sTitle = isset( $oTaskData->title )
			? (string) $oTaskData->title
			: ''
		;
		if( empty($sTitle) ) {
			return $oReturn; //TODO: error message
		}
		$sTitle = htmlspecialchars( $sTitle, ENT_QUOTES, 'UTF-8' );
		if( strlen($sTitle) > BsConfig::get( 'MW::RatedComments::MaxTitleLength' ) ) { //TODO: add to settings
			$sTitle = substr(
				$sTitle,
				0,
				BsConfig::get( 'MW::RatedComments::MaxTitleLength' )
			);
		}

		$rRes = $this->getDB( DB_MASTER )->selectRow(
			'bs_shoutbox',
			'sb_user_id',
			array('sb_id' => $iShoutID)
		);
		if( !$rRes ) {
			return $oReturn; //TODO: error message
		}
		$iShoutUserID = (int)$rRes->sb_user_id;

		if( $iShoutUserID !== $this->getUser()->getId() ) {
			if( !$this->getUser()->isAllowed( 'ratedcommentedit' ) ) {
				return $oReturn;
			}
		}

		$oUser = User::newFromId( $iShoutUserID );
		if( !$oUser ) {
			return $oReturn;
		}

		$oReturn->success = $this->getDB( DB_MASTER )->update(
			'bs_shoutbox',
			array(
				'sb_title' => $sTitle,
				'sb_touched' => wfTimestampNow(),
				'sb_message'    => $sMessage,
			),
			array( 'sb_id' => $iShoutID ),
			__METHOD__
		);
		if( !$oReturn->success ) {
			return $oReturn;
		}
		$oRatingItem = RatingItem::getInstance( 'article', $iArticleId );
		$oReturn->success = $oRatingItem->setRating(
			$iArticleId,
			$iRatingValue,
			'article',
			$oUser->getID(),
			$oUser->getName()
		);

		$oTitle = Title::newFromID( $iArticleId );
		if( $oTitle ) {
			$oTitle->invalidateCache();
		}

		return $oReturn;
	}

	protected function task_archiveRatedComment( $oTaskData, $aParams ) {
		$oReturn = $this->makeStandardReturn();

		$iShoutID = isset( $oTaskData->shoutId )
			? (int) $oTaskData->shoutId
			: 0
		;
		if( $iShoutID < 1 ) {
			return $oReturn; //TODO: error message
		}

		$iArticleId = isset( $oTaskData->articleId )
			? (int) $oTaskData->articleId
			: 0
		;
		if( $iArticleId < 1 ) {
			return $oReturn; //TODO: error message
		}

		if( empty($iArticleId) || empty($iShoutID) ) return false;
		$oTitle = Title::newFromID( $iArticleId );
		if( !$oTitle || !$oTitle->exists() ) {
			return $oReturn;
		}

		$rRes = $this->getDB( DB_MASTER )->selectRow(
			'bs_shoutbox',
			'sb_user_id',
			array( 'sb_id' => $iShoutID)
		);
		if( !$rRes ) {
			return $oReturn;
		}
		$iShoutUserID = (int)$rRes->sb_user_id;

		if( $iShoutUserID !== $this->getUser()->getId() ) {
			$b = ( !$oTitle->userCan( 'archiveshoutbox', $this->getUser() )
				|| !$oTitle->userCan( 'rating-archive', $this->getUser() )
			);
			if( $b ) {
				$oReturn->message = wfMessage(
					'bs-ratedcomments-err-permission'
				)->plain();
				return $oReturn;
			}
		}

		$oShoutedUser = User::newFromId( $iShoutUserID );
		if( !$oShoutedUser ) {
			return $oReturn;
		}
		$oRatingItem = RatingItem::getInstance( 'article', $iArticleId );
		if( !$oRatingItem->archiveRating($oShoutedUser) ) {
			return $oReturn;
		}

		$oHelpfulRatingItem = RatingItem::getInstance(
			'ratedcomments',
			$iShoutID
		);
		if( $oHelpfulRatingItem->getTotal() > 0 ) {
			if( !$oHelpfulRatingItem->archiveRating() ) {
				return $oReturn;
			}
		}

		$oReturn = $this->task_archiveShout( $oTaskData );
		$oTitle->invalidateCache();

		return $oReturn;
	}

	protected function task_getMessageForm( $oTaskData, $aParams ) {
		$oReturn = $this->makeStandardReturn();

		$iShoutID = !empty( $oTaskData->shoutId )
			? (int) $oTaskData->shoutId
			: 0
		;
		$iArticleID = !empty( $oTaskData->articleid )
			? (int) $oTaskData->articleid
			: 0
		;

		if( empty($iArticleID) || empty($iShoutID) ) {
			return $oReturn;
		}

		$dbw = $this->getDB( DB_MASTER );
		$rRes = $dbw->selectRow(
			'bs_shoutbox',
			'*',
			array( 'sb_id' => $iShoutID )
		);
		if( !$rRes ) {
			return $oReturn;
		}

		$iShoutUserID = (int)$rRes->sb_user_id;

		$oUser = User::newFromId( $iShoutUserID );
		if( !$oUser ) {
			return $oReturn;
		}

		if( $iShoutUserID !== $this->getUser()->getId() ) {
			if( !$this->getUser()->isAllowed( 'ratedcommentedit' ) ) {
				return $oReturn;
			}
		}

		$oView = new ViewRatedCommentsShoutBoxMessageForm();
		$oView->setTitle( $rRes->sb_title );
		$oView->setShoutID( $iShoutID );
		$oView->setArticleID( $iArticleID );
		$oView->setMessage( $rRes->sb_message );
		$oView->setUser( $oUser );

		$oReturn->success = true;
		$oReturn->payload = array(
			'shoutId' => $iShoutID,
			'view' => $oView->execute(),
		);

		return $oReturn;
	}
}