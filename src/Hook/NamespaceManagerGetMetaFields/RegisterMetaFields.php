<?php

namespace BlueSpice\Rating\Hook\NamespaceManagerGetMetaFields;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerGetMetaFields;

class RegisterMetaFields extends NamespaceManagerGetMetaFields {
	
	protected function doProcess() {
		$this->metaFields[] = [
			'name' => 'rating-stars',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-rating-nsm-label-stars' )->plain(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		$this->metaFields[] = [
			'name' => 'rating-likes',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-rating-nsm-label-likes' )->plain(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		return true;
	}
}
