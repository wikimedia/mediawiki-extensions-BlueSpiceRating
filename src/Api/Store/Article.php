<?php

namespace BlueSpice\Rating\Api\Store;

use BlueSpice\Context;
use BlueSpice\Rating\Data\Item\Article\Store;
use MediaWiki\Context\RequestContext;

class Article extends \BlueSpice\Api\Store {

	protected function makeDataStore() {
		return new Store(
			new Context( RequestContext::getMain(), $this->getConfig() ),
			$this->services->getDBLoadBalancer()
		);
	}
}
