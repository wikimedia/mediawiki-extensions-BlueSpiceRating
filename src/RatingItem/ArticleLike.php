<?php
/**
 * ArticleLike class for extension Rating
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
 * @package    BlueSpice Pro
 * @subpackage BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */
namespace BlueSpice\Rating\RatingItem;

use BlueSpice\Rating\RatingItem;
use BlueSpice\Rating\Data\Record;
/**
 * ArticleLike class for Rating extension
 * @package BlueSpiceRating
 * @subpackage Rating
 */
class ArticleLike extends RatingItem {
	protected $refType = 'articlelike';

	/**
	 * ArticleLike from a \Title object
	 * @param \Title $title
	 * @return ArticleLike
	 */
	public static function newFromTitle( \Title $title ) {
		return static::newFromObject((object) array(
			Record::REFTYPE => 'articlelike',
			Record::REF => $title->getArticleID(), //check this, omg
			Record::SUBTYPE => '',
		));
	}

	public function jsonSerialize() {
		$data = parent::jsonSerialize();
		$status = $this->userCan(
			\RequestContext::getMain()->getUser(),
			'update',
			\Title::newFromID( $this->getRef() )
		);
		$data['usercanmodify'] = $status->isOK();
		return $data;
	}

	/**
	 * @param \User $user
	 * @return \Status
	 */
	public function userCan( \User $user, $action = 'read', \Title $title = null ) {
		if( !$title ) {
			$title = \Title::newFromID( (int) $this->getRef() );
		}
		return parent::userCan( $user, $action, $title );
	}
}