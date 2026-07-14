<?php

namespace BlueSpice\Rating\Api\Task;

use BlueSpice\Rating\Data\Record;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Json\FormatJson;
use MediaWiki\Title\Title;

/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpice_pro
 */
class Rating extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'vote',
		'reload',
	];

	/**
	 * Returns an array of tasks and their required permissions
	 * array('taskname' => array('read', 'edit'))
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'vote' => [ 'read' ],
			'reload' => [ 'read' ],
		];
	}

	/**
	 * @param \stdClass $taskData
	 * @param array $aParams
	 * @return \stdClass
	 */
	public function task_vote( $taskData, $aParams ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$ratingFactory = $this->services->getService( 'BSRatingFactory' );
		$status = $ratingFactory->ensureBasicParams( $taskData );
		if ( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}
		$rating = $ratingFactory->newFromObject( $taskData );
		$title = null;
		if ( !isset( $taskData->{Record::VALUE} ) ) {
			$taskData->{Record::VALUE} = false;
		}
		if ( !empty( $taskData->articleid ) ) {
			$title = Title::newFromID( $taskData->articleid );
		}
		if ( !empty( $taskData->titletext ) ) {
			$title = Title::newFromText( $taskData->titletext );
		}

		$status = $rating->vote(
			$this->getUser(),
			$taskData->{Record::VALUE},
			$this->getUser(),
			0,
			$title
		);
		if ( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}

		if ( $title ) {
			$this->runTitleUpdates( $title );
			$this->runUpdates( $title );
		}

		$result->success = true;
		$result->payload['data'] = FormatJson::encode( $rating );

		return $result;
	}

	/**
	 * @param \stdClass $taskData
	 * @param array $aParams
	 * @return \stdClass
	 */
	public function task_reload( $taskData, $aParams ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$ratingFactory = $this->services->getService( 'BSRatingFactory' );
		$status = $ratingFactory->ensureBasicParams( $taskData );
		if ( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}
		$rating = $ratingFactory->newFromObject( $taskData );

		$result->success = true;
		$result->payload['data'] = FormatJson::encode( $rating );

		return $result;
	}

	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		return parent::getAllowedParams() + [

		];
	}

	/**
	 * Returns the basic param descriptions
	 * @return array
	 */
	public function getParamDescription() {
		return parent::getParamDescription() + [

		];
	}

	/**
	 * @param Title $title
	 * @return void
	 */
	private function runTitleUpdates( Title $title ) {
		$wikiPage = $this->services->getWikiPageFactory()->newFromTitle( $title );
		$wikiPage->doSecondaryDataUpdates( [
			'triggeringUser' => $this->getUser(),
			'defer' => DeferredUpdates::POSTSEND
		] );
	}

}
