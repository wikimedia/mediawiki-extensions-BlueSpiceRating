<?php

namespace BlueSpice\Rating\Api\Store;

use BlueSpice\Context;
use BlueSpice\Rating\Data\Collection\Article\Store;
class Article extends \BlueSpice\StoreApiBase {

	protected function makeDataStore() {
		return new Store(
			new Context( \RequestContext::getMain(), $this->getConfig() ),
			\MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}
}