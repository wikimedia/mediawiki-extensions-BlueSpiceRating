<?php
/**
 * RatingItemArticle class for extension Rating
 *
 * Provides a rating item.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.27.0
 * @package    BlueSpiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * RatingItemArticle class for Rating extension
 * @package BlueSpiceRating
 * @subpackage Rating
 */
class RatingItemArticle extends RatingItem {
	protected $sRefType = 'article';

	/**
	 * RatingItemArticle from a Title object
	 * @param Title $oTitle
	 * @return \RatingItemArticle
	 */
	public static function newFromTitle( Title $oTitle ) {
		return static::newFromObject((object) array(
			'reftype' => 'article',
			'ref' => $oTitle->getArticleID(), //check this, omg
			//no subtype?
		));
	}
}