<?php

namespace BlueSpice\Rating\Data\Collection\Article;

class Reader extends \BlueSpice\Rating\Data\Reader {

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db );
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return RatingSet
	 */
	public function read( $params ) {
		$result = parent::read( $params );
		$records = [];
		foreach( $result->getRecords() as $record ) {
			$records[] = $record;
		}
		$setClass = $this->config->get( 'RatingSetClass' );
		return new $setClass( $records, $result->getTotal() );
	}

}
