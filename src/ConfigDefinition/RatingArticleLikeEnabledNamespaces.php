<?php

namespace BlueSpice\Rating\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;

class RatingArticleLikeEnabledNamespaces extends ArraySetting {

	public function getLabelMessageKey() {
		return 'bs-rating-toc-enarticlelikens';
	}

	public function isRLConfigVar() {
		return true;
	}
}
