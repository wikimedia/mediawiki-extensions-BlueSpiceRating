<?php

namespace BlueSpice\Rating\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddRecommendationsGlobalAction extends SkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		if( !$special = \SpecialPageFactory::getPage( 'Recommendations' ) ) {
			return true;
		}
		if( !$special->userCanExecute( $this->skin->getUser() ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$special = \SpecialPageFactory::getPage( 'Recommendations' );
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-special-recommendations' => [
					'href' => $special->getPageTitle()->getFullURL(),
					'text' => $special->getDescription(),
					'title' => $special->getPageTitle(),
					'classes' => ' icon-special-recommendations ',
					'position' => 30
				]
			]
		);

		return true;
	}
}