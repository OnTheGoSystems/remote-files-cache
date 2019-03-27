<?php

class OTGS_Remote_File_Cache {

	const OPTION_KEY_PREFIX = 'otgs_remote_file_hash';

	private $file_url;
	private $option_key_name;
	private $remote_hash;

	/**
	 * OTGS_Remote_File_Cache constructor.
	 *
	 * @param string $file_url
	 * @param string $option_key_name
	 */
	public function __construct( $file_url, $option_key_name ) {
		$this->file_url = $file_url;
		$this->option_key_name = $option_key_name;
	}

	/**
	 * @return bool
	 */
	public function is_up_to_date() {
		return $this->get_remote_hash() === $this->get();
	}

	/**
	 * @param $file_url
	 *
	 * @return string|bool
	 */
	private function get_remote_hash() {
		if ( ! $this->remote_hash ) {
			$head_request = wp_remote_head( $this->file_url );

			if ( is_wp_error( $head_request ) ) {
				return false;
			}

			if ( isset( $head_request['headers']['etag'] ) ) {
				$this->remote_hash = str_replace( '"', '', $head_request['headers']['etag'] );
			} elseif ( $head_request['headers']['last-modified'] ) {
				$this->remote_hash = md5( $head_request['headers']['last-modified'] );
			}
		}

		return $this->remote_hash;
	}

	public function update() {
		update_option( $this->get_option_name(), $this->get_remote_hash() );
	}

	/**
	 * @return string
	 */
	private function get() {
		return get_option( $this->get_option_name() );
	}

	/**
	 * @return string
	 */
	private function get_option_name() {
		return sprintf(
			'%s_%s_%s',
			self::OPTION_KEY_PREFIX,
			$this->option_key_name,
			md5( $this->file_url )
		);
	}
}