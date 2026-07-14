<?php
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace BlueSpice\Rating\RatingConfig;

use BlueSpice\Rating\RatingConfig;

/**
 * Article class for Rating extension
 * @package BlueSpiceFoundation
 */
class Article extends RatingConfig {
	/**
	 * @var string
	 */
	protected $type = 'article';

	/**
	 * @return string
	 */
	protected function get_RatingClass() {
		return "\\BlueSpice\\Rating\\RatingItem\\Article";
	}

	/**
	 * @return string
	 */
	protected function get_TypeMsgKey() {
		return "bs-rating-types-page";
	}

	/**
	 * @return string[]
	 */
	protected function get_ModuleScripts() {
		return array_merge(
			parent::get_ModuleScripts(), [
				'ext.rating.starRatingSvg',
				'ext.bluespice.ratingItemArticle'
			]
		);
	}

	/**
	 * @return string[]
	 */
	protected function get_ModuleStyles() {
		return array_merge(
			parent::get_ModuleStyles(),
			[ 'ext.rating.starRatingSvg.styles' ]
		);
	}

	/**
	 * @return int[]
	 */
	protected function get_AllowedValues() {
		return range( 1, 5 );
	}

	/**
	 * @return bool
	 */
	protected function get_UserCanRemoveVote() {
		return false;
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
			'class' => [ 'bs-rating-article' ],
		] );
	}
}
