<?php
/**
 * Renders the StateBar rating body element.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    $Id: view.HeadlineElementRating.php 9176 2013-04-17 08:50:35Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the rating from the Rating extension.
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 */
class ViewHeadlineElementRating extends ViewBaseElement {

	protected $oUser = null;
	protected $oRatingItem = null;
	protected $bVotable = false;

	public function __construct($I18N = null) {
		parent::__construct($I18N);
		$this->sKey = 'RatingHeadline';
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
		$oView->setDataType('view', 'ViewHeadlineElementRating');
		$oView->setDataType('replace', 'he-'.$this->sKey);
		$oView->setAdditionalDivClasses('bs-headline-rating');
		$oView->setVotable( $this->bVotable );

		$aOut = array();
		$aOut[] = '<div class="bs-headline-item" id="he-'.$this->sKey.'">';

		global $wgUser;
		if( !$wgUser->isLoggedIn() && !$wgUser->isAllowed('rating-write') ) {
			$oSpecialPage = SpecialPage::getTitleFor('UserLogin');
			$oReturnTo = Title::newFromID($oRatingItem->getRef());
			$sReturnTo = str_replace( ' ','_',$oReturnTo->getPrefixedText() );

			$aOut[] =	'<a title="'.wfMessage('bs-rating-he-linktitle',$iCount)->parse().'" href="'.$oSpecialPage->getFullURL('returnto='.$sReturnTo).'">';
			$aOut[] =		$oView->execute();
			$aOut[] =	'</a>';
			$aOut[] =	'<a id="he-'.$this->sKey.'-text" href="'.$oSpecialPage->getFullURL('returnto='.$sReturnTo).'">'.wfMessage('bs-rating-pt-link',$iCount)->parse().'</a>';
		} elseif( !$wgUser->isAllowed('rating-write') ) {
			$oView->setAdditionalDivClasses('bs-headline-rating bs-rating-item-notallowed');

			$aOut[] =		$oView->execute();
			$aOut[] =		'<a href="#" id="he-'.$this->sKey.'-text" class="bs-headline-top-text">'.wfMessage('bs-rating-pt-link',$iCount)->parse().'</a>';
		} else {
			$aOut[] =	$oView->execute();
			$aOut[] =	'<a href="#" id="he-'.$this->sKey.'-text" class="bs-headline-top-text">'.wfMessage('bs-rating-pt-link',$iCount)->parse().'</a>';
		}

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
