<?php

namespace BlueSpice\Rating\Hook\NamespaceManagerEditNamespace;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerEditNamespace;

class SetRatingValues extends NamespaceManagerEditNamespace {
	
	protected function doProcess() {
		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['rating'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'rating' ] = $this->additionalSettings['rating'];
		}
		else {
			$this->namespaceDefinition[$this->nsId][ 'rating' ] = false;
		}

		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['rating-recommendations'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'rating-recommendations' ] = $this->additionalSettings['rating-recommendations'];
		}
		else {
			$this->namespaceDefinition[$this->nsId][ 'rating-recommendations' ] = false;
		}
		return true;
	}

}
