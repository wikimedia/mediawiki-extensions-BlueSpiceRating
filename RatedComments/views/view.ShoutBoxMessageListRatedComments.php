<?php
/**
 * Renders a list of shouts.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.ShoutBoxMessageListRatedComments.php 9051 2013-03-28 15:15:42Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the frame for list of shouts, e.g. when no entries are there,
 * and collects all shouts
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments 
 */
class ViewShoutBoxMessageListRatedComments extends ViewShoutBoxMessageList {
	private $oTitle = null;
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @param array $params not used here
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		global $wgUser;
		$sROut = '';
		if( !$wgUser->isLoggedIn() && !$wgUser->isAllowed('rating-write') ) {
			$oSpecialPage = SpecialPage::getTitleFor('UserLogin');
			$sReturnTo = str_replace( ' ','_',$this->oTitle->getPrefixedText() );

			$sROut = '<li><i>'.wfMessage( 'bs-sb-ratedcomments-notloggedin' )->parse().' <a href="'.$oSpecialPage->getFullURL('returnto='.$sReturnTo).'">'.$oSpecialPage->getText().'</a></i></li>';
		} /*elseif( !$wgUser->isAllowed('rating-write') ) {
			//not implemented jet
		}*/
		$out = parent::execute();

		return '<ul>'.$sROut.$out.'</ul>';
	}
	
	public function setTitle( $oTitle ) {
		$this->oTitle = $oTitle;
		return $this;
	}
}