<?php

namespace BlueSpice\Rating\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;

class RatingArticleEnabledNamespaces extends ArraySetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_PERSONALISATION . '/BlueSpiceRating',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceRating/' . static::FEATURE_PERSONALISATION ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceRating',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-rating-toc-enratingns';
	}

	public function isRLConfigVar() {
		return true;
	}

	public function getOptions() {
		$language = \RequestContext::getMain()->getLanguage();
		$exclude = array( NS_MEDIAWIKI, NS_SPECIAL, NS_MEDIA );
		foreach ( $language->getNamespaces() as $namespace ) {
			$nsIndx = $language->getNsIndex( $namespace );
			if( !\MWNamespace::isTalk( $nsIndx ) ) {
				continue;
			}
			$exclude[] = $nsIndx;
		}
		return \BsNamespaceHelper::getNamespacesForSelectOptions( $exclude );
	}
}
