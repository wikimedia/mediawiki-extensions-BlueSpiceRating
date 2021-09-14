<?php

namespace BlueSpice\Rating\Hook\UserMergeAccountFields;

use BlueSpice\DistributionConnector\Hook\UserMergeAccountFields;

class MergeRatingDBFields extends UserMergeAccountFields {

	protected function doProcess() {
		$this->updateFields[] = [ 'bs_rating', 'rat_userid', 'rat_userip' ];
	}

}
