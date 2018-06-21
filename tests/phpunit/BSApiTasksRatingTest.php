<?php

namespace BlueSpice\Rating\Tests;

use BlueSpice\Tests\BSApiTasksTestBase;

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceRating
 */
class BSApiTasksRatingTest extends BSApiTasksTestBase {
	protected function getModuleName() {
		return 'bs-rating-tasks';
	}

	public function setUp() {
		parent::setUp();
		$oDBW = $this->db;
		$oDBW->delete( 'bs_rating', array( 'rat_ref' => 1 ) );
	}

	public function testVote() {
		$oTitle = Title::newFromId( 1 );
		$iRef = $oTitle->getArticleID();

		$aData = array(
			'reftype' => 'article',
			'ref' => $iRef,
			'value' => 3,
			'articleid' => $iRef
		);

		$oResponse = $this->executeTask(
			'vote',
			$aData
		);

		$this->assertTrue( $oResponse->success, 'Vote task failed' );
		$this->assertSelect(
			'bs_rating',
			array( 'rat_reftype', 'rat_userip', 'rat_value' ),
			array( 'rat_ref' => $iRef ),
			array(
				array( 'article', 'Apitestsysop', '3' )
			)
		);
		$this->verifyResponse( $oResponse, 3 );

		$aData['value'] = 4;
		$oResponse = $this->executeTask(
			'vote',
			$aData
		);

		$this->assertTrue( $oResponse->success, 'Vote task failed' );
		$this->assertSelect(
			'bs_rating',
			array( 'rat_value' ),
			array( 'rat_ref' => $iRef ),
			array(
				array( '4' )
			)
		);
		$this->verifyResponse( $oResponse, 4 );
	}

	public function testReload() {
		$oTitle = \Title::newFromId( 1 );
		$iRef = $oTitle->getArticleID();

		$aData = array(
			'reftype' => 'article',
			'ref' => $iRef
		);

		$oResponse = $this->executeTask(
			'reload',
			$aData
		);

		$this->assertTrue( $oResponse->success, 'Reload task failed' );
		$this->verifyResponse( $oResponse, 4 );
	}

	protected function verifyResponse( $oResponse, $iValue ) {
		$aPayload = $oResponse->payload;

		$this->assertArrayHasKey( 'data', $aPayload, 'Payload does not contain "data" key' );
		$oData = json_decode( $aPayload['data'] );

		$this->assertEquals( 'article', $oData->reftype, 'Reftype value is wrong' );
		$this->assertEquals( 1, $oData->ref, 'Ref value is wrong' );

		$oRatings = $oData->ratings;
		foreach( $oRatings as $oRating ) {
			$this->assertEquals( $iValue, $oRating->value );
			$this->assertEquals( 1, $oRating->ref );
		}
	}
}
