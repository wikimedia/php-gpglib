<?php

namespace GpgLib;

/**
 * A temporary file that will be cleaned up automatically.
 */
class TempDir {
	protected $path;

	/**
	 * @param string $parentDir Parent directory
	 * @param string $prefix Directory name prefix
	 * @throws GpgLibException
	 */
	public function __construct( $parentDir, $prefix ) {
		$random = $this->getRandomHexString();
		$this->path = $parentDir . DIRECTORY_SEPARATOR  . $prefix . $random;
		$this->make();
	}

	public function __destruct() {
		self::delete( $this->path );
	}

	/**
	 * Returns the full filesystem path to the temporary directory.
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Creates a temporary file in the directory with a random name.
	 * @param string $prefix Filename prefix
	 * @param string $content Initial file content
	 * @return string Full path to the file
	 * @throws GpgLibException
	 */
	public function makeTempFile( $prefix = '', $content = '' ) {
		$fileName = $prefix . $this->getRandomHexString();
		$filePath = $this->path . DIRECTORY_SEPARATOR . $fileName;
		$ok = file_put_contents( $filePath, $content );
		if ( $ok === false ) {
			throw new GpgLibException( "Could not create temporary file at $filePath" );
		}
		return $filePath;
	}

	/**
	 * Returns a random string of 16 hexadecimal characters.
	 */
	protected function getRandomHexString() {
		return substr( md5( uniqid( mt_rand(), true ) ), 0, 16 );
	}

	/**
	 * Actually creates the temporary directory.
	 * @throws GpgLibException
	 */
	protected function make() {
		$ok = mkdir( $this->path );
		if ( !$ok ) {
			throw new GpgLibException( "Could not create temporary directory at $this->path" );
		}

		// the directory is deleted when the TempDir object goes out of scope, but PHP is not very
		// reliable about calling destructors after a fatal error
		register_shutdown_function( array( __CLASS__, 'delete' ), $this->path );

		// make permission setting a separate step as mkdir might fail silently at it
		$ok = chmod( $this->path, 0700 );
		if ( !$ok ) {
			throw new GpgLibException( "Could not make temporary directory private at $this->path" );
		}
	}

	/**
	 * Deletes a temporary directory.
	 * @param string $path Path to the directory
	 * @throws GpgLibException
	 */
	public static function delete( $path ) {
		if ( !is_dir( $path ) ) { // already deleted
			return;
		}
		$files = scandir( $path );
		if ( !$files ) {
			throw new GpgLibException( "Tried to delete temporary directory $path but could not find it" );
		}
		foreach ( $files as $file ) {
			if ( $file === '.' || $file === '..' ) {
				continue;
			}
			$file = $path . DIRECTORY_SEPARATOR . $file;
			$ok = unlink( $file );
			if ( !$ok ) {
				throw new GpgLibException( "Could not delete $file" );
			}
		}
		$ok = rmdir( $path );
		if ( !$ok ) {
			throw new GpgLibException( "Could not delete $path" );
		}
	}
}

