<?php
/**
 * Renders the "like" for the RatingItem class
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    $Id: view.RatingItemLike.php 8934 2013-03-18 14:25:38Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the "stars" for the RatingItem class. NOT yet in use
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 */
class ViewRatingItemLike extends ViewBaseElement {

	protected $oUser = null;
	protected $oRatingItem = null;
	protected $bVotable = false;
	protected $sAdditionalDivClasses = "";
	protected $aDataTypes = array();

	public function __construct() {
		$this->oUser = null;
		$this->mOptions = array();
	}

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		if( is_null($this->oRatingItem) ) return '';

		//if( $this->oUser instanceof User ) {
			/*$aRatingOfSpecificUser = $this->oRatingItem
				->getRatingOfSpecificUser($this->oUser);

			$iTotal = 0;
			$iCount = 0;
			if(!empty($aRatingOfSpecificUser)) {
				$iTotal = $aRatingOfSpecificUser['value'];
				$iCount = 1;
			}*/
		//} else {
			$iTotal = $this->oRatingItem->getTotal();
			//$iCount = $this->oRatingItem->countRatings();
		//}
		/*$iRange = $this->oRatingItem->getRange();
		$fAverage = !empty($iTotal) && !empty($iCount) 
			? round( $iTotal/$iCount, 1 )
			: 0;
		*/
		$aLocalDataTypes = array(
			'value' => $iTotal,
			'view' => 'ViewRatingItemLike',
			'reftype' => $this->oRatingItem->getRefType(),
			'ref' => $this->oRatingItem->getRef(),
		);
		if($this->bVotable) {
			$aLocalDataTypes['votable'] = "true";
		}
		$aLocalDataTypes = array_merge($aLocalDataTypes, $this->aDataTypes);
		
		$sDataTypes = "";
		foreach($aLocalDataTypes as $key => $value) $sDataTypes.= ' data-'.$key.'="'.$value.'"';
		
		$aOut = array();
		$aOut[] = '<div class="bs-rating-item bs-rating-like '.($this->sAdditionalDivClasses ? $this->sAdditionalDivClasses : '').'" '.$sDataTypes.' >';
		$aOut[] = $this->renderThumbUp();
		//$aOut[] = $this->renderThumbDown();
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

	/**
	 * this method renders a single "star" image
	 * @param int $iValue
	 * @param string $sType
	 * @return string
	 */
	private function renderThumbUp() {
		$aRatings = $this->oUser instanceof User
					? $this->oRatingItem->getRatingOfSpecificUser($this->oUser)
					: $this->oRatingItem->getRatings();

		if( !empty($sType) ) $sType = '-'.$sType;

		return '<img
					src="'.$this->mOptions['icon-path'].'thumb_up.png"
					'.($this->bVotable ? 'data-value="1"' : '').'
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
