<?php
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace BlueSpice\Rating\RatingConfig;

use BlueSpice\Rating\RatingConfig;

/**
 * ArticleLike class for Rating extension
 * @package BlueSpiceFoundation
 */
class ArticleLike extends RatingConfig {
	/**
	 * @var string
	 */
	protected $type = 'articlelike';

	/**
	 * @return string
	 */
	protected function get_RatingClass() {
		return "\\BlueSpice\\Rating\\RatingItem\\ArticleLike";
	}

	/**
	 * @return string
	 */
	protected function get_TypeMsgKey() {
		return "bs-rating-types-pagelike";
	}

	/**
	 * @return string[]
	 */
	protected function get_ModuleScripts() {
		return array_merge(
			parent::get_ModuleScripts(), [
				'ext.bluespice.ratingItemArticleLike'
			]
		);
	}

	/**
	 * @return string[]
	 */
	protected function get_ModuleStyles() {
		return array_merge(
			parent::get_ModuleStyles(),
			[ 'ext.bluespice.ratingItemArticleLike.styles' ]
		);
	}

	/**
	 * @return int[]
	 */
	protected function get_AllowedValues() {
		return [ 1 ];
	}

	/**
	 * @return bool
	 */
	protected function get_UserCanRemoveVote() {
		return true;
	}

	/**
	 * @return bool
	 */
	protected function get_PermissionTitleRequired() {
		return true;
	}

	/**
	 * @return array
	 */
	protected function get_HTMLTagOptions() {
		return array_merge_recursive( parent::get_HTMLTagOptions(), [
			'class' => [ 'bs-rating-articlelike' ],
		] );
	}
}
