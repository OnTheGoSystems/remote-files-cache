<?php
/**
 * Class Test_OTGS_Remote_File_Cache
 *
 * @group installer-555
 */

class Test_OTGS_Remote_File_Cache extends OTGS_TestCase {

	/**
	 * @test
	 */
	public function it_should_return_false_if_remote_file_hash_could_not_be_achieved() {
		$remote_url = 'http://something.com/products.json';
		$subject    = new OTGS_Remote_File_Cache( $remote_url );

		$response = 'WP_Error';

		\WP_Mock::userFunction( 'get_option', array(
			'args'   => OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ),
			'return' => 'local-hash',
		) );

		\WP_Mock::userFunction( 'wp_remote_head', array(
			'args'   => $remote_url,
			'return' => $response,
		) );

		\WP_Mock::userFunction( 'is_wp_error', array(
			'return' => true,
		) );

		$this->assertFalse( $subject->is_up_to_date() );
	}

	/**
	 * @test
	 */
	public function it_should_return_false_and_cache_the_current_etag_if_remote_file_hash_is_different_than_the_local_one() {
		$remote_url = 'http://something.com/products.json';
		$subject    = new OTGS_Remote_File_Cache( $remote_url );
		$hash       = '"some-etag-hash"';

		$response = array(
			'headers' => array(
				'etag' => $hash,
			),
		);

		\WP_Mock::userFunction( 'wp_remote_head', array(
			'args'   => $remote_url,
			'return' => $response,
		) );

		\WP_Mock::userFunction( 'is_wp_error', array(
			'return' => false,
		) );

		\WP_Mock::userFunction( 'get_option', array(
			'args'   => OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ),
			'return' => 'some-old-hash',
		) );

		$this->assertFalse( $subject->is_up_to_date() );
	}

	/**
	 * @test
	 */
	public function it_should_return_false_and_cache_the_current_last_modified_hash_when_etag_is_not_found_when_remote_file_hash_is_different_than_the_local_one() {
		$remote_url = 'http://something.com/products.json';
		$subject    = new OTGS_Remote_File_Cache( $remote_url );
		$last_modified_date = 'Mon Jan 17th';

		$response = array(
			'headers' => array(
				'last-modified' => $last_modified_date,
			),
		);

		\WP_Mock::userFunction( 'wp_remote_head', array(
			'args'   => $remote_url,
			'return' => $response,
		) );

		\WP_Mock::userFunction( 'is_wp_error', array(
			'return' => false,
		) );

		\WP_Mock::userFunction( 'get_option', array(
			'args'   => OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ),
			'return' => 'some-old-hash',
		) );

		$this->assertFalse( $subject->is_up_to_date() );
	}

	/**
	 * @test
	 */
	public function it_should_update_file_cache_with_remote_etag_when_found() {
		$remote_url = 'http://something.com/products.json';
		$subject    = new OTGS_Remote_File_Cache( $remote_url );
		$new_hash = 'new-hash';

		$response = array(
			'headers' => array(
				'etag' => $new_hash,
			),
		);

		\WP_Mock::userFunction( 'wp_remote_head', array(
			'args'   => $remote_url,
			'return' => $response,
		) );

		\WP_Mock::userFunction( 'is_wp_error', array(
			'return' => false,
		) );

		\WP_Mock::userFunction( 'get_option', array(
			'args'   => OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ),
			'return' => 'some-old-hash',
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'args'   => array( OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ), $new_hash ),
			'times'  => 1,
		) );

		$subject->update();
	}

	/**
	 * @test
	 */
	public function it_should_update_file_cache_with_remote_last_modified_date_when_etag_is_not_found() {
		$remote_url = 'http://something.com/products.json';
		$subject    = new OTGS_Remote_File_Cache( $remote_url );
		$last_modified_date = 'Mon Jan 17th';

		$response = array(
			'headers' => array(
				'last-modified' => $last_modified_date,
			),
		);

		\WP_Mock::userFunction( 'wp_remote_head', array(
			'args'   => $remote_url,
			'return' => $response,
		) );

		\WP_Mock::userFunction( 'is_wp_error', array(
			'return' => false,
		) );

		\WP_Mock::userFunction( 'get_option', array(
			'args'   => OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ),
			'return' => 'some-old-hash',
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'args'   => array( OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ), md5( $last_modified_date ) ),
			'times'  => 1,
		) );

		$subject->update();
	}

	/**
	 * @test
	 */
	public function it_should_return_true_and_cache_the_current_file_for_the_request_if_the_remote_file_hash_is_equal_than_the_local_one() {
		$remote_url = 'http://something.com/products.json';
		$subject    = new OTGS_Remote_File_Cache( $remote_url );
		$hash       = '"some-etag-hash"';

		$response = array(
			'headers' => array(
				'etag' => $hash,
			),
		);

		\WP_Mock::userFunction( 'wp_remote_head', array(
			'args'   => $remote_url,
			'return' => $response,
		) );

		\WP_Mock::userFunction( 'is_wp_error', array(
			'return' => false,
		) );

		\WP_Mock::userFunction( 'get_option', array(
			'args'   => OTGS_Remote_File_Cache::OPTION_KEY_PREFIX . md5( $remote_url ),
			'return' => 'some-etag-hash',
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'times' => 0,
		) );

		$this->assertTrue( $subject->is_up_to_date() );
	}
}