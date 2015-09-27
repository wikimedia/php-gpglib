<?php

namespace GpgLib\Test;


use GpgLib\GpgLib;
use GpgLib\ShellGpgLib;
use GpgLib\TempDirFactory;

class ShellGpgLibTest extends \PHPUnit_Framework_TestCase {
	/** @var  ShellGpgLib */
	public $gpgLib;

	public function setUp() {
		$this->gpgLib = new ShellGpgLib( 'gpg', new TempDirFactory() );
//		$this->gpgLib->setLogger( new \fool\echolog\Echolog() );
	}

	/**
	 * @dataProvider provideValidateKey
	 */
	public function testValidateKey( $key, $type, $isValid ) {
		$key = $this->getContents( $key );

		$res = $this->gpgLib->validateKey( $key, $type );
		$this->assertEquals( $isValid, $res );
	}

	public function provideValidateKey() {
		return array(
			array( 'public.asc', GpgLib::KEY_BOTH, true ),
			array( 'private.asc', GpgLib::KEY_BOTH, true ),
			array( 'public.asc', GpgLib::KEY_PUBLIC, true ),
			array( 'private.asc', GpgLib::KEY_PRIVATE, true ),
			array( 'public.asc', GpgLib::KEY_PRIVATE, false ),
			array( 'private.asc', GpgLib::KEY_PUBLIC, false  ),
			array( 'cleartext.txt', GpgLib::KEY_BOTH, false  ),
		);
	}

	/**
	 * @dataProvider provideEncrypt
	 */
	public function testEncrypt( $text, $publicKey, $signingKey, $privateKey ) {
		$text = $this->getContents( $text );
		$publicKey = $this->getContents( $publicKey );
		$privateKey = $this->getContents( $privateKey );
		$signingKey = $this->getContents( $signingKey );

		$res = $this->gpgLib->encrypt( $text, $publicKey, $signingKey );
		$res2 = $this->gpgLib->decrypt( $res, $privateKey );
		$this->assertEquals( $text, $res2 );
	}

	public function provideEncrypt() {
		return array(
			array( 'cleartext.txt', 'public.asc', null, 'private.asc' ),
		);
	}

	/**
	 * Returns the content of the given getContents from the data directory
	 * @param string|null $fileName
	 * @return string|null
	 */
	protected function getContents( $fileName ) {
		if ( !$fileName ) {
			return $fileName;
		}
		return file_get_contents( __DIR__ . "/data/$fileName" );
	}
}
