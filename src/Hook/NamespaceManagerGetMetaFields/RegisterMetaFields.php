<?php

namespace BlueSpice\Rating\Hook\NamespaceManagerGetMetaFields;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerGetMetaFields;

class RegisterMetaFields extends NamespaceManagerGetMetaFields {

	/**
	 * @inheritDoc
	 */
	protected function doProcess() {
		$this->metaFields[] = [
			'name' => 'rating',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-rating-nsm-label' )->plain(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		$this->metaFields[] = [
			'name' => 'recommendations',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-rating-nsm-label-recommendations' )->plain(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		return true;
	}
}
