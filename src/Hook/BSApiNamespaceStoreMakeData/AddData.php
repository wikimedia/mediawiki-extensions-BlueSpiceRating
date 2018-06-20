<?php

namespace BlueSpice\Rating\Hook\BSApiNamespaceStoreMakeData;

use BlueSpice\NamespaceManager\Hook\BSApiNamespaceStoreMakeData;

class AddData extends BSApiNamespaceStoreMakeData {
	
	protected function doProcess() {
		$enabledNamespaces = $this->getConfig()->get( 'RatingArticleEnabledNamespaces' );
		$enabledLikeNamespaces = $this->getConfig()->get( 'RatingArticleLikeEnabledNamespaces' );

		foreach( $this->results as $key => &$result ) {
			$result['rating'] =
				in_array( $result[ 'id' ], $enabledNamespaces );
			$result['rating-recommendations'] =
				in_array( $result[ 'id' ], $enabledLikeNamespaces );
		}

		return true;
	}
}
