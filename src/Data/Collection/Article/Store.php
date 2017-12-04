<?php

namespace BlueSpice\Rating\Data\Collection\Article;

class Store extends \BlueSpice\Rating\Data\Store {

	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

	public function getWriter() {
		throw new \MWException( 'Write is not supported' );
	}
}
