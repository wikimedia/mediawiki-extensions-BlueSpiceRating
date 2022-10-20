<?php

namespace BlueSpice\Rating\Data\Item;

abstract class Record extends \MWStake\MediaWiki\Component\DataStore\Record {
	public const REFTYPE = 'rat_reftype';
	public const REF = 'rat_ref';
	public const SUBTYPE = 'rat_subtype';
	public const ITEM = 'item';
	public const CONTENT = 'content';
}
