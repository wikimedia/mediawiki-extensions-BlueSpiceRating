<?php

namespace BlueSpice\Rating\Data\Item\ArticleLike;

use LogicException;

class Store extends \BlueSpice\Rating\Data\Store {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

	/**
	 * @throws LogicException
	 */
	public function getWriter() {
		throw new LogicException( 'Write is not supported' );
	}
}
