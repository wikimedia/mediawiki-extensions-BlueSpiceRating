<?php
/**
 * Renders the headline rating element for RatedComments.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.HeadlineElementRatedComments.php 9176 2013-04-17 08:50:35Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the rating from the Rating extension.
 * @package    BlueSpice_Extensions
 * @subpackage RatedComments
 */
class ViewHeadlineElementRatedComments extends ViewHeadlineElementRating {

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
		$oView->setDataType('view', 'ViewHeadlineElementRatedComments');
		$oView->setDataType('replace', 'he-'.$this->sKey);
		$oView->setAdditionalDivClasses('bs-headline-rating bs-statebar-gotoratedcomment');
		$oView->setVotable( $this->bVotable );

		$aOut = array();
		$aOut[] = '<div class="bs-headline-item" id="he-'.$this->sKey.'">';
		$aOut[] =	$oView->execute();
		$aOut[] =	'<a title="'.wfMessage('bs-rating-sb-linktitle', $iCount)->parse().'" id="he-'.$this->sKey.'-text" class="bs-headline-top-text bs-statebar-gotoratedcomment">'.wfMessage('bs-rating-pt-link', $iCount)->parse().'</a>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}
}
