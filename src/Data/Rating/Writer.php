<?php

namespace BlueSpice\Rating\Data\Rating;
use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter;

class Writer extends \BlueSpice\Data\DatabaseWriter {

	/**
	 *
	 * @param \BlueSpice\Data\IReader $reader
	 * @param \LoadBalancer $loadBalancer
	 * @param \IContextSource $context
	 */
	public function __construct( \BlueSpice\Data\IReader $reader, $loadBalancer, \IContextSource $context = null ) {
		parent::__construct( $reader, $loadBalancer, $context, $context->getConfig() );
	}

	protected function getTableName() {
		return 'bs_rating';
	}

	/**
	 * @return Schema Column definition compatible to
	 * https://docs.sencha.com/extjs/4.2.1/#!/api/Ext.grid.Panel-cfg-columns
	 */
	public function getSchema() {
		return new Schema();
	}

	protected function getIdentifierFields() {
		return [ Record::ID ];
	}

	/**
	 *
	 * @param BlueSpice\Data\Record $record
	 */
	protected function makeExistingRecordFilter( $record, $fieldName ) {
		return [
			Filter::KEY_FIELD => $fieldName,
			Filter::KEY_VALUE => $record->get( $fieldName ),
			Filter::KEY_TYPE => $this->getFilterFieldTypeMapping( $fieldName ),
			Filter::KEY_COMPARISON => Filter::COMPARISON_EQUALS,
		];
	}
	
	protected function getFilterFieldTypeMapping( $fieldName ) {
		$type = $this->getFieldType( $fieldName );
		if( $type == FieldType::INT ) {
			return 'numeric';
		}
		return $type;
	}
}
