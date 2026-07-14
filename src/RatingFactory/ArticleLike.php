<?php

namespace BlueSpice\Rating\RatingFactory;

use BlueSpice\Rating\Data\Record;
use BlueSpice\Rating\RatingFactory;
use MediaWiki\Title\Title;

class ArticleLike extends RatingFactory {
	/**
	 * ArticleLike from a Title object
	 * @param Title $title
	 * @return \BlueSpice\Rating\RatingFactory\ArticleLike|null
	 */
	public function newFromTitle( Title $title ) {
		return $this->newFromObject( (object)[
			Record::REFTYPE => 'articlelike',
			Record::REF => $title->getArticleID(),
			Record::SUBTYPE => '',
		] );
	}
}
