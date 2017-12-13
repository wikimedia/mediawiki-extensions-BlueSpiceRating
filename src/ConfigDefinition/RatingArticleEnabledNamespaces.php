<?php

namespace BlueSpice\Rating\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;

class RatingArticleEnabledNamespaces extends ArraySetting {

	public function getLabelMessageKey() {
		return 'bs-rating-toc-enratingns';
	}

	public function isRLConfigVar() {
		return true;
	}
}
