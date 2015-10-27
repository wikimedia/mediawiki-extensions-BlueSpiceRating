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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    2.23.1
 * @package    BlueSpice_pro
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for Rating extension
 * @package BlueSpice_pro
 * @subpackage Rating
 */
class Rating extends BsExtensionMW {

	private $bRatingTypesAlreadySet = false;
	protected $bStateBar = false;

	/**
	 * Contructor of the Rating class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME        => 'Rating',
			EXTINFO::DESCRIPTION => 'bs-rating-extension-description',
			EXTINFO::AUTHOR      => 'Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'BlueSpice pro',
			EXTINFO::URL         => 'https://help.bluespice.com/index.php/Rating',
			EXTINFO::DEPS        => array(
				'bluespice' => '2.23.0'
			)
		);
		$this->mExtensionKey = 'MW::Rating';
		wfProfileOut( 'BS::'.__METHOD__ );
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
			'MW::Rating::RatingTypes',
			array(),
			BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_ARRAY_MIXED,
			'bs-rating-ratingtypes'
		);
		BsConfig::registerVar(
			'MW::Rating::SpecialRatingTypes',
			array(),
			BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_ARRAY_MIXED,
			'bs-rating-specialratingtypes'
		);
		BsConfig::registerVar(
			'MW::Rating::Position',
			'articletitle',
			BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS,
			'bs-rating-pref-position',
			'select'
		);

		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'ParserFirstCallInit' );

		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );

		$this->setHook( 'BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars' );
		$this->setHook( 'BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars' );

		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd', 'onStateBarBeforeBodyViewAdd' );

		$this->mCore->registerBehaviorSwitch( 'bs_norating' );

		$this->mCore->registerPermission( 'rating-write',			array('user') );
		$this->mCore->registerPermission( 'rating-read',			array('*') );
		$this->mCore->registerPermission( 'rating-archive',			array('sysop') );
		$this->mCore->registerPermission( 'rating-viewspecialpage', array('user') );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * registers rating types
	 * @return boolean
	 */
	public function runRegisterCustomTypes() {
		if( $this->bRatingTypesAlreadySet == true ) return false;

		$aRatingTypes = BsConfig::get( 'MW::Rating::RatingTypes' );
		$aSpecialRatingTypes = BsConfig::get( 'MW::Rating::SpecialRatingTypes' );

		$aRatingTypes['article'] = array(
			'displaytitle'	=> wfMessage('bs-rating-types-page')->plain(),
			'view'			=> 'ViewRatingItemStars',
			'icon-path'		=> $this->getImagePath( true ),
			'allowedvalues'	=> range( 1, 5 ),
		);
		$aSpecialRatingTypes[] = 'article';

		wfRunHooks( 'BSRatingRegisterCustomTypes', array(&$aRatingTypes, &$aSpecialRatingTypes) );

		$bRes = BsConfig::set( 'MW::Rating::RatingTypes', $aRatingTypes );
		$bRes = BsConfig::set( 'MW::Rating::SpecialRatingTypes', $aSpecialRatingTypes );

		$this->bRatingTypesAlreadySet = true;
		return $bRes;
	}

	/**
	 * AJAX interface for BlueSpice Rating dialog in StateBar
	 * @global User $wgUser
	 * @param string $sRefType
	 * @param string $sRef
	 * @param int $iValue
	 * @param string $sViewName
	 * @param int $iArticleID
	 * @return string The JSON formatted response
	 */
	public static function ajaxVote( $sRefType, $sRef, $iValue, $sViewName = '', $sVotable = "", $iUserID = 0, $iArticleID = 0, $sSubType = '' ) {
		global $wgUser;
		if( !$wgUser->isAllowed('rating-write') || !$wgUser->isAllowed('rating-read') ) return false;
		if( empty($sRefType) || empty($sRef) || empty($iValue) ) return false;

		$aResult = array();

		$bSuccess = false;
		$iVotable = $sVotable == "true" ? true : false;
		$oUserOnly = empty( $iUserID ) ? null : User::newFromId( $iUserID );

		$oInstance = BsExtensionManager::getExtension('Rating');
		$oInstance->runRegisterCustomTypes();

		$oRatingItem = RatingItem::getInstance( $sRefType, $sRef, $sSubType );
		wfRunHooks( 'BSRatingBeforeVote', array(&$oRatingItem) );

		$bSuccess = $oRatingItem->setRating(
			$sRef,
			$iValue,
			$sRefType,
			$wgUser->getID(),
			$wgUser->getName()
		);

		$aResult['message'] = '';
		$aResult['success'] = $bSuccess;

		if( !$bSuccess ) return false;

		$oView = $oRatingItem->getView($oUserOnly, $sViewName);
		$oView->setVotable( $iVotable );

		$oTitle = null;
		if( !empty($iArticleID) ) {
			$oTitle = Title::newFromID($iArticleID);
			$oTitle->invalidateCache();
		}

		wfRunHooks( 'BSRatingVoteComplete', array($oRatingItem, $oView, $oTitle) );
		$aResult['view'] = $oView->execute();

		return json_encode( $aResult );
	}

	/**
	 * AJAX interface for BlueSpice Rating dialog - reload view
	 * @global User $wgUser
	 * @param string $sRefType
	 * @param string $sRef
	 * @param int $iValue
	 * @param string $sViewName
	 * @return string The JSON formatted response - view output
	 */
	public static function ajaxReloadRating( $sRefType, $sRef, $sViewName = '', $sVotable = "", $iUserID = 0, $sSubType = "") {
		global $wgUser;
		if( !$wgUser->isAllowed('rating-read') ) {
			return false;
		}

		$bVotable = $sVotable == "true" ? true : false;
		$oUserOnly = empty( $iUserID ) ? null : User::newFromId( $iUserID );

		$oInstance = BsExtensionManager::getExtension('Rating');
		$oInstance->runRegisterCustomTypes();

		$oRatingItem = RatingItem::getInstance( $sRefType, $sRef, $sSubType );
		$oRatingItemView = $oRatingItem->getView($oUserOnly, $sViewName);
		if( $bVotable ) {
			$oRatingItemView->setVotable( true );
		}

		return json_encode(array(
			'success' => true,
			'view' => $oRatingItemView->execute(),
		));
	}

	/**
	 * ParserFirstCallInit Hook is called when the parser initialises for the first time.
	 * @param Parser $parser MediaWiki Parser object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$parser ) {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->runRegisterCustomTypes();

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Hook-Handler for 'BeforePageDisplay'. Sets context of Rating extension.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return boolean alway true. Keeps the hook system running.
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if( $this->checkContext( $oOutputPage->getTitle() ) === false ) return true;

		BsExtensionManager::setContext('MW::Rating');

		//PW TODO: find better way
		//this always was loaded too late, no matter what dependency or position
		$oOutputPage->addScript(
			'<script>'.
				"$(document).on( 'BsStateBarRegisterToggleClickElements', function(event, aRegisteredToggleClickElements) {".
					"aRegisteredToggleClickElements.push($('#bs-rating-statelink'));".
				"});".
			'</script>'
		);
		$oOutputPage->addModules('ext.bluespice.rating');
		$oOutputPage->addModuleStyles('ext.bluespice.rating.styles');
		return true;
	}

	/**
	 * Adds an rating view after article title
	 * @param Skin $skin
	 * @param BaseTemplate $template
	 * @return boolean always true
	 */
	public function onSkinTemplateOutputPageBeforeExec(&$skin, &$template){

		$oTitle = $skin->getTitle();
		if( BsExtensionManager::isContextActive( 'MW::Rating' ) === false ) return true;
		if( $this->bStateBar && BsConfig::get('MW::Rating::Position') === 'statebar') return true;

		global $wgRequest;
		if( $oTitle->isRedirect() && $wgRequest->getVal('redirect') != 'no' ) {
			//TODO: Use DB Query? Or WikiPage::getRedirectTarget() in later versions.
			//TODO: Use $this->mAdapter->getTitleFromRedirectRecurse( $oTitle );
			$oArticle = new Article( $oTitle, 0 ); //New: current revision
			$sContent = $oArticle->fetchContent( 0 ); //Old: current revision
			$oTitle = Title::newFromRedirectRecurse( $sContent );
		}
		if ( $oTitle == null || $oTitle->exists() == false ) return true;
		if( !$oTitle->userCan( 'rating-read' ) ) return true;

		$oRatingItem = RatingItem::getInstance( 'article', $oTitle->getArticleID() );

		$oView = $oRatingItem->getView( null, 'ViewHeadlineElementRating' );
		$bUserCanVote = $oTitle->userCan( 'rating-write' );
		if( $bUserCanVote ) {
			$oView->setVotable( true );
		}

		wfRunHooks('BSRatingBeforeHeadlineViewAdd', array(&$oRatingItem, &$oView, &$oTitle));
		if( is_null($oView) ) return true;

		$template->data['title'] .= $oView->execute();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeTopViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aTopViews
	 * @return boolean Always true to keep hook running
	 */
	public function onStateBarBeforeTopViewAdd( $oStateBar, &$aTopViews, $oUser, $oTitle ) {
		wfProfileIn( 'BS::' . __METHOD__ );

		if(!BsExtensionManager::isContextActive("MW::Rating") || BsConfig::get('MW::Rating::Position') !== 'statebar') {
			wfProfileOut( 'BS::' . __METHOD__ );
			return true;
		}

		if( !$oTitle->userCan( 'rating-read' ) ) return true;

		$oRatingItem = RatingItem::getInstance( 'article', $oTitle->getArticleID() );

		$oTopView = $oRatingItem->getView(
			null,
			'ViewStateBarTopElementRating'
		);
		$bUserCanVote = $oTitle->userCan( 'rating-write' );
		if( $bUserCanVote ) {
			$oTopView->setVotable( true );
		}

		wfRunHooks('BSRatingBeforeStateBarTopViewAdd', array(&$oRatingItem, &$oTopView, &$oTitle));
		if( is_null($oTopView) ) return true;

		$aTopViews[ 'statebartoprating' ] = $oTopView;

		wfProfileOut( 'BS::' . __METHOD__ );
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeBodyViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aBodyViews
	 * @return boolean Always true to keep hook running
	 */
	public function onStateBarBeforeBodyViewAdd( $oStateBar, &$aBodyViews, $oUser, $oTitle ) {
		wfProfileIn( 'BS::' . __METHOD__ );

		if( !$this->checkContext($oTitle) || BsConfig::get('MW::Rating::Position') !== 'statebar' ) {
			wfProfileOut( 'BS::' . __METHOD__ );
			return true;
		}

		if( !$oTitle->userCan( 'rating-read' ) || !$oTitle->userCan( 'rating-write' ) ) {
			wfProfileOut( 'BS::' . __METHOD__ );
			return true;
		}

		$this->runRegisterCustomTypes();
		$oRatingItem = RatingItem::getInstance( 'article', $oTitle->getArticleID() );

		$oBodyView = $oRatingItem->getView(
			$oUser,
			'ViewStateBarBodyElementRating'
		);
		$oBodyView->setVotable( true );

		wfRunHooks('BSRatingBeforeStateBarBodyViewAdd', array(&$oRatingItem, &$oBodyView, &$oTitle));
		if( is_null($oBodyView) ) return true;

		$aBodyViews[ 'statebarbodyrating' ] = $oBodyView;

		wfProfileOut( 'BS::' . __METHOD__ );
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortTopVars'
	 * @param array $aSortTopVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortTopVars( &$aSortTopVars ) {
		$this->bStateBar = true;
		$aSortTopVars['statebartoprating'] = wfMessage( 'bs-rating-toc-statebartoprating' )->plain();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodyrating'] = wfMessage( 'bs-rating-toc-statebarbodyrating' )->plain();
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
			case 'enRatingNS':
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
			case 'Position':
				$aPrefs['type']		= 'select';
				$aPrefs['options']	= array(
						wfMessage('bs-rating-pref-position-articletitle')->plain() => 'articletitle',
				);
				if( $this->bStateBar ) {
					$aPrefs['options'][wfMessage('prefs-statebar')->plain()] = 'statebar';
				}
				break;
			default:
		}

		wfProfileOut( 'BS::' . __METHOD__ );
		return $aPrefs;
	}

	/**
	 * Adds the table to the database
	 * @param DatabaseUpdater $oUpdater
	 * @return boolean Always true to keep Hook running
	 */
	public static function getSchemaUpdates( $oUpdater ) {
		$sDir = __DIR__.DS.'db'.DS;

		$oUpdater->addExtensionTable(
			'bs_rating',
			$sDir.'rating.sql'
		);
		$oUpdater->addExtensionField(
			'bs_rating',
			'rat_subtype',
			$sDir.'bs_rating.newfield.rat_subtype.sql'
		);

		return true;
	}

	/**
	 * Checks wether to set Context or not.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function checkContext( $oTitle ) {
		global $wgRequest;

		if( !in_array($wgRequest->getVal('action', 'view'), array('view', 'ajax')) )
			return false;
		if( !is_object( $oTitle ) )
			return false;
		if( $oTitle->isRedirect() )	{
			//PW(16.01.2013): this method does not exist in 1.20.1
			//- use later
			//$oTitle = $this->mAdapter->getTitleFromRedirectRecurse($oTitle);
				if( $wgRequest->getVal('redirect') != 'no' ) {
					$oArticle = new Article( $oTitle, 0 ); //New: current revision
					$sContent = $oArticle->fetchContent( 0 ); //Old: current revision
					$oTitle = Title::newFromRedirectRecurse( $sContent );
				}
			//
			if( $oTitle->isRedirect() )	return false;
		}
		if( $oTitle->exists()          === false )      return false;
		if( $oTitle->getNamespace()    === NS_SPECIAL ) return false;
		if( $oTitle->userCan( 'rating-read' ) === false )		return false;

		if ( !in_array( $oTitle->getNamespace(), BsConfig::get( 'MW::Rating::enRatingNS' ) ) ) return false;
		$vNoRating = BsArticleHelper::getInstance($oTitle)->getPageProp( 'bs_norating' );
		if( $vNoRating === '' ) return false;
		return true;
	}
}