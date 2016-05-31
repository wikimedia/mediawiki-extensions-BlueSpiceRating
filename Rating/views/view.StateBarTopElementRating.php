<?php
/**
 * Renders the StateBar rating body element.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.StateBarTopElementRating.php 9050 2013-03-28 15:14:36Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the rating from the Rating extension.
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 */
class ViewStateBarTopElementRating extends ViewStateBarTopElement {

	protected $oUser = null;
	protected $oRatingItem = null;
	protected $bVotable = false;

	public function __construct($I18N = null) {
		parent::__construct($I18N);
		$this->sKey = 'RatingState';
		$this->mOptions = array();
	}

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$oRatingItem = $this->oRatingItem;

		if( is_null($oRatingItem) ) return '';

		$iCount = $oRatingItem->countRatings();
		$oView = $oRatingItem->getView();
		$oView->setDataType('view', 'ViewStateBarTopElementRating');
		$oView->setDataType('replace', 'sb-'.$this->sKey);
		$oView->setAdditionalDivClasses('bs-statebar-top-icon');
		$oView->setVotable( $this->bVotable );

		$aOut = array();
		$aOut[] = '<div class="bs-statebar-top-item" id="sb-'.$this->sKey.'">';

		global $wgUser;
		if( !$wgUser->isLoggedIn() && !$wgUser->isAllowed('rating-write') ) {
			$oSpecialPage = SpecialPage::getTitleFor('UserLogin');
			$oReturnTo = Title::newFromID($oRatingItem->getRef());
			$sReturnTo = str_replace( ' ','_',$oReturnTo->getPrefixedText() );

			$aOut[] =	'<a title="'.wfMessage('bs-rating-sb-linktitle',$iCount)->parse().'" href="'.$oSpecialPage->getFullURL('returnto='.$sReturnTo).'">';
			$aOut[] =		$oView->execute();
			$aOut[] =	'</a>';
			$aOut[] =	'<span class="bs-statebar-top-text" id="sb-'.$this->sKey.'-text">';
			$aOut[] =		'<a title="'.wfMessage('bs-rating-sb-linktitle',$iCount)->parse().'" href="'.$oSpecialPage->getFullURL('returnto='.$sReturnTo).'">'.wfMessage('bs-rating-sb-link',$iCount)->parse().'</a>';
		} elseif( !$wgUser->isAllowed('rating-write') ) {
			$oView->setAdditionalDivClasses('bs-statebar-top-icon bs-rating-item-notallowed');

			$aOut[] =		$oView->execute();
			$aOut[] =	'<span class="bs-statebar-top-text" id="sb-'.$this->sKey.'-text">';
			$aOut[] =		'<a title="'.wfMessage('bs-rating-sb-linktitle',$iCount)->parse().'" href="#" id="bs-rating-statelink">'.wfMessage('bs-rating-sb-link',$iCount)->parse().'</a>';
		} else {
			$aOut[] =	$oView->execute();
			$aOut[] =	'<span class="bs-statebar-top-text" id="sb-'.$this->sKey.'-text">';
			$aOut[] =		'<a title="'.wfMessage('bs-rating-sb-linktitle',$iCount)->parse().'" href="#" id="bs-rating-statelink">'.wfMessage('bs-rating-sb-link',$iCount)->parse().'</a>';
		}

		$aOut[] =	'</span>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

	public function setRatingItem( $oRatingItem ) {
		$this->oRatingItem = $oRatingItem;
		return $this;
	}

	public function setUser( $oUser ) {
		$this->oUser = $oUser;
		return $this;
	}

	public function setVotable( $bVotable ) {
		$this->bVotable = $bVotable;
		return $this;
	}
	/**
	 * Does nothing - not supported by default
	 * @param integer $iCOntext
	 * @return \ViewRatingItemLike
	 */
	public function setContext( $iCOntext ) {
		return $this;
	}
}
