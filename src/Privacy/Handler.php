<?php

namespace BlueSpice\Rating\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;

class Handler implements IPrivacyHandler {
	protected $db;

	public function __construct( \Database $db ) {
		$this->db = $db;
	}

	public function anonymize( $oldUsername, $newUsername ) {
		$this->db->update(
			'bs_rating',
			[ 'rat_userip' => $newUsername ],
			[ 'rat_userip' => $oldUsername ]
		);
		return \Status::newGood();
	}

	public function delete( \User $userToDelete, \User $deletedUser ) {
		$this->anonymize( $userToDelete->getName(), $deletedUser->getName() );

		$this->db->update(
			'bs_rating',
			[ 'rat_userid' => $deletedUser->getId() ],
			[ 'rat_userid' => $userToDelete->getId() ]
		);

		return \Status::newGood();
	}

	public function exportData( array $types, $format, \User $user ) {
		// Where is Rating used?
		return \Status::newGood( [] );
	}
}
