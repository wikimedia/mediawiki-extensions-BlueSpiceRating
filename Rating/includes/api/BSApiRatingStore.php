<?php

class BSApiRatingStore extends BSApiExtJSStoreBase {

	protected function makeData($sQuery = '') {
		$aData = array();
		foreach( $Res as $oRow ) {
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
	 * @param stdClass $row
	 * @return array
	 */
	protected function makeResultRow( $row ) {
		return array(
			
		);
	}
}