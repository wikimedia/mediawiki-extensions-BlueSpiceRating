<?php

namespace BlueSpice\Rating\Data\Item;

use \BlueSpice\Data\DatabaseReader;

abstract class Reader extends DatabaseReader {
	/**
	 *
	 * @param \LoadBalancer $loadBalancer
	 * @param \IContextSource $context
	 */
	public function __construct( $loadBalancer, \IContextSource $context = null ) {
		parent::__construct( $loadBalancer, $context, $context->getConfig() );
	}
}
