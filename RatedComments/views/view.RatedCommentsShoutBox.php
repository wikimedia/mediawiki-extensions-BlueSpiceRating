<?php
/**
 * Renders the Shoutbox frame.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.RatedCommentsShoutBox.php 9757 2013-06-17 08:18:53Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the Shoutbox frame.
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments 
 */
class ViewRatedCommentsShoutBox extends ViewBaseElement {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut = '';

		if ($this->getOption( 'showmessageform' ) ) {
			$sOut .= $this->renderMessageForm();
		}
		$sOut = $this->wrapAll( $sOut );

		return $sOut;
	}

	/**
	 * Renders the link to the special page
	 * @return string HTML
	 */
	protected function renderMessageForm() {
		global $wgScriptPath;
		$aOut = array();

		$oRequest = RequestContext::getMain()->getRequest();
		$oTitle = RequestContext::getMain()->getTitle();
		$wgUser = RequestContext::getMain()->getUser();
		if( $oTitle->isRedirect() ) {
			if( $oRequest->getVal('redirect') != 'no' ) {
				$oTitle = BsArticleHelper::getInstance( $oTitle )
					->getTitleFromRedirectRecurse();
			}
			if( !$oTitle || $oTitle->isRedirect() ) {
				return false;
			}
		}
		if ( $oTitle == null || $oTitle->exists() == false ) return true;

		if( !$oTitle->userCan('rating-write') || !$oTitle->userCan('writeshoutbox') )
			return '';

		$oExistingMessage = $this->getExistingMessage($oTitle, $wgUser);
		if( !is_object($oExistingMessage) ) {
			$sToogle = '<input type="submit" id="bs-ratedcomments-toogle-button" value="'.wfMessage( 'bs-ratedcomments-givecommentlink' )->plain().'" />';
			$sValueTextBox = wfMessage( 'bs-ratedcomments-form-default-message' )->plain();
		}
		else {
			//quick and dirty
			return '';

			//$sToogle = '<a id="bs-ratedcomments-toogle-link">'.wfMessage( 'bs-ratedcomments-mycommentlink' )->plain().'</a>';
			//$sValueTextBox = $oExistingMessage->sb_message;
		}
		$sToggleImg = '<span id="bs-ratedcomments-viewtoggler" style="line-height: 0em;" >'
					.'<img id="bs-ratedcomments-viewtoggler-image" src="'.$wgScriptPath.'/extensions/BlueSpiceRatedComments/RatedComments/resources/images/bs-ratedcomments-viewtoggler_more.png" />'
					.'</span>';

		$oRatingItem = RatingItem::getInstance('article', $oTitle->getArticleID());
		$oView = $oRatingItem->getView( $wgUser );
		$oView->setDataType('view', 'ViewRatedCommentsFormRating'); // set fake view to bind on trigger in js
		$oView->setVotable( true );

		$aOut[] = '<div id="bs-ratedcomments">';
		$aOut[] = $sToogle.$sToggleImg;
		$aOut[] = '<div id="bs-users-ratedcomment">';
		$aOut[] = '<form id="bs-ratedcomments-form" class="clearfix">';
		$aOut[] = '<fieldset>';
		$aOut[] =	'<legend>'.wfMessage('bs-ratedcomments-form-legendrating')->plain().'</legend>';
		$aOut[] =	$oView->execute().'<input type="hidden" value="" id="bs-ratedcomments-ratinginput" />';
		$aOut[] = '</fieldset>';
		$aOut[] = '<fieldset>';
		$aOut[] =	'<legend>'.wfMessage('bs-ratedcomments-form-legendtitle')->plain().'</legend>';
		$aOut[] =	'<input type="text" value="" maxlength="'.BsConfig::get( 'MW::RatedComments::MaxTitleLength' ).'" id="bs-ratedcomments-sbtitle" />';
		$aOut[] = '</fieldset>';
		$aOut[] = '<fieldset>';
		$aOut[] = '<legend>'.wfMessage('bs-ratedcomments-form-legendtext')->plain().'</legend>';
		$aOut[] =	'<textarea id="bs-ratedcomments-message" maxlength="'.BsConfig::get( 'MW::RatedComments::MaxMessageLength' ).'" rows="10">'.$sValueTextBox.'</textarea>';
		$aOut[] =	'<br />';
		$aOut[] = '</fieldset>';
		$aOut[] = '<img id="bs-sb-loading" src="'.$wgScriptPath.'/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-ajax-loader-bar-blue.gif" alt="Loading..."/>';
		$aOut[] = '<input id="bs-ratedcomments-form-submit" type="submit" value="'.wfMessage('bs-ratedcomments-form-submit')->plain().'" />';
		$aOut[] = '</form>';
		$aOut[] = '</div>';
		$aOut[] = '</div>';

		return implode( "\n" , $aOut );
	}


	/**
	 * Renders the basic shoutbox layer
	 * @param string $innerText HTML that is to be put inside the basic shoutbox layer, i.e. the output box and shouts.
	 * @return string HTML for output
	 */
	protected function wrapAll( $innerText ) {
		$aOut = array();
		$aOut[] = '<div class="bs-sb">';
		$aOut[] = '  <fieldset>';
		$aOut[] = '    <legend>' . wfMessage( 'bs-ratedcomments-title' ) . '</legend>';
		$aOut[] = $innerText;
		//$aOut[] = '    <div id="bs-sb-loading" style="display:none;"><img src="' . BsConfig::get('MW::ScriptPath') . '/bluespice-mw/ext/ShoutBox/css/images/loading.gif" alt="'.wfMessage('loading').'" /></div>';
		$aOut[] = '    <div id="bs-sb-content" style="display:none;"></div>';
		$aOut[] = '  </fieldset>';
		$aOut[] = '</div>';

		return implode( "\n" , $aOut );
	}

	protected function getExistingMessage($oTitle, $oUser) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array('bs_shoutbox', 'bs_rating'),
			'*',
			array(
				'sb_user_id' => $oUser->getId(),
				'sb_page_id' => $oTitle->getArticleID(),
				'sb_archived' => '0',
				'rat_archived' => '0',
				'sb_user_id = rat_userid',
				'sb_page_id = rat_ref',
				'sb_title != ""', //quick and dirty
			),
			__METHOD__,
			array('LIMIT' => 1)
		);
		if( !$res ) return false;

		return $dbr->fetchObject($res);
	}

}
