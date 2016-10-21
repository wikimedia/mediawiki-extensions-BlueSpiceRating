<?php
/**
 * Renders the Shoutbox frame.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.RatedCommentsShoutBoxMessageForm.php 9757 2013-06-17 08:18:53Z pwirth $
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
class ViewRatedCommentsShoutBoxMessageForm extends ViewBaseElement {

	/**
	 * The title of the shout
	 * @var string readily rendered message 
	 */
	protected $sTitle = '';
	/**
	 * The message of the shout
	 * @var string readily rendered message 
	 */
	protected $sMessage;
	/**
	 *
	 * @var User 
	 */
	protected $oUser;
	/**
	 *
	 * @var integer
	 */
	protected $iShoutID;
	/**
	 *
	 * @var integer
	 */
	protected $iArticleID = 0;
	/**
	 * Constructor
	 */

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Sets the title property
	 * @param string $sTitle readily rendered tile. Currently just plain text.
	 */
	public function setTitle( $sTitle ) {
		$this->sTitle = $sTitle;
	}

	/**
	 * Sets the message property
	 * @param string $sMessage readily rendered message. Currently just plain text.
	 */
	public function setMessage( $sMessage ) {
		$this->sMessage = $sMessage;
	}

	/**
	 * Sets the User object
	 * @param User $oUser
	 */
	public function setUser( $oUser ) {
		$this->oUser = $oUser;
	}

	/**
	 * Sets the ID of the shout
	 * @param Integer $iShoutID
	 */
	public function setShoutID( $iShoutID ) {
		$this->iShoutID = $iShoutID;
	}

	/**
	 * Sets the article ID
	 * @param integer $iArticleID
	 */
	public function setArticleID( $iArticleID ) {
		$this->iArticleID = $iArticleID;
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut = '';

		//if ($this->getOption( 'showmessageform' ) ) {
			$sOut .= $this->renderMessageForm();
		//}
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
		$oTitle = Title::newFromID( (int) $this->iArticleID );
		if( !$oTitle || !$oTitle->exists() ) {
			return '';
		}
		if( !$oTitle->userCan('rating-write') || !$oTitle->userCan('writeshoutbox') )
			return '';

		
		$oRatingItem = RatingItem::getInstance('article', $this->iArticleID, '', true); //reload needed for unknown reason!

		$oView = $oRatingItem->getView( $this->oUser );
		$oView->setAdditionalDivClasses('bs-rc-form-rating');
		$oView->setDataType('view', 'ViewRatedCommentsFormRating'); // set fake view to bind on trigger in js
		$oView->setVotable( true );

		$aOut[] = '<div id="bs-ratedcomments-edit">';
		$aOut[] = '<div id="bs-users-ratedcomment-edit">';
		$aOut[] = '<form id="bs-ratedcomments-form-edit" class="clearfix">';
		$aOut[] = '<fieldset>';
		$aOut[] =	'<legend>'.wfMessage('bs-ratedcomments-form-legendrating')->plain().'</legend>';
		$aOut[] =	$oView->execute().'<input type="hidden" value="" id="bs-ratedcomments-ratinginput-edit" />';
		$aOut[] = '</fieldset>';
		$aOut[] = '<fieldset>';
		$aOut[] =	'<legend>'.wfMessage('bs-ratedcomments-form-legendtitle')->plain().'</legend>';
		$aOut[] =	'<input type="text" value="'.$this->sTitle.'" maxlength="'.BsConfig::get( 'MW::RatedComments::MaxTitleLength' ).'" id="bs-ratedcomments-sbtitle-edit" />';
		$aOut[] = '</fieldset>';
		$aOut[] = '<fieldset>';
		$aOut[] = '<legend>'.wfMessage('bs-ratedcomments-form-legendtext')->plain().'</legend>';
		$aOut[] =	'<textarea id="bs-ratedcomments-message-edit" maxlength="'.BsConfig::get( 'MW::RatedComments::MaxMessageLength' ).'" rows="10">'.$this->sMessage.'</textarea>';
		$aOut[] =	'<br />';
		$aOut[] = '</fieldset>';
		$aOut[] = '<img id="bs-sb-loading" src="'.$wgScriptPath.'/bluespice-core/resources/bluespice/images/bs-ajax-loader-bar-blue.gif" alt="Loading..."/>';
		$aOut[] = '<input id="bs-ratedcomments-form-edit-submit" type="submit" value="'.wfMessage('bs-ratedcomments-form-submit')->plain().'" />';
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
		$aOut[] = $innerText;
		return implode( "\n" , $aOut );
	}
}
