<?php

namespace BlueSpice\Rating\Data\Item\Article;

use BsNamespaceHelper;

class SecondaryDataProvider extends \MWStake\MediaWiki\Component\DataStore\SecondaryDataProvider {

	public function __construct() {
	}

	/**
	 *
	 * @param Record &$dataSet
	 */
	protected function doExtend( &$dataSet ) {
		$dataSet->set(
			Record::PAGENAMESPACETEXT,
			$this->getNameSpaceText( $dataSet->get( Record::PAGENAMESPACE ) )
		);
	}

	/**
	 *
	 * @param int $nsId
	 * @return string
	 */
	private function getNameSpaceText( $nsId = 0 ) {
		return BsNamespaceHelper::getNamespaceName( $nsId );
	}
}
