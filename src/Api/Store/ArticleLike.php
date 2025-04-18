<?php

namespace BlueSpice\Rating\Api\Store;

use BlueSpice\Context;
use BlueSpice\Rating\Data\Item\ArticleLike\Store;
use MediaWiki\Context\RequestContext;

class ArticleLike extends \BlueSpice\Api\Store {

	protected function makeDataStore() {
		return new Store(
			new Context( RequestContext::getMain(), $this->getConfig() ),
			$this->services->getDBLoadBalancer()
		);
	}
}
