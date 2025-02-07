<?php

use MediaWiki\Maintenance\LoggedUpdateMaintenance;

$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once "$IP/maintenance/Maintenance.php";

class BSRatingRemoveDuplicateEntries extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return true
	 */
	protected function doDBUpdates() {
		$ids = $ips = $deleteEntries = [];
		$res = $this->getDB( DB_PRIMARY )->select(
			'bs_rating',
			'*',
			[],
			__METHOD__,
			[ 'ORDER BY' => 'rat_touched desc' ]
		);
		foreach ( $res as $row ) {
			$hash = md5(
				$row->rat_reftype
					. $row->rat_ref
					. $row->rat_subtype
					. $row->rat_context
			);
			if ( isset( $ids[ $hash ] ) && $ids[ $hash ] == $row->rat_userid ) {
				$deleteEntries[] = $row->rat_id;
				continue;
			}
			if ( isset( $ips[ $hash ] ) && $ips[ $hash ] == $row->rat_userid ) {
				$deleteEntries[] = $row->rat_id;
				continue;
			}
			$ids[ $hash ] = $row->rat_userid;
			$ips[ $hash ] = $row->rat_userip;
		}
		if ( empty( $deleteEntries ) ) {
			return true;
		}
		$b = $this->getDB( DB_PRIMARY )->delete(
			'bs_rating',
			[ 'rat_id' => $deleteEntries ]
		);
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_rating-removeduplicateentries';
	}

}
