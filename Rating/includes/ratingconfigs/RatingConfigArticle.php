<?php
/**
 * RatingConfigArticle class for Rating extension
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
 * @package    BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * RatingConfigArticle class for Rating extension
 * @package BlueSpiceFoundation
 */
class RatingConfigArticle extends RatingConfig {
	protected $sType = 'article';

	protected function get_RatingClass() {
		return "RatingItemArticle";
	}
	protected function get_TypeMsgKey() {
		return "bs-rating-types-page";
	}

	protected function get_ModuleScripts() {
		return array_merge(
			parent::get_ModuleScripts(),
			array( 'ext.rating.starRatingSvg' )
		);
	}
	protected function get_ModuleStyles() {
		return array_merge(
			parent::get_ModuleStyles(),
			array( 'ext.rating.starRatingSvg.styles' )
		);
	}

	protected function get_AllowedValues() {
		return range( 1, 5 ); // basic 5 stars
	}

	protected function get_UserCanRemoveVote() {
		return false;
	}

	protected function get_HTMLTagOptions() {
		return array_merge_recursive( parent::get_HTMLTagOptions(), array(
			'class' => array( 'bs-rating-article' ),
		));
	}
}