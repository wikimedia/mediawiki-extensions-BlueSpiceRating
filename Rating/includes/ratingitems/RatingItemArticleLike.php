<?php
/**
 * RatingItemArticleLike class for extension Rating
 *
 * Provides a rating item.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * RatingItemArticleLike class for Rating extension
 * @package BlueSpiceRating
 * @subpackage Rating
 */
class RatingItemArticleLike extends RatingItem {
	protected $sRefType = 'articlelike';

	/**
	 * RatingItemArticleLike from a Title object
	 * @param Title $oTitle
	 * @return \RatingItemArticleLike
	 */
	public static function newFromTitle( Title $oTitle ) {
		return static::newFromObject((object) array(
			'reftype' => 'articlelike',
			'ref' => $oTitle->getArticleID(), //check this, omg
			'subtype' => '',
		));
	}

	public function jsonSerialize() {
		$aData = parent::jsonSerialize();
		$oStatus = $this->userCan(
			RequestContext::getMain()->getUser(),
			'update',
			Title::newFromID( $this->getRef() )
		);
		$aData['usercanmodify'] = $oStatus->isOK();
		return $aData;
	}
}