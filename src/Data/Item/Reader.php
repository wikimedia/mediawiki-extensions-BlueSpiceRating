<?php

namespace BlueSpice\Rating\Data\Item;

use MWStake\MediaWiki\Component\DataStore\DatabaseReader;

abstract class Reader extends DatabaseReader {
	/**
	 *
	 * @param \LoadBalancer $loadBalancer
	 * @param \IContextSource|null $context
	 */
	public function __construct( $loadBalancer, \IContextSource $context = null ) {
		parent::__construct( $loadBalancer, $context, $context->getConfig() );
	}
}
