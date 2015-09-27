<?php

namespace GpgLib;

/**
 * Configuration container for temporary dirs.
 */
class TempDirFactory {
	protected $parentDir;
	protected $prefix;

	/**
	 * @param string $parentDir Parent directory
	 * @param string $prefix Filename prefix
	 */
	public function __construct( $parentDir = null, $prefix = 'gpglib-' ) {
		if ( !$parentDir ) {
			if ( function_exists( 'sys_get_temp_dir' ) ) { // PHP 5.2.1+
				$parentDir = sys_get_temp_dir();
			} else {
				$parentDir = '/tmp';
			}
		}

		$this->parentDir = $parentDir;
		$this->prefix = $prefix;
	}

	/**
	 * @return TempDir
	 */
	public function create() {
		return new TempDir( $this->parentDir, $this->prefix );
	}
}

