<?php

namespace BlueSpice\Rating\Api\Store;

class Rating extends \BSApiExtJSStoreBase {

	protected function getRequiredPermissions() {
		return array( 'rating-read' );
	}

	protected function makeData($sQuery = '') {
		$aData = array();

		$aTables = array( 
			'bs_rating' 
		);
		$aFields = array( 
			'rat_ref',
			'rat_reftype', 
			'ROUND(AVG( rat_value ),1) AS vote', 
			'COUNT(rat_value) as votes' 
		);
		$aOptions = array(
			'GROUP BY' => 'rat_reftype, rat_ref',
		);
		$aConditions = array(
			'rat_archived' => '0',
		);

		//hard coded: only article ratings are supported :(
		//$aTables[] = 'page';
		//$aConditions['page_id'] = 'rat_ref';
		$aConditions['rat_reftype'] = 'article';
		$aConditions['rat_context'] = '';

		$oRes = $this->getDB( DB_SLAVE )->select(
			$aTables,
			$aFields,
			$aConditions,
			__METHOD__,
			$aOptions
		);
		foreach( $oRes as $oRow ) {
			$aResRow = $this->makeResultRow( $oRow );
			if( !$aResRow ) {
				continue;
			}
			$aData[] = (object)$aResRow;
		}

		return $aData;
	}

	/**
	 * Builds a single result set
	 * @param stdClass $oRow
	 * @return array
	 */
	protected function makeResultRow( $oRow ) {
		$oTitle = \Title::newFromID( $oRow->rat_ref );
		if( !$oTitle ) {
			return false;
		}
		return (array) $oRow + array(
			'refcontent' => \Linker::link( $oTitle ),
			'page_title' => $oTitle->getFullText(),
		);
	}
}