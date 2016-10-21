<?php
/**
 * Renders the "helpful questiion" for the RatingItem class
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.RatedCommentsRatingItemHelpful.php 8934 2013-03-18 14:25:38Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the "helpful questiion" for the RatingItem class.
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 */
class ViewRatedCommentsRatingItemHelpful extends ViewRatingItemLike {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		if( is_null($this->oRatingItem) ) return '';

		$bSingleItem = false;
		$iTotal = 0;
		$iCount = 0;
		if( $this->oUser instanceof User ) {
			$bSingleItem = true;
			$aRatingOfSpecificUser = $this->oRatingItem->getRatingOfSpecificUser( $this->oUser );

			if(!empty($aRatingOfSpecificUser)) {
				$iTotal = $aRatingOfSpecificUser['value'];
				$iCount = 1;
			}
		} else {
			$iTotal = count( $this->oRatingItem->getValueFilteredRatings( 1 ) );
			$iCount = $this->oRatingItem->countRatings();
		}

		$aLocalDataTypes = array(
			'value'		=> $iTotal,
			'view'		=> 'ViewRatedCommentsRatingItemHelpful',
			'reftype'	=> $this->oRatingItem->getRefType(),
			'ref'		=> $this->oRatingItem->getRef(),
			'replace'	=> 'bs-ratedcomments-helpful-'.($bSingleItem ? '' : 'all-').$this->oRatingItem->getRef(),
			'userid'	=> $this->oUser ? $this->oUser->getID() : 0,
		);
		if($this->bVotable) {
			$aLocalDataTypes['votable'] = "true";
		}
		$aLocalDataTypes = array_merge($aLocalDataTypes, $this->aDataTypes);
		
		$sDataTypes = "";
		foreach($aLocalDataTypes as $key => $value) $sDataTypes.= ' data-'.$key.'="'.$value.'"';
		
		$aOut = array();

		if( $bSingleItem ) {
			$aOut[] = '<div class="bs-rating-item bs-ratedcomments-helpful '.($this->sAdditionalDivClasses ? $this->sAdditionalDivClasses : '').'" '.$sDataTypes.' id="bs-ratedcomments-helpful-'.$this->oRatingItem->getRef().'">';
			if( empty($aRatingOfSpecificUser) ) {
				$aOut[] = '<p class="bs-ratedcomments-helpful-text">'.wfMessage('bs-ratedcomments-helpful')->plain().'</p>';
				$aOut[] = '<input type="button" class="bs-ratedcomments-helpful-button" data-value="1" value="'.wfMessage('bs-ratedcomments-helpful-yes')->plain().'" />';
				$aOut[] = '<input type="button" class="bs-ratedcomments-helpful-button" data-value="-1" value="'.wfMessage('bs-ratedcomments-helpful-no')->plain().'" />';
			} else {
				$sMsg = 'bs-ratedcomments-helpful-'.($aRatingOfSpecificUser['value'] == 1 ? 'helpful' : 'nothelpful');
				$aOut[] = '<p class="bs-ratedcomments-helpful-text">'.wfMessage('bs-ratedcomments-helpful-rated', '{{int:'.$sMsg.'}}')->parse( ).'</p>';
			}
			$aOut[] = '</div>';
		} else {
			$aOut[] = '<div class="bs-rating-item bs-ratedcomments-helpful-all '.($this->sAdditionalDivClasses ? $this->sAdditionalDivClasses : '').'" '.$sDataTypes.' id="bs-ratedcomments-helpful-all-'.$this->oRatingItem->getRef().'">';
			$aOut[] = '<p>'.wfMessage('bs-ratedcomments-helpful-all', $iTotal, $iCount)->parse().'</p>';
			$aOut[] = '</div>';
		}
		return implode( "\n", $aOut );
	}
}
