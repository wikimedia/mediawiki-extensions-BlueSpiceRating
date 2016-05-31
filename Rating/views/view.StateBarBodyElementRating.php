<?php
/**
 * Renders the StateBar rating body element.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    $Id: view.StateBarBodyElementRating.php 9050 2013-03-28 15:14:36Z pwirth $
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
class ViewStateBarBodyElementRating extends ViewStateBarBodyElement {

	protected $oUser = null;
	protected $oRatingItem = null;
	protected $bVotable = false;

	public function __construct($I18N = null) {
		parent::__construct($I18N);
		$this->sKey = 'RatingVote';
		$this->mOptions = array();
	}

	/**
	 * This method actually generates the output
	 * @param ms method actually generates the outpuixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$oRatingItem = $this->oRatingItem;
		if( is_null($oRatingItem) ) return '';

		$oView = $oRatingItem->getView();
		$oView->setDataType('view', 'ViewStateBarBodyElementRating');
		$oView->setDataType('replace', 'sbb-'.$this->sKey);
		$oView->setAdditionalDivClasses('bs-statebar-body-itembody');
		$oView->setVotable( $this->bVotable );
		$oView->setUser( $this->oUser );

		$aOut[] = '<div class="bs-statebar-body-item" id="sbb-'.$this->sKey.'">';
		$aOut[] =	'<h4 class="bs-statebar-body-itemheading" id="sbb-'.$this->sKey.'-heading">'.wfMessage('bs-rating-sbb-title')->plain().'</h4>';
		//$aOut[] =		'<p id="sbb-'.$this->sKey.'-vote">'.wfMessage('bs-rating-sbb-votetext')->plain().'</p>';
		$aOut[] =		$oView->execute();
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
