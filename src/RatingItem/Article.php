<?php

namespace BlueSpice\Rating\RatingItem;

use BlueSpice\Rating\Data\Record;
use BlueSpice\Rating\RatingItem;
use MediaWiki\Context\RequestContext;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class Article extends RatingItem {
	/** @inheritDoc */
	protected $refType = 'article';

	/**
	 * Article from a Title object
	 * @param Title $title
	 * @return Article
	 */
	public static function newFromTitle( Title $title ) {
		return static::newFromObject( (object)[
			Record::REFTYPE => 'article',
			Record::REF => $title->getArticleID(),
			Record::SUBTYPE => '',
		] );
	}

	public function jsonSerialize(): array {
		$data = parent::jsonSerialize();
		$status = $this->userCan(
			RequestContext::getMain()->getUser(),
			'update',
			Title::newFromID( $this->getRef() )
		);
		$data['usercanmodify'] = $status->isOK();
		return $data;
	}

	/**
	 * @param User $user
	 * @param string $action
	 * @param Title|null $title
	 * @return Status
	 */
	public function userCan( User $user, $action = 'read', ?Title $title = null ) {
		if ( !$title ) {
			$title = Title::newFromID( (int)$this->getRef() );
		}
		return parent::userCan( $user, $action, $title );
	}
}
