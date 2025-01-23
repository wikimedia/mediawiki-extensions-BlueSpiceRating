<?php

namespace BlueSpice\Rating\Data;

use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\DatabaseReader;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class Reader extends DatabaseReader {
	/**
	 *
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 */
	public function __construct( $loadBalancer, IContextSource $context = null ) {
		parent::__construct( $loadBalancer, $context, $context->getConfig() );
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db );
	}

	/**
	 *
	 * @return null
	 */
	protected function makeSecondaryDataProvider() {
		return null;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return RatingSet
	 */
	public function read( $params ) {
		$result = parent::read( $params );
		$records = [];
		foreach ( $result->getRecords() as $record ) {
			$records[] = $record;
		}
		$setClass = $this->config->get( 'RatingSetClass' );
		return new $setClass( $records, $result->getTotal() );
	}

}
