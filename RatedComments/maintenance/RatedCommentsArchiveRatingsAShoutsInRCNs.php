<?php
/**
 * Maintenance script to remove leftover ratings and shouts in RatedComments aktivated namespaces
 *
 * @file
 * @ingroup Maintenance
 * @author Patric Wirth <wirth@hallowelt.com>
 * @licence GNU General Public Licence 2.0 or later
 */

require_once( dirname(dirname(dirname(dirname( dirname( __FILE__ ))))).'/maintenance/Maintenance.php' );

class RatedCommentsArchiveRatingsAShoutsInRCNs extends Maintenance {
	protected $aEnNs = array();
	protected $bExecute;
	protected $aLeftOverShoutIDs = array();
	protected $aLeftOverRatingIDs = array();

	public function __construct() {
		parent::__construct();

		$this->addOption( 'execute', 'Realy execute script', false, false );
	}

	public function execute() {
		$this->bExecute = $this->getOption( 'execute', false );
		$this->aEnNs = BsConfig::get('MW::RatedComments::enRatedCommentsNS');
		echo "\n";
		if( empty($this->aEnNs) ) {
			echo 'RatedComments is not activated in any Namespace';
			return;
		}

		echo "1. cheking for pages with ratings or shouts...\n";
		$aTitleIDs = array_unique(
			$this->getAllTitleIDsWhereRatingOrShout()
		);

		if( empty($aTitleIDs) ) {
			echo " - no rating or shout was found in activated namespaces\n";
			return;
		}

		echo " -".count($aTitleIDs)." Titles include rating or shout\n\n";

		echo "2. Checking for not connected shouts and ratings...\n";
		foreach( $aTitleIDs as $iID ) {
			$this->getLeftOverShoutsAndRatings( $iID );
		}
		echo " -".count($this->aLeftOverShoutIDs)." leftover shouts found\n";
		echo " -".count($this->aLeftOverRatingIDs)." leftover ratings found\n";

		if( empty($this->aLeftOverShoutIDs) && empty($this->aLeftOverRatingIDs) ) {
			echo "nothing to do here: YAY! \n\n";
			return;
		}
		echo "3. Archive not connected shouts and ratings...\n";
		if( !empty($this->aLeftOverShoutIDs) ) {
			$b = $this->archiveShoutsByIDs($this->aLeftOverShoutIDs);
			echo " -Archived shouts: ".($b?"OK.":"Failed")."\n";
		}
		if( !empty($this->aLeftOverShoutIDs) ) {
			$b = $this->archiveRatingsByIDs($this->aLeftOverRatingIDs);
			echo " -Archived ratings: ".($b?"OK.":"Failed")."\n";
		}
		
		echo "\n";
		if(!$this->bExecute) 
			echo "Script is in testmode: use --execute as param to realy execute the script!\n\n";

		return;
	}

	private function getLeftOverShoutsAndRatings( $iID ) {
		$dbr = wfGetDB( DB_SLAVE );
		$rRes = $dbr->select(
			array('bs_shoutbox', 'bs_rating'),
			array('sb_id', 'rat_id'),
			array(
				'sb_page_id' => $iID,
				'sb_archived' => '0',
				'rat_archived' => '0',
				'sb_user_id = rat_userid',
				'sb_page_id = rat_ref',
				'sb_title != ""', //quick and dirty
			),
			__METHOD__,
			array(
				'GROUP BY' => 'sb_user_id',
				'ORDER BY' => 'sb_timestamp DESC',
			)
		);

		$aConnectedShouts = array();
		$aConnectedRatings = array();
		
		if( $rRes->numRows() > 0) {
			while( $row = $dbr->fetchObject($rRes) ) {
				$aConnectedShouts[] = $row->sb_id;
				$aConnectedRatings[] = $row->rat_id;
			}
		}

		$aConditions = array(
			'sb_page_id' => $iID,
			'sb_archived' => '0',
		);
		if( !empty($aConnectedShouts) ) $aConditions[] = "sb_id NOT IN('".implode("','", $aConnectedShouts )."')";
		$rRes = $dbr->select(
			array('bs_shoutbox'),
			'sb_id',
			$aConditions
		);
		if($rRes->numRows() > 0){
			while( $row = $dbr->fetchObject($rRes)) {
				$this->aLeftOverShoutIDs[] = $row->sb_id;
			}
		}

		$aConditions = array(
			'rat_ref' => $iID,
			'rat_reftype' => 'article',
			'rat_archived' => '0',
		);
		if( !empty($aConnectedRatings) ) $aConditions[] = "rat_id NOT IN('".implode("','", $aConnectedRatings )."')";
		$rRes = $dbr->select(
			array('bs_rating'),
			'rat_id',
			$aConditions
		);
		if($rRes->numRows() > 0){
			while( $row = $dbr->fetchObject($rRes)) {
				$this->aLeftOverRatingIDs[] = $row->rat_id;
			}
		}
		
		return;
	}

	private function getAllTitleIDsWhereRatingOrShout( $aTitleIDs = array() ) {
		$dbr = wfGetDB( DB_SLAVE );

		$rRes = $dbr->select(
			array('page'),
			'page_id',
			array(
				'page_namespace' => $this->aEnNs,
			)
		);
		if($rRes->numRows() < 1) return $aTitleIDs;

		$aActivatedIDs = array();
		while( $row = $dbr->fetchObject($rRes)) {
			$aActivatedIDs[] = $row->page_id;
		}

		$rRes = $dbr->select(
			array('bs_shoutbox'),
			'sb_page_id',
			array(
				'sb_page_id' => $aActivatedIDs,
				'sb_archived' => '0',
			)
		);
		if($rRes->numRows() > 0){
			while( $row = $dbr->fetchObject($rRes)) {
				$aTitleIDs[] = $row->sb_page_id;
			}
		}

		$rRes = $dbr->select(
			array('bs_rating'),
			'rat_ref',
			array(
				'rat_ref' => $aActivatedIDs,
				'rat_reftype' => 'article',
				'rat_archived' => '0',
			)
		);
		if($rRes->numRows() > 0){
			while( $row = $dbr->fetchObject($rRes)) {
				$aTitleIDs[] = $row->rat_ref;
			}
		}

		return $aTitleIDs;
	}
	
	private function archiveShoutsByIDs( $aIDs = array() ) {
		if( !$this->bExecute ) return true;

		$dbr = wfGetDB( DB_SLAVE );
		return $dbr->update(
			'bs_shoutbox', 
			array('sb_archived' => '1'), 
			array('sb_id' => $aIDs)
		);
	}
	
	private function archiveRatingsByIDs( $aIDs = array() ) {
		if( !$this->bExecute ) return true;

		$dbr = wfGetDB( DB_SLAVE );
		return $dbr->update(
			'bs_rating', 
			array('rat_archived' => '1'), 
			array('rat_id' => $aIDs)
		);
	}
}

$maintClass = 'RatedCommentsArchiveRatingsAShoutsInRCNs';
if (defined('RUN_MAINTENANCE_IF_MAIN')) {
	require_once( RUN_MAINTENANCE_IF_MAIN );
} else {
	require_once( DO_MAINTENANCE ); # Make this work on versions before 1.17
}