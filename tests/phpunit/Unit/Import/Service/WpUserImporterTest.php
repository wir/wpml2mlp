<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use Brain;
use W2M\Import\Service;
use W2M\Test\Helper;

class WpUserImporterTest extends Helper\MonkeyTestCase {

	private $fs_helper;

	/**
	 * runs before each test
	 */
	public function setUp() {

		if ( ! $this->fs_helper ) {
			$this->fs_helper = new Helper\FileSystem;
		}

		parent::setUp();

	}

	/**
	 * @group import_post
	 */
	public function test_import_user() {

		$this->markTestSkipped( 'Under construction…' );
		/**
		 * Now define the behaviour of the mock object. Each of the specified
		 * methods ( @see ImportUserInterface ) should return a proper value!
		 */
		$userdata = array();

		$origin_user_id = 3;
		$local_user_id = 15;

		$testee->import_user( $user_mock );

	}

}