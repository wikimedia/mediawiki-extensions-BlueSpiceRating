<?php

namespace BlueSpice\Rating\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddRecommendationsGlobalAction extends SkinTemplateOutputPageBeforeExec {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$special = \SpecialPageFactory::getPage( 'Recommendations' );
		if ( !$special ) {
			return true;
		}
		if ( !$special->userCanExecute( $this->skin->getUser() ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$special = \SpecialPageFactory::getPage( 'Recommendations' );
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-special-recommendations' => [
					'href' => $special->getPageTitle()->getFullURL(),
					'text' => $special->getDescription(),
					'title' => $special->getPageTitle(),
					'iconClass' => ' icon-special-recommendations ',
					'position' => 30
				]
			]
		);

		return true;
	}
}
