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

		$core->registerPermission(
			'rating-write',
			['user']
		);
		$core->registerPermission(
			'rating-read',
			['*']
		);
		$core->registerPermission(
			'rating-archive',
			['sysop']
		);
		$core->registerPermission(
			'rating-viewspecialpage',
			['user']
		);
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

		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );

		$this->setHook( 'BSUserSidebarGlobalActionsWidgetGlobalActions' );


		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Adds an rating view after article title
	 * @param Skin $skin
	 * @param BaseTemplate $template
	 * @return boolean always true
	 */
	public function onSkinTemplateOutputPageBeforeExec(&$skin, &$template){
		$oATitle = $this->getArticleContext( $skin->getTitle() );
		if( $oATitle ) {
			$template->data['title'] .= RatingItemArticle::newFromTitle(
				$oATitle
			)->getTag();
		}

		$oALikeTitle = $this->getArticleLikeContext( $skin->getTitle() );
		if( $oALikeTitle ) {
			$template->data['title'] .= RatingItemArticleLike::newFromTitle(
				$oALikeTitle
			)->getTag();
		}
		return true;
	}

	/**
	 * Adds Special:Rating link to wiki wide widget
	 * @param UserSidebar $oUserSidebar
	 * @param User $oUser
	 * @param array $aLinks
	 * @param string $sWidgetTitle
	 * @return boolean
	 */
	public function onBSUserSidebarGlobalActionsWidgetGlobalActions( UserSidebar $oUserSidebar, User $oUser, &$aLinks, &$sWidgetTitle ) {
		$oSpecialRating = SpecialPageFactory::getPage( 'Rating' );
		if( !$oSpecialRating ) {
			return true;
		}
		$aLinks[] = array(
			'target' => $oSpecialRating->getPageTitle(),
			'text' => $oSpecialRating->getDescription(),
			'attr' => array(),
			'position' => 700,
			'permissions' => array(
				'read'
			),
		);
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
			case 'enArticleLikeNS':
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
		$sDir = __DIR__.'/maintenance/db';

		$oUpdater->addExtensionTable(
			'bs_rating',
			"$sDir/rating.sql"
		);
		$oUpdater->addExtensionField(
			'bs_rating',
			'rat_subtype',
			"$sDir/bs_rating.newfield.rat_subtype.sql"
		);
		$oUpdater->addExtensionField(
			'bs_rating',
			'rat_context',
			"$sDir/bs_rating.newfield.rat_context.sql"
		);

		return true;
	}

	/**
	 * Checks wether to set Context or not and returns the context Title.
	 * @param Title $oTitle
	 * @param string $sCheckRatingPermission
	 * @return Title - or false
	 */
	public function getArticleContext( Title $oTitle = null, $sCheckRatingPermission = 'read') {
		if( !RatingRegistry::isRegisteredType( 'article' ) ) {
			return true;
		}

		if( !$oTitle = $this->checkRequestTitle( $oTitle ) ) {
			return false;
		}

		$aEnabledNamespaces = BsConfig::get( 'MW::Rating::enRatingNS' );
		if( !in_array( $oTitle->getNamespace(), $aEnabledNamespaces ) ) {
			return false;
		}

		if( empty($sCheckRatingPermission) ) {
			return $oTitle;
		}
		if( !$oRatingItem = RatingItemArticle::newFromTitle( $oTitle ) ) {
			return false;
		}
		$oStatus = $oRatingItem->userCan(
			$this->getUser(),
			$sCheckRatingPermission,
			$oTitle
		);

		if( !$oStatus->isOK() ) {
			return false;
		}

		return $oTitle;
	}

	/**
	 * Checks wether to set Context or not and returns the context Title.
	 * @param Title $oTitle
	 * @param string $sCheckRatingPermission
	 * @return Title - or false
	 */
	public function getArticleLikeContext( Title $oTitle = null, $sCheckRatingPermission = 'read') {
		if( !RatingRegistry::isRegisteredType( 'articlelike' ) ) {
			return true;
		}

		if( !$oTitle = $this->checkRequestTitle( $oTitle ) ) {
			return false;
		}

		$aEnabledNamespaces = BsConfig::get( 'MW::Rating::enArticleLikeNS' );
		if( !in_array( $oTitle->getNamespace(), $aEnabledNamespaces ) ) {
			return false;
		}

		if( empty( $sCheckRatingPermission ) ) {
			return $oTitle;
		}
		if( !$oRatingItem = RatingItemArticleLike::newFromTitle( $oTitle ) ) {
			return false;
		}
		$oStatus = $oRatingItem->userCan(
			$this->getUser(),
			$sCheckRatingPermission,
			$oTitle
		);

		if( !$oStatus->isOK() ) {
			return false;
		}

		return $oTitle;
	}

	protected function checkRequestTitle( \Title $oTitle = null ) {
		if( !$oTitle instanceof Title ) {
			return false;
		}

		$oRequest = $this->getRequest();
		$sAction = $oRequest->getVal( 'action', 'view' );

		if( !in_array( $sAction, ['view', 'submit', 'bs-statebar-tasks'] ) ) {
			return false;
		}

		if( $oTitle->isRedirect() ) {
			if( $oRequest->getVal( 'redirect' ) != 'no' ) {
				$oTitle = BsArticleHelper::getInstance( $oTitle )
					->getTitleFromRedirectRecurse();
			}
			if( !$oTitle || !$oTitle->exists() || $oTitle->isRedirect() ) {
				return false;
			}
		}
		if( $oTitle->getNamespace() === NS_SPECIAL ) {
			return false;
		}
		$vNoRating = BsArticleHelper::getInstance( $oTitle )->getPageProp(
			'bs_norating'
		);
		if( !is_null($vNoRating) ) {
			return false;
		}

		return $oTitle;
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