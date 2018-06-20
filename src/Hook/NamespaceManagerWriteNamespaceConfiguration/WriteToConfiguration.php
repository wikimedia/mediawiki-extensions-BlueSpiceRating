<?php

namespace BlueSpice\Rating\Hook\NamespaceManagerWriteNamespaceConfiguration;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerWriteNamespaceConfiguration;

class WriteToConfiguration extends NamespaceManagerWriteNamespaceConfiguration {
	protected function skipProcessing() {
		if( $this->ns === null ) {
			return true;
		}
		return false;
	}
	
	protected function doProcess() {
		$this->writeConfiguration( "RatingArticleEnabledNamespaces", "rating-stars" );
		$this->writeConfiguration( "RatingArticleLikeEnabledNamespaces", "rating-likes" );

		return true;
	}

	protected function writeConfiguration( $configVar, $nsManagerOptionName ) {
		$enabledNamespaces = $this->getConfig()->get( $configVar );

		$currentlyActivated = in_array( $this->ns, $enabledNamespaces );

		$explicitlyDeactivated = false;
		if ( isset( $this->definition[$nsManagerOptionName] ) && $this->definition[$nsManagerOptionName] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $this->definition[$nsManagerOptionName] ) && $this->definition[$nsManagerOptionName] === true ) {
			$explicitlyActivated = true;
		}

		if( ( $currentlyActivated && !$explicitlyDeactivated ) || $explicitlyActivated ) {
			$this->saveContent .= "\$GLOBALS['bsg$configVar'][] = {$this->constName};\n";
		}
	}

}