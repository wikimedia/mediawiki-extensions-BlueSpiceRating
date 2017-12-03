<?php
/**
 * Hook handler base class for MediaWiki hook SkinTemplateOutputPageBeforeExec
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
 * @package    BlueSpiceFoundation
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */
namespace BlueSpice\Rating\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;

class AddArticleLikeRating extends SkinTemplateOutputPageBeforeExec {
	protected $contextTitle = null;

	protected function skipProcessing() {
		$registry = $this->getServices()->getService( 'BSRatingRegistry' );
		if( !$registry->isRegisteredType( 'articlelike' ) ) {
			return true;
		}
		if( !$title = $this->getArticleLikeContext( $this->skin->getTitle() ) ) {
			return true;
		}

		$prop = \BsArticleHelper::getInstance( $title )->getPageProp(
			'bs_norating'
		);
		if( !is_null( $prop ) ) {
			return true;
		}

		$enambledNamespaces = $this->getConfig()->get(
			'RatingenArticleLikeNS'
		);
		if( !in_array( $title->getNamespace(), $enambledNamespaces ) ) {
			//return true;
		}

		$factory = $this->getServices()->getService(
			'BSRatingFactoryArticleLike'
		);
		if( !$rating = $factory->newFromTitle( $title ) ) {
			return true;
		}
		$oStatus = $rating->userCan(
			$this->getContext()->getUser(),
			'read',
			$title
		);
		if( !$oStatus->isOK() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		if( !$title = $this->getArticleLikeContext( $this->skin->getTitle() ) ) {
			return true;
		}
		$factory = $this->getServices()->getService(
			'BSRatingFactoryArticleLike'
		);
		if( !$rating = $factory->newFromTitle( $title ) ) {
			return true;
		}

		$this->template->data['title'] .= $rating->getTag();
		return true;
	}

	/**
	 * Checks wether to set Context or not and returns the context Title.
	 * @param \Title $title
	 * @return Title - or false
	 */
	public function getArticleLikeContext( \Title $title = null ) {
		if( !$title instanceof \Title ) {
			return false;
		}
		if( $this->contextTitle ) {
			return $this->contextTitle;
		}

		$request = $this->skin->getRequest();
		$action = $request->getVal( 'action', 'view' );

		if( !in_array( $action, ['view', 'submit'] ) ) {
			return false;
		}

		if( $title->isRedirect() ) {
			if( $request->getVal( 'redirect' ) != 'no' ) {
				$title = \BsArticleHelper::getInstance( $title )
					->getTitleFromRedirectRecurse();
			}
			if( !$title || !$title->exists() || $title->isRedirect() ) {
				return false;
			}
		}
		if( $title->getNamespace() === NS_SPECIAL ) {
			return false;
		}

		$this->contextTitle = $title;
		return $this->contextTitle;
	}
}
