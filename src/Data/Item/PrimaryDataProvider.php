<?php

namespace BlueSpice\Rating\Data\Item;

use MediaWiki\MediaWikiServices;

abstract class PrimaryDataProvider extends \BlueSpice\Rating\Data\PrimaryDataProvider {

	/**
	 *
	 * @var \ContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 */
	public function __construct( $db, $context ) {
		$this->context = $context;
		parent::__construct( $db );
	}

	protected function checkRatingPermission( $rating ) {
		$user = $this->context->getUser();
		$status = $rating->userCan( $user, 'read' );
		if( !$status->isOK() ) {
			return false;
		}
		return true;
	}

	protected function appendRowToData( $row ) {
		$title = \Title::newFromID( $row->page_id );
		if( !$title ) {
			return;
		}
		$rating = $this->makeRatingItem( $row );
		if( !$rating ) {
			return;
		}
		if( !$this->checkRatingPermission( $rating ) ) {
			return;
		}

		$this->data[] = new \BlueSpice\Data\Record( (object)
			$this->extractDataFromRow( $row, $rating )
		);
	}

	protected function extractDataFromRow( $row, $rating ) {
		return [
			Record::REFTYPE => $row->{Record::REFTYPE},
			Record::REF => $row->{Record::REF},
			Record::SUBTYPE => $row->{Record::SUBTYPE},
			Record::ITEM => \FormatJson::encode( $rating ),
			Record::CONTENT => $rating->getTag(),
		];
	}

	protected function makeRatingItem( $row ) {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSRatingFactory'
		);
		return $factory->newFromObject( $row );
	}
}
