<?php
/**
 * Renders the "stars" for the RatingItem class
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    $Id: view.RatingItemStars.php 10349 2013-09-10 08:57:06Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the "stars" for the RatingItem class.
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 */
class ViewRatingItemStars extends ViewBaseElement {

	protected $oUser = null;
	protected $oRatingItem = null;
	protected $bVotable = false;
	protected $sAdditionalDivClasses = "";
	protected $aDataTypes = array();

	public function __construct() {
		$this->mOptions = array();
	}

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		if( is_null($this->oRatingItem) ) return '';

		if( $this->oUser instanceof User ) {
			$aRatingOfSpecificUser = $this->oRatingItem->getRatingOfSpecificUser($this->oUser);
			$this->aDataTypes['userID'] = $this->oUser->getID();

			$iTotal = 0;
			$iCount = 0;
			if(!empty($aRatingOfSpecificUser)) {
				$iTotal = $aRatingOfSpecificUser['value'];
				$iCount = 1;
			}
		} else {
			$iTotal = $this->oRatingItem->getTotal();
			$iCount = $this->oRatingItem->countRatings();
		}
		$iMaxValue = max( $this->oRatingItem->getAllowedValues() );
		$fAverage = !empty($iTotal) && !empty($iCount) 
			? round( $iTotal/$iCount, 1 )
			: 0;

		$aLocalDataTypes = array(
			'value' => $fAverage,
			'view' => 'ViewRatingItemStars',
			'reftype' => $this->oRatingItem->getRefType(),
			'ref' => $this->oRatingItem->getRef(),
			'subtype' => $this->oRatingItem->getSubType(),
		);
		if($this->bVotable) {
			$aLocalDataTypes['votable'] = "true";
		}
		$aLocalDataTypes = array_merge($aLocalDataTypes, $this->aDataTypes);

		$sDataTypes = "";
		foreach($aLocalDataTypes as $key => $value) $sDataTypes.= ' data-'.$key.'="'.$value.'"';

		$aOut = array();
		$aOut[] = '<div class="bs-rating-item bs-rating-stars '.($this->sAdditionalDivClasses ? $this->sAdditionalDivClasses : '').'" '.$sDataTypes.' >';
		$aOut[] = $this->renderRatingItem( $iCount, $iTotal, $fAverage, $iMaxValue );
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

	/**
	 * this method renders the stars
	 * @param int $iCount
	 * @param int $iTotal
	 * @param float $fAverage
	 * @param int $iMaxValue
	 * @return string - output
	 */
	protected function renderRatingItem( $iCount, $iTotal, $fAverage, $iMaxValue ) {
		$sOutput = '';

		if( !empty( $iCount ) ) {
			$aAverage = explode('.', $fAverage );

			for($i = 1; $i <= (int) $aAverage[0]; $i++) {
				$sOutput .= $this->createVoteItemImage( $i );
			}

			if( isset($aAverage[1]) && $aAverage[1] >= $iMaxValue ) {
				$aAverage[0] ++;
				$sOutput .= $this->createVoteItemImage( $aAverage[0], 'half' );
			}

			for($i = 1; $i <= $iMaxValue - (int) $aAverage[0]; $i++) {
				$sOutput .= $this->createVoteItemImage( $aAverage[0]+$i, 'empty' );
			}

		} else {
			for($i = 1; $i <= $iMaxValue; $i++) {
				$sOutput .= $this->createVoteItemImage( $i, 'notrated' );
			}
		}

		return $sOutput;
	}

	/**
	 * this method renders a single "star" image
	 * @param int $iValue
	 * @param string $sType
	 * @return string
	 */
	protected function createVoteItemImage( $iValue, $sType = '') {
		global $wgScriptPath;
		$aRatings = $this->oUser instanceof User
					? $this->oRatingItem->getRatingOfSpecificUser($this->oUser)
					: $this->oRatingItem->getRatings();

		if( !empty($sType) ) $sType = '-'.$sType;

		return '<img
					title="'.wfMessage( (!empty($aRatings) ? 'bs-rating-sb-yourrating' : 'bs-rating-sb-vote' ))->plain().'"
					alt="'.wfMessage( (!empty($aRatings) ? 'bs-sb-yourrating' : 'bs-rating-sb-vote' ))->plain().'"
					src="'.$wgScriptPath.'/extensions/BlueSpiceRating/Rating/resources/images/star'.$sType.'.png"
					'.($this->bVotable ? 'data-value="'.$iValue.'"' : '').'
					'.($this->bVotable ? 'class="votable"' : '').'
				/>';
	}

	public function setRatingItem( $oRatingItem ) {
		$this->oRatingItem = $oRatingItem;
		return $this;
	}
	
	public function setUser( $oUser ) {
		$this->oUser = $oUser;
		return $this;
	}
	
	public function setDataType( $key, $value ) {
		$this->aDataTypes[$key] = $value;
		return $this;
	}
	
	public function setAdditionalDivClasses( $sAdditionalDivClasses ) {
		$this->sAdditionalDivClasses = $sAdditionalDivClasses;
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
