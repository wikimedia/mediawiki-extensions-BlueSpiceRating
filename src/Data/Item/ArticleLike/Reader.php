<?php

namespace BlueSpice\Rating\Data\Item\ArticleLike;

class Reader extends \BlueSpice\Rating\Data\Item\Reader {

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	protected function makeSecondaryDataProvider() {
		return null;
	}

	public function getSchema() {
		return new Schema();
	}

}
