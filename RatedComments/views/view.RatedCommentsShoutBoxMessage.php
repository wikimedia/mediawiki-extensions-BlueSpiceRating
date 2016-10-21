<?php
/**
 * Renders a single shout.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: $
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */


/**
 * This view renders the a single shout.
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments
 */
class ViewRatedCommentsShoutBoxMessage extends ViewBaseElement {

	/**
	 * Date and time of the shout
	 * @var string readily rendered date
	 */
	protected $sDate = '';
	/**
	 * Name of the author of the shout
	 * @var string readily rendered name
	 */
	protected $sUsername;
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
	 * @var ViewUserMiniProfile
	 */
	protected $oMiniProfile;
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
	 * Sets the date property
	 * @param string $sDate readily rendered date, currently something like "2 minutes ago"
	 */
	public function setDate( $sDate ) {
		$this->sDate = $sDate;
	}

	/**
	 * Sets the username property
	 * @param string $sName readily rendered name, currently the real name
	 */
	public function setUsername( $sName ) {
		$this->sUsername = $sName;
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
	 * Sets the UserMiniProfile view property
	 * @param ViewUserMiniProfile $oView 
	 */
	public function setMiniProfile( $oView ) {
		$this->oMiniProfile = $oView;
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
	 * @param array $aParams not used here
	 * @return string HTML output
	 */
	public function execute( $aParams = false ) {
		$sUserName = $this->oUser->getRealName();
		if( empty($sUserName) ) {
			$sUserName = $this->oUser->getName();
		}

		$aOut = array();
		$aOut[] = '<li class="bs-sb-listitem clearfix" id="bs-sb-'.$this->iShoutID.'">';
		$aOut[] = '<div class="bs-user-image">';
		if ( $this->oMiniProfile instanceof ViewUserMiniProfile ) {
			$aOut[] = $this->oMiniProfile->execute();
		}
		$aOut[] = '</div>';
		$aOut[] = '<div class="bs-sb-message">';
		$aOut[] = '<div class="bs-sb-message-head">';

		$oRatingItem = RatingItem::getInstance( 'article', $this->iArticleID, '', true ); // reload needed for unknown reason!
		if( is_null($oRatingItem) ) return '';

		$oView = $oRatingItem->getView( $this->oUser );
		$oView->setAdditionalDivClasses( 'bs-sb-ratingitem' );

		$aOut[] = $oView->execute();

		global $wgUser;

		$sArchiveButton = '';
		if( (BsCore::checkAccessAdmission( 'archiveshoutbox' ) && BsCore::checkAccessAdmission( 'rating-archive' ))
			|| ($this->oUser->getId() === $wgUser->getId())
		) { 
			$sArchiveButton = '<span class="bs-rc-archive" title="'.wfMessage( 'bs-ratedcomments-sb-message-archive' )->plain().'"><a class="bs-rc-archive-link icon-cancel-circle"> </a></span>';
		}

		$sEditButton = '';
		if( BsCore::checkAccessAdmission( 'ratedcommentedit' ) && $this->oUser->getId() === $wgUser->getId() ) { 
			$sEditButton = '<span class="bs-rc-edit"><a class="bs-rc-edit-link icon-pencil" title="'.wfMessage( 'bs-ratedcomments-sb-message-edit' )->plain().'"> </a></span>';
		}

		$aOut[] = $sEditButton;
		$aOut[] = $sArchiveButton;
		$aOut[] = '</div>';

		$aOut[] = '<span class="bs-ratedcomments-sb-message-title"><strong>'.$this->sTitle.'</strong></span>';

		$aOut[] = '<div class="bs-sb-message-text">'. nl2br( $this->sMessage ) . '</div> ';
		
		$aOut[] = '<div class="bs-sb-message-foot">'
				.wfMessage( 'bs-ratedcomments-sb-message-from', '[[User:'.$this->oUser->getName().'|'.$sUserName.']]' )->parse()
				.' '.$this->sDate
				.'</div> ';

		$oShoutRatingItem = RatingItem::getInstance('ratedcomments', $this->iShoutID);
		$aOut[] = $oShoutRatingItem->renderView();

		if($wgUser->getId() !== 0 && $this->oUser->getId() !== $wgUser->getId()) {
			$aOut[] = $oShoutRatingItem->renderView( $wgUser );
		}

		$aOut[] = '</div>';

		$aOut[] = '</li>';
		return implode( "\n", $aOut);
	}

}