<?php

namespace GpgLib\Test;


use GpgLib\TempDir;

class TempDirTest extends \PHPUnit_Framework_TestCase {
	public function testExistence() {
		$tempDir = new TempDir( '/tmp', 'foo_' );
		$path = $tempDir->getPath();

		$this->assertRegExp( '!/tmp/foo_\w*!', $path );
		$this->assertTrue( is_dir( $path ), "Failed to assert that $path is a directory" );

		unset( $tempDir );

		$this->assertFalse( is_dir( $path ), "Failed to assert that $path is deleted at the end" );

	}

	public function testMakeTempFile() {
		$tempDir = new TempDir( '/tmp', 'foo_' );
		$tempFile = $tempDir->makeTempFile( 'bar_' ,'baz' );

		$this->assertEquals( file_get_contents( $tempFile ), 'baz', 'Failed to assert that '
			. "$tempFile has the right contents" );

		$tempFile2 = $tempDir->makeTempFile( 'bar_' );

		$this->assertFileExists( $tempFile2 );

		unset( $tempDir );

		$this->assertFileNotExists( $tempFile );
		$this->assertFileNotExists( $tempFile2 );
	}
}
