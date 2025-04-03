<?php

namespace BlueSpice\Rating\Data\Item;

use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\DatabaseReader;

abstract class Reader extends DatabaseReader {
	/**
	 *
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 */
	public function __construct( $loadBalancer, ?IContextSource $context = null ) {
		parent::__construct( $loadBalancer, $context, $context->getConfig() );
	}
}
