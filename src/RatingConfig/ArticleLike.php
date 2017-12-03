<?php
/**
 * ArticleLike class for Rating extension
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
namespace BlueSpice\Rating\RatingConfig;

use BlueSpice\Rating\RatingConfig;
/**
 * ArticleLike class for Rating extension
 * @package BlueSpiceFoundation
 */
class ArticleLike extends RatingConfig {
	protected $type = 'articlelike';

	protected function get_RatingClass() {
		return "\\BlueSpice\\Rating\\RatingItem\\ArticleLike";
	}
	protected function get_TypeMsgKey() {
		return "bs-rating-types-pagelike";
	}

	protected function get_ModuleScripts() {
		return array_merge(
			parent::get_ModuleScripts(), [
				'ext.bluespice.ratingItemArticleLike'
			]
		);
	}
	protected function get_ModuleStyles() {
		return array_merge(
			parent::get_ModuleStyles(),
			['ext.bluespice.ratingItemArticleLike.styles']
		);
	}

	protected function get_AllowedValues() {
		return [ 1 ];
	}

	protected function get_UserCanRemoveVote() {
		return true;
	}

	protected function get_PermissionTitleRequired() {
		return true;
	}

	protected function get_HTMLTagOptions() {
		return array_merge_recursive( parent::get_HTMLTagOptions(), [
			'class' => ['bs-rating-articlelike'],
		]);
	}
}