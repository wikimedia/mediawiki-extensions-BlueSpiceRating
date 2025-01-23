<?php

namespace BlueSpice\Rating\Data;

use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\IStore;
use Wikimedia\Rdbms\LoadBalancer;

class Store implements IStore {

	/** @var IContextSource */
	protected $context;

	/** @var LoadBalancer */
	protected $loadBalancer;

	/**
	 * @param IContextSource $context
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( $context, $loadBalancer ) {
		$this->context = $context;
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

	/**
	 *
	 * @return Writer
	 */
	public function getWriter() {
		return new Writer(
			$this->getReader(),
			$this->loadBalancer,
			$this->context
		);
	}
}
