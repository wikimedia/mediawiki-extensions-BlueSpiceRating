<?php
/**
 * Renders the StateBar rating body element.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.StateBarTopElementRatedComments.php 9051 2013-03-28 15:15:42Z pwirth $
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
class ViewStateBarTopElementRatedComments extends ViewStateBarTopElementRating {

	public function __construct($I18N = null) {
		parent::__construct($I18N);
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
		$oView->setDataType('view', 'ViewStateBarTopElementRatedComments');
		$oView->setDataType('replace', 'sb-'.$this->sKey);
		$oView->setAdditionalDivClasses('bs-statebar-top-icon bs-statebar-gotoratedcomment');
		$oView->setVotable( $this->bVotable );

		$aOut = array();
		$aOut[] = '<div class="bs-statebar-top-item" id="sb-'.$this->sKey.'">';
		$aOut[] =	$oView->execute();
		$aOut[] =	'<span class="bs-statebar-top-text" id="sb-'.$this->sKey.'-text">';
		$aOut[] =		'<a title="'.wfMessage('bs-rating-sb-linktitle',$iCount)->parse().'" id="bs-rating-statelink" class="bs-statebar-gotoratedcomment">'.wfMessage('bs-rating-sb-link',$iCount)->parse().'</a>';
		$aOut[] =	'</span>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}
}
