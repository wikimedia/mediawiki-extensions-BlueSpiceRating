<?php

namespace BlueSpice\Rating\RatingFactory;

use BlueSpice\Rating\Data\Record;
use BlueSpice\Rating\RatingFactory;
use MediaWiki\Title\Title;

class Article extends RatingFactory {
	/**
	 * Article from a Title object
	 * @param Title $title
	 * @return \BlueSpice\Rating\RatingItem\Article|null
	 */
	public function newFromTitle( Title $title ) {
		return $this->newFromObject( (object)[
			Record::REFTYPE => 'article',
			Record::REF => $title->getArticleID(),
			Record::SUBTYPE => '',
		] );
	}
}
