<?php

namespace GpgLib;

interface GpgLib {
	const KEY_PUBLIC = 'public';
	const KEY_PRIVATE = 'private';
	const KEY_BOTH = 'both';

	/**
	 * Validates a GPG key.
	 * @param string $key
	 * @param string $type One of the GpgLib::KEY_* constants
	 * @return boolean
	 * @throws GpgLibException When validation could not be attempted.
	 */
	public function validateKey( $key, $type = self::KEY_BOTH );

	/**
	 * Encrypts the given text with the given key.
	 * @param string $text The cleartext
	 * @param string $publicKey Public key of the receiver for encryption
	 * @param string $signingKey Private key of the sender for signing
	 * @return bool|string The cyphertext, or false on failure
	 * @throws GpgLibException When encryption failed due to errors other than the key(s) being
	 *   invalid.
	 */
	public function encrypt( $text, $publicKey, $signingKey = null );
}
