<?php

namespace BlueSpice\Rating\Data\Item;

use MWStake\MediaWiki\Component\DataStore\FieldType;

abstract class Schema extends \MWStake\MediaWiki\Component\DataStore\Schema {
	/**
	 *
	 * @param Record[] $data
	 */
	public function __construct( $data ) {
		parent::__construct( array_merge( $data, [
			Record::REFTYPE => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			Record::REF => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::SUBTYPE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::CONTENT => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
		] ) );
	}
}
