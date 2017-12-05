<?php

namespace BlueSpice\Rating\Data\Item\ArticleLike;

use BlueSpice\Data\FilterFinder;
use BlueSpice\Rating\Data\Schema;

class PrimaryDataProvider extends \BlueSpice\Rating\Data\Item\PrimaryDataProvider {

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 */
	public function makeData( $params ) {
		$this->data = [];

		$fields = [
			'rat_ref',
			'rat_reftype',
			'COUNT(rat_value) as totalcount',
			'page_id',
			'page_title',
			'page_namespace',
		];

		$res = $this->db->select(
			['bs_rating', 'page'],
			$fields,
			$this->makePreFilterConds( $params ),
			__METHOD__,
			$this->makePreOptionConds( $params )
		);

		foreach( $res as $row ) {
			$this->appendRowToData( $row );
		}

		return $this->data;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( $params ) {
		$conds = [
			'rat_reftype' => 'articlelike',
			'page_id = rat_ref',
		];
		$schema = new Schema();
		$fields = array_values( $schema->getFilterableFields() );
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach( $fields as $fieldName ) {
			$filter = $filterFinder->findByField( $fieldName );
			if( !$filter instanceof Filter ) {
				continue;
			}
			$conds[$fieldName] = $filter->getValue();
		}
		return $conds;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	protected function makePreOptionConds( $params ) {
		$conds = [
			'GROUP BY' => 'rat_reftype, rat_ref, rat_subtype',
		];

		$schema = new Schema();
		$fields = array_values( $schema->getSortableFields() );

		foreach( $params->getSort() as $sort ) {
			if( !in_array( $sort->getProperty(), $fields ) ) {
				continue;
			}
			if( !isset( $conds['ORDER BY'] ) ) {
				$conds['ORDER BY'] = "";
			} else {
				$conds['ORDER BY'] .= ",";
			}
			$conds['ORDER BY'] .=
				"{$sort->getProperty()} {$sort->getDirection()}";
		}
		return $conds;
	}

	protected function extractDataFromRow( $row, $rating ) {
		return array_merge( parent::extractDataFromRow( $row, $rating ), [
			Record::TOTALCOUNT => $row->{Record::TOTALCOUNT},
			Record::PAGENAMESPACE => $row->{Record::PAGENAMESPACE},
			Record::PAGETITLE => $row->{Record::PAGETITLE},
		]);
	}
}
