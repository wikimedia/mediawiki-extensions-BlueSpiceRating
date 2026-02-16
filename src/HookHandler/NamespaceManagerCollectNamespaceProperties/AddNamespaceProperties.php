<?php

namespace BlueSpice\Rating\HookHandler\NamespaceManagerCollectNamespaceProperties;

class AddNamespaceProperties {

	/**
	 * @inheritDoc
	 */
	public function onNamespaceManagerCollectNamespaceProperties(
		int $namespaceId,
		array $globals,
		array &$properties
	): void {
		$properties['rating'] = in_array(
			$namespaceId,
			$globals['bsgRatingArticleEnabledNamespaces'] ?? []
		);
		$properties['recommendations'] = in_array(
			$namespaceId,
			$globals['bsgRatingArticleLikeEnabledNamespaces'] ?? []
		);
	}

}
