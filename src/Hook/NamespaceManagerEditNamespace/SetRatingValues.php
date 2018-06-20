<?php

namespace BlueSpice\Rating\Hook\NamespaceManagerEditNamespace;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerEditNamespace;

class SetRatingValues extends NamespaceManagerEditNamespace {
	
	protected function doProcess() {
		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['rating-stars'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'rating-stars' ] = $this->additionalSettings['rating-stars'];
		}
		else {
			$this->namespaceDefinition[$this->nsId][ 'rating-stars' ] = false;
		}

		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['rating-likes'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'rating-likes' ] = $this->additionalSettings['rating-likes'];
		}
		else {
			$this->namespaceDefinition[$this->nsId][ 'rating-likes' ] = false;
		}
		return true;
	}

}
