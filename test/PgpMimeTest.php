<?php

namespace GpgLib\Test;

use GpgLib\PgpMime;

class PgpMimeTest extends \PHPUnit_Framework_TestCase {
	public function testEncrypt() {
		$gpgLib = $this->getMock( 'GpgLib\GpgLib' );
		$pgpMime = $this->getMockBuilder( 'GpgLib\PgpMime' )
			->setMethods( array( 'getBoundary' ) )
			->setConstructorArgs( array( $gpgLib ) )
			->getMock();

		// example from RFC 3156
		$headers = array(
			'From' => 'Michael Elkins <elkins@aero.org>',
			'To' => 'Michael Elkins <elkins@aero.org>',
			'Mime-Version' => '1.0',
			'Content-Type' => 'text/plain; charset=iso-8859-1',
			'Content-Transfer-Encoding' => 'quoted-printable',
		);
		$body = <<<EMAIL
=A1Hola!

Did you know that talking to yourself is a sign of senility?

It's generally a good idea to encode lines that begin with
From=20because some mail transport agents will insert a greater-
than (>) sign, thus invalidating the signature.

Also, in some cases it might be desirable to encode any   =20
trailing whitespace that occurs on lines in order to ensure  =20
that the message signature is not invalidated when passing =20
a gateway that modifies such whitespace (like BITNET). =20

me
EMAIL;

		$expectedHeaders = array(
			'From' => 'Michael Elkins <elkins@aero.org>',
			'To' => 'Michael Elkins <elkins@aero.org>',
			'Mime-Version' => '1.0',
			'Content-Type' => 'multipart/encrypted; boundary="foo"; protocol="application/pgp-encrypted"',
		);
		$expectedBody = <<<EMAIL
This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)

--foo
Content-Type: application/pgp-encrypted
Content-Description: PGP/MIME version identification

Version: 1

--foo
Content-Type: application/octet-stream; name="encrypted.asc"
Content-Description: OpenPGP encrypted message
Content-Disposition: inline; filename="encrypted.asc"

<Encrypted text>

--foo--
EMAIL;
		$expectedCleartext = <<<TEXT
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: quoted-printable

=A1Hola!

Did you know that talking to yourself is a sign of senility?

It's generally a good idea to encode lines that begin with
From=20because some mail transport agents will insert a greater-
than (>) sign, thus invalidating the signature.

Also, in some cases it might be desirable to encode any   =20
trailing whitespace that occurs on lines in order to ensure  =20
that the message signature is not invalidated when passing =20
a gateway that modifies such whitespace (like BITNET). =20

me
TEXT;

		$gpgLib->expects( $this->once() )->method( 'encrypt' )
			->with( $this->equalTo( $expectedCleartext ) )->willReturn( '<Encrypted text>' );
		$pgpMime->method( 'getBoundary' )->willReturn( 'foo' );
		list( $headers, $body ) = $pgpMime->encrypt( $headers, $body, '<mock key>' );
		$this->assertEquals( $expectedHeaders, $headers );
		$this->assertEquals( $expectedBody, $body );
	}

	public function testEncryptError() {
		$gpgLib = $this->getMock( 'GpgLib\GpgLib' );
		$pgpMime = new PgpMime( $gpgLib );

		// example from RFC 3156
		$headers = array(
			'From' => 'Michael Elkins <elkins@aero.org>',
			'To' => 'Michael Elkins <elkins@aero.org>',
			'Mime-Version' => '1.0',
			'Content-Type' => 'text/plain; charset=iso-8859-1',
			'Content-Transfer-Encoding' => 'quoted-printable',
		);
		$body = <<<EMAIL
=A1Hola!

Did you know that talking to yourself is a sign of senility?

It's generally a good idea to encode lines that begin with
From=20because some mail transport agents will insert a greater-
than (>) sign, thus invalidating the signature.

Also, in some cases it might be desirable to encode any   =20
trailing whitespace that occurs on lines in order to ensure  =20
that the message signature is not invalidated when passing =20
a gateway that modifies such whitespace (like BITNET). =20

me
EMAIL;

		$gpgLib->expects( $this->once() )->method( 'encrypt' )->willReturn( false );
		list( $headers, $body ) = $pgpMime->encrypt( $headers, $body, '<mock key>' );
		$this->assertEquals( false, $headers );
		$this->assertEquals( false, $body );
	}
}
