<?php

namespace BlueSpice\Rating\Data;

class Record extends \MWStake\MediaWiki\Component\DataStore\Record {
	public const ID = 'rat_id';
	public const REFTYPE = 'rat_reftype';
	public const REF = 'rat_ref';
	public const USERID = 'rat_userid';
	public const USERIP = 'rat_userip';
	public const VALUE = 'rat_value';
	public const CREATED = 'rat_created';
	public const TOUCHED = 'rat_touched';
	public const SUBTYPE = 'rat_subtype';
	public const CONTEXT = 'rat_context';

	public function __clone() {
		$this->dataSet = clone $this->dataSet;
		$this->status = null;
	}
}
