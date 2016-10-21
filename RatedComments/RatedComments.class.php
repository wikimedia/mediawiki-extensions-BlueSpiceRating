<?php
/**
 * RatedComments extension for BlueSpice
 *
 * Combines Rating and Shoutbox to "Ratbox^^".
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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_pro
 * @subpackage RatedComments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for RatedComments extension
 * @package BlueSpice_pro
 * @subpackage RatedComments
 */
class RatedComments extends BsExtensionMW {

	private $bShoutBoxPrefsOverwritten = false;
	private $aShoutedUserIDsForTitle = array();

	/**
	 * Initialization of RatedComments extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsConfig::registerVar(
			'MW::RatedComments::enRatedCommentsNS',
			array(),
			BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_INT | BsConfig::USE_PLUGIN_FOR_PREFS,
			'bs-ratedcomments-toc-enratedcommentsns',
			'multiselectex'
		);

		$this->setHook( 'BSShoutBoxBeforeAddViewAfterArticleContent' );
		$this->setHook( 'BSShoutBoxGetShoutsBeforeQuery' );
		$this->setHook( 'BSRatingBeforeStateBarTopViewAdd' );
		$this->setHook( 'BSRatingBeforeHeadlineViewAdd' );
		$this->setHook( 'BSRatingBeforeStateBarBodyViewAdd' );
		$this->setHook( 'BSRatingBeforeLoadRatingQuery' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSRatingVoteComplete' );

		// TODO: Deprecated use of mCore
		// TODO: Also declare rating-write and rating-archive. These should be renamed to follow our naming conventions
		$this->mCore->registerPermission( 'ratedcommentedit' );

		BsConfig::registerVar(
			'MW::RatedComments::NumberOfComments',
			3,
			BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT,
			'bs-ratedcomments-pref-NumberOfComments',
			'int'
		);
		BsConfig::registerVar(
			'MW::RatedComments::MaxTitleLength',
			255,
			BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT,
			'bs-ratedcomments-pref-MaxTitleLength',
			'int'
		);
		BsConfig::registerVar(
			'MW::RatedComments::MaxMessageLength',
			2000,
			BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT,
			'bs-ratedcomments-pref-MaxMessageLength',
			'int'
		);

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for BlueSpice hook BSRatingRegisterCustomTypes - used to register own rating type
	 * @param Array $aRatingTypes
	 * @return boolean - always true
	 */
	public static function onBSRatingRegisterCustomTypes( &$aRatingTypes ) {
		$aRatingTypes['ratedcomments'] = array(
			'displaytitle' => wfMessage('bs-ratedcomments-ratingtype-ratedcomments'),
			'view' => 'ViewRatedCommentsRatingItemHelpful',
			'icon-path' => BsConfig::get( 'MW::ScriptPath' ).'/bluespice-mw/ext/Rating/images/',
			'allowedvalues'	=> array(-1,1),
		);
		return true;
	}
	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if( !$this->checkContext($oSkin->getTitle()) ) return true;

		BsExtensionManager::setContext('MW::RatedComments');
		$oOutputPage->addModules('ext.bluespice.ratedComments');
		$oOutputPage->addModuleStyles('ext.bluespice.yRatedComments.styles');
		return true;
	}

	/**
	 * Hook-Handler for hook BSRatingBeforeStateBarTopViewAdd - used to set votable to false
	 * @param RatingItem $oRatingItem
	 * @param ViewStateBarTopElementRating $oTopView
	 * @return boolean - always true
	 */
	public function onBSRatingBeforeStateBarTopViewAdd( &$oRatingItem, &$oTopView ) {
		if( !$this->checkContext($this->getTitle()) ) return true;

		$oTopView = $oRatingItem->getView(null, 'ViewStateBarTopElementRatedComments' );
		$oTopView->setVotable( false );
		return true;
	}

	/**
	 * Hook-Handler for hook BSRatingBeforeStateBarTopViewAdd - used to set votable to false
	 * @param RatingItem $oRatingItem
	 * @param ViewStateBarTopElementRating $oTopView
	 * @return boolean - always true
	 */
	public function onBSRatingBeforeHeadlineViewAdd( &$oRatingItem, &$oView, &$oTitle ) {
		if( !$this->checkContext($oTitle) ) return true;

		$oView = $oRatingItem->getView(null, 'ViewHeadlineElementRatedComments' );
		$oView->setVotable( false );
		return true;
	}

	/**
	 * Hook-Handler for hook BSRatingBeforeStateBarTopViewAdd - used to unset StateBarBodyView
	 * @param RatingItem $oRatingItem
	 * @param ViewStateBarBodyElementRating $oTopView
	 * @return boolean - always true
	 */
	public function onBSRatingBeforeStateBarBodyViewAdd( &$oRatingItem, &$oBodyView, &$oTitle ) {
		if( !$this->checkContext($oTitle) ) return true;

		$oBodyView = null;
		return true;
	}

	public function onBSRatingVoteComplete( $oRatingItem, $oView, $oTitle ) {
		if( !$this->checkContext($oTitle) ) return true;
		if( !$oView instanceof ViewStateBarBodyElementRating && !$oView instanceof ViewStateBarTopElementRating ) return true;

		$oView->setVotable( false );
		return true;
	}

	/**
	 * Hook-Handler for hook BSRatingBeforeLoadRatingQuery - used to filter by user ids
	 * @param array $aConditions
	 * @return boolean true or false - return false to break following query
	 */
	public function onBSRatingBeforeLoadRatingQuery( $sRef, $sRefType, &$aConditions ) {
		if( $sRefType !== 'article' ) return true;

		$oTitle = Title::newFromID((int) $sRef);
		if( !$this->checkContext($oTitle) ) return true;

		$aShoutedUserIDs = $this->getShoutedUserIDsForTitle( $oTitle );
		if( empty($aShoutedUserIDs) ) return false;

		$aConditions['rat_userid'] = $aShoutedUserIDs;
		return true;
	}

	/**
	 * Hook-Handler for hook BSShoutBoxGetShoutsBeforeQuery - Used for own query and view processing
	 * @param string $sOutput
	 * @param int $iArticleId
	 * @param int $iLimit
	 * @return boolean - return false to break following query
	 */
	public function onBSShoutBoxGetShoutsBeforeQuery( &$sOutput, $iArticleId, &$iLimit, &$aTables, &$aFields, &$aConditions, &$aOptions, $oReturn ){
		if( !$this->checkContext( Title::newFromID($iArticleId)) ) return true;

		if( $iLimit == 0 ) {
			$iLimit = BsConfig::get( 'MW::RatedComments::NumberOfComments' );
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				array('bs_shoutbox', 'bs_rating'),
				'*',
				array(
					'sb_page_id' => $iArticleId,
					'sb_archived' => '0',
					'rat_archived' => '0',
					'sb_user_id = rat_userid',
					'sb_page_id = rat_ref',
					'sb_title != ""', //quick and dirty
				),
				__METHOD__,
				array(
					//'DISTINCT',
					'GROUP BY' => 'sb_user_id',
					//'HAVING' => 'max(sb_timestamp)',
					'ORDER BY' => 'sb_timestamp DESC',
					'LIMIT' => $iLimit + 1,
				)
		);
		$oShoutBoxMessageListView = new ViewShoutBoxMessageListRatedComments();
		$oShoutBoxMessageListView->setTitle( Title::newFromID($iArticleId) );

		if( $dbr->numRows( $res ) > $iLimit ) {
			$oShoutBoxMessageListView->setMoreLimit(
				$iLimit + BsConfig::get( 'MW::RatedComments::NumberOfComments' )
			);
		}

		$bShowAge  = BsConfig::get( 'MW::ShoutBox::ShowAge' );
		$bShowUser = BsConfig::get( 'MW::ShoutBox::ShowUser' );

		$iCount = 0;
		while( $row = $dbr->fetchRow( $res ) ) {
			$oUser = User::newFromId( $row['sb_user_id'] );
			$oProfile = $this->mCore->getUserMiniProfile( $oUser );
			$oShoutBoxMessageView = new ViewRatedCommentsShoutBoxMessage();
			if ( $bShowAge )  $oShoutBoxMessageView->setDate( BsFormatConverter::mwTimestampToAgeString( $row[ 'sb_timestamp' ], true ) );
			if ( $bShowUser ) $oShoutBoxMessageView->setUsername( $row[ 'sb_user_name' ] );
			$oShoutBoxMessageView->setUser( $oUser );
			$oShoutBoxMessageView->setMiniProfile( $oProfile );
			$oShoutBoxMessageView->setMessage( $row[ 'sb_message' ] );
			$oShoutBoxMessageView->setShoutID( $row[ 'sb_id' ] );
			$oShoutBoxMessageView->setTitle( $row[ 'sb_title' ] );
			$oShoutBoxMessageView->setArticleID( $iArticleId );
			$oShoutBoxMessageListView->addItem( $oShoutBoxMessageView );
			// Since we have one more shout than iLimit, we need to count :)
			$iCount++;
			if ( $iCount >= $iLimit ) break;
		}

		$dbr->freeResult( $res );

		$oReturn->payload['html'] = $oShoutBoxMessageListView->execute();
		$oReturn->success = true;
		return false;
	}

	/**
	 * Hook-Handler for hook BSShoutBoxBeforeAddViewAfterArticleContent - Used to replace view
	 * @param ViewRatedCommentsShoutBox $oShoutBoxView
	 * @return boolean always true - keeps hooksystem running
	 */
	public function onBSShoutBoxBeforeAddViewAfterArticleContent( &$oShoutBoxView ) {
		if( !$this->checkContext($this->getTitle()) ) return true;

		$oShoutBoxView = new ViewRatedCommentsShoutBox();

		return true;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @param type $default
	 * @return Array
	 */
	private function getShoutedUserIDsForTitle( $oTitle ) {
		if( !$oTitle->exists() ) return array();

		$iArticleID = $oTitle->getArticleID();
		if( isset($this->aShoutedUserIDsForTitle[$iArticleID]) )
			return $this->aShoutedUserIDsForTitle[$iArticleID];

		$dbr = wfGetDB( DB_SLAVE );
		$rRes = $dbr->select(
				array('bs_shoutbox'),
				'sb_user_id',
				array(
					'sb_page_id' => $iArticleID,
					'sb_archived' => '0',
					'sb_title != ""', //quick and dirty
				)
		);

		$aUserIDs = array();
		while( $row = $dbr->fetchRow($rRes) ) {
			$aUserIDs[] = $row['sb_user_id'];
		}

		return $aUserIDs;
	}

	/**
	 * Overwrites ShoutBox settings
	 * @return
	 */
	private function overwriteShoutBoxPrefs() {
		if( $this->bShoutBoxPrefsOverwritten ){
			return;
		}

		BsConfig::set(
			'MW::ShoutBox::NumberOfShouts',
			BsConfig::get( 'MW::RatedComments::NumberOfComments' )
		);

		return;
	}

	/**
	 * @param Title $oTitle
	 * @return boolean - true or false
	 */
	private function checkContext( $oTitle ) {
		if( !is_object( $oTitle ) ){
			return false;
		}
		if( $oTitle->isRedirect() ) {
			if( $this->getRequest()->getVal('redirect') != 'no' ) {
				$oTitle = BsArticleHelper::getInstance( $oTitle )
					->getTitleFromRedirectRecurse();
			}
			if( !$oTitle || !$oTitle->exists() || $oTitle->isRedirect() ) {
				return false;
			}
		}
		if( $oTitle->exists() === false ) {
			return false;
		}
		if( $oTitle->getNamespace() === NS_SPECIAL ) {
			return false;
		}

		$b = in_array(
			$oTitle->getNamespace(),
			BsConfig::get( 'MW::Rating::enRatingNS' )
		) && in_array(
			$oTitle->getNamespace(),
			BsConfig::get( 'MW::RatedComments::enRatedCommentsNS' )
		);

		if( !$b ) {
			return false;
		}

		$vNoRating = BsArticleHelper::getInstance( $oTitle )->getPageProp(
			'bs_norating'
		);
		if( $vNoRating === '' ) {
			return false;
		}

		if( !$oTitle->userCan('readshoutbox') ) {
			return false;
		}
		if( !$oTitle->userCan('rating-read') ) {
			return false;
		}

		$this->overwriteShoutBoxPrefs();
		return true;
	}
	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		wfProfileIn( 'BS::' . __METHOD__ );
		$aPrefs = array();

		switch ($oVariable->getName()) {
			case 'enRatedCommentsNS':
				global $wgContLang;
				$aExcludeNmsps = array( NS_MEDIAWIKI, NS_SPECIAL, NS_MEDIA );
				foreach ( $wgContLang->getNamespaces() as $sNamespace ) {
					$iNsIndex = $wgContLang->getNsIndex( $sNamespace );
					if ( !MWNamespace::isTalk( $iNsIndex ) ) continue;
					$aExcludeNmsps[] = $iNsIndex;
				}
				$aPrefs['type']		= 'multiselectex';
				$aPrefs['options']	= BsNamespaceHelper::getNamespacesForSelectOptions( $aExcludeNmsps );
				break;
			default:
		}

		wfProfileOut( 'BS::' . __METHOD__ );
		return $aPrefs;
	}
}