<?php

namespace BlueSpice\Rating\Data;

use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\DatabaseWriter;
use MWStake\MediaWiki\Component\DataStore\IReader;

class Writer extends DatabaseWriter {

	/**
	 *
	 * @param IReader $reader
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 */
	public function __construct( IReader $reader, $loadBalancer,
		?IContextSource $context = null ) {
		parent::__construct( $reader, $loadBalancer, $context, $context->getConfig() );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTableName() {
		return 'bs_rating';
	}

	/**
	 * @return Schema Column definition compatible to
	 * https://docs.sencha.com/extjs/4.2.1/#!/api/Ext.grid.Panel-cfg-columns
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @return array
	 */
	protected function getIdentifierFields() {
		return [ Record::ID ];
	}
}
