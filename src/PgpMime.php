<?php

namespace GpgLib;

/**
 * Encrypts an email according to PGP/MIME ({@link https://tools.ietf.org/html/rfc3156}).
 * @package GpgLib
 */
class PgpMime {
	/** @var GpgLib */
	protected $gpgLib;

	/**
	 * @param GpgLib $gpgLib
	 */
	public function __construct( GpgLib $gpgLib ) {
		$this->gpgLib = $gpgLib;
	}

	/**
	 * Encrypts an email according to PGP/MIME. The email must be MIME-encoded already (with
	 * the PEAR Mail_mime class, for example).
	 * @param array $headers Email headers in "Header-Name" => "value" format
	 * @param string $body Email body
	 * @param string $publicKey Public key of the receiver for encryption
	 * @return array [$newHeaders, $newBody]
	 */
	public function encrypt( array $headers, $body, $publicKey) {
		$preparedBody = '';
		foreach ( $headers as $name => $value ) {
			if ( strpos( $name, 'Content-' ) === 0 ) {
				$preparedBody .= "$name: $value\n";
				unset( $headers[$name] );
			}
		}
		$preparedBody .= "\n$body";

		$encryptedBody = $this->gpgLib->encrypt( $preparedBody, $publicKey);

		$boundary = $this->getBoundary( $encryptedBody );

		$headers['Content-Type'] = 'multipart/encrypted; boundary="' . $boundary . '"; '
			. 'protocol="application/pgp-encrypted"';

		$finalBody = <<<EMAIL
This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)

--$boundary
Content-Type: application/pgp-encrypted
Content-Description: PGP/MIME version identification

Version: 1

--$boundary
Content-Type: application/octet-stream; name="encrypted.asc"
Content-Description: OpenPGP encrypted message
Content-Disposition: inline; filename="encrypted.asc"

$encryptedBody

--$boundary--
EMAIL;

		return array( $headers, $finalBody );
	}

	/**
	 * Generate a MIME multipart boundary
	 * @param string $textToBound Text of the email where the boundary needs to be inserted
	 * @return string
	 */
	protected function getBoundary( $textToBound ) {
		$boundary = md5( 'php-gpglib' . uniqid( mt_rand(), true ) );
		while ( strpos( $textToBound, $boundary ) !== false ) {
			$boundary .= '=_';
		}
		return $boundary;
	}
}
