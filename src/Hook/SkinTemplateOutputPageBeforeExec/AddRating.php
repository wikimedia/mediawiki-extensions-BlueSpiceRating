<?php

namespace BlueSpice\Rating\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddRating extends SkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$specialRating = \SpecialPageFactory::getPage( 'Rating' );
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-special-rating' => [
					'href' => $specialRating->getPageTitle()->getFullURL(),
					'text' => $specialRating->getDescription(),
					'title' => $specialRating->getPageTitle(),
					'classes' => ' icon-special-rating ',
					'position' => 30
				]
			]
		);

		return true;
	}
}