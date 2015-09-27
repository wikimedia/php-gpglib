<?php

namespace GpgLib;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ShellGpgLib implements GpgLib, LoggerAwareInterface {
	/** @var string Location of the GnuPG binary. */
	protected $gpgBinary;

	/** @var TempDirFactory */
	protected $tempDirFactory;

	/** @var TempDir Temporary home directory. */
	protected $homeDir;

	/** @var callable Shell exec function.  */
	protected $exec;

	/** @var LoggerInterface */
	protected $logger;

	public function __construct( $gpgBinary, TempDirFactory $tempDirFactory ) {
		$this->gpgBinary = $gpgBinary;
		$this->tempDirFactory = $tempDirFactory;
		$this->exec = array( $this, 'exec' );
		$this->logger = new NullLogger();
	}

	/**
	 * Allows using an alternative function instead of exec(). Should have the same signature.
	 * @param callable $exec A callable that will receive an array of arguments (with element 0
	 *   being the gpg binary) and optionally a reference. The reference should be set to the
	 *   return value and the output of the call should be returned by the function.
	 *   See ShellGpgLib::exec() for an example.
	 */
	public function setExec( callable $exec ) {
		$this->exec = $exec;
	}

	/**
	 * Sets a logger instance on the object.
	 *
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validateKey( $key, $type = GpgLib::KEY_BOTH ) {
		$validKeyTypes = array( GpgLib::KEY_PUBLIC, GpgLib::KEY_PRIVATE, GpgLib::KEY_BOTH );
		if ( !in_array( $type, $validKeyTypes, true ) ) {
			throw new GpgLibException( "invalid type parameter to validateKey: $type" );
		}

		$this->resetHome();

		$keyFile = $this->homeDir->makeTempFile( 'key-', $key );
		$args = array( '--with-fingerprint', '--with-colons', $keyFile );

		$output = $this->call( $args, $retval );
		if ( $retval ) {
			$this->logger->warning( 'GPG with-fingerprint call returned error',
				array( 'retval' => $retval, 'output' => $output ) );
			return false;
		}

		$keyCount = 0;
		// see http://git.gnupg.org/cgi-bin/gitweb.cgi?p=gnupg.git;a=blob_plain;f=doc/DETAILS
		foreach ( explode( "\n", $output ) as $line ) {
			if ( !$line ) { // last, empty line?
				continue;
			}
			$keyData = explode( ':', $line );
			if ( count( $keyData ) < 10 ) {
				// stderr is piped to stdout for better logging so there can be all kinds of junk
				continue;
			}

			$recordType = $keyData[0];
			if ( $recordType === 'pub' || $recordType === 'sec' ) {
				$keyCount++;
				if ( $keyCount > 1 ) {
					$this->logger->info( 'Multiple keys in keyfile',
						array( 'output' => $output ) );
					return false;
				}

				if ( $recordType === 'pub' && $type === GpgLib::KEY_PRIVATE ) {
					$this->logger->info( 'Public key passed but private expected',
						array( 'output' => $output ) );
					return false;
				} elseif ( $recordType === 'sec' && $type === GpgLib::KEY_PUBLIC ) {
					$this->logger->info( 'Private key passed but public expected',
						array( 'output' => $output ) );
					return false;
				}
			}
		}

		if ( !$keyCount ) {
			$this->logger->info( 'No key found', array( 'output' => $output ) );
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function encrypt( $text, $publicKey, $signingKey = null ) {
		$this->resetHome();

		$publicKeyId = $this->addKey( $publicKey );
		if ( !$publicKeyId ) {
			return false;
		}
		$args = array( '--encrypt', '--armor', '--recipient', $publicKeyId );

		if ( $signingKey ) {
			$signingKeyId = $this->addKey( $signingKey );
			if ( !$signingKeyId ) {
				return false;
			}
			array_push( $args, '--sign', '--default-key', $signingKeyId );
		}

		$cipherFile = $this->homeDir->makeTempFile( 'output-' );
		array_push( $args, '--output', $cipherFile );

		$textFile = $this->homeDir->makeTempFile( 'cleartext-', $text );
		array_push( $args, $textFile );

		$output = $this->call( $args, $retval );
		if ( $retval ) {
			$this->logger->warning( 'GPG encrypt call returned error',
				array( 'retval' => $retval, 'output' => $output ) );
			return false;
		}

		$cipherText = file_get_contents( $cipherFile );
		if ( !$cipherText ) {
			$this->logger->warning( 'failed to load ciphertext file' );
			return false;
		}

		return $cipherText;
	}

	/**
	 * For now only used for testing
	 */
	public function decrypt( $text, $privateKey ) {
		$this->resetHome();

		$privateKeyId = $this->addKey( $privateKey );
		if ( !$privateKeyId ) {
			return false;
		}

		$cipherFile = $this->homeDir->makeTempFile( 'ciphertext-', $text );
		$clearFile = $this->homeDir->makeTempFile( 'cleartext-' );

		$args = array( '--decrypt', '--output', $clearFile, $cipherFile );
		$output = $this->call( $args, $retval );
		if ( $retval ) {
			$this->logger->warning( 'GPG encrypt call returned error',
				array( 'retval' => $retval, 'output' => $output ) );
			return false;
		}

		$clearText = file_get_contents( $clearFile );
		if ( !$clearText ) {
			$this->logger->warning( 'failed to load cleartext file' );
			return false;
		}

		return $clearText;
	}

	/**
	 * Creates a new temporary home directory. The previous one gets deleted.
	 */
	protected function resetHome() {
		$this->homeDir = $this->tempDirFactory->create();
	}

	/**
	 * @param string $key
	 * @return bool|string Key ID or false on failure
	 */
	protected function addKey( $key ) {
		$keyFile = $this->homeDir->makeTempFile( 'key-', $key );

		$output = $this->call( array( '--import', $keyFile ), $retval );

		if ( $retval ) {
			$this->logger->warning( 'Failed to add key',
				array( 'retval' => $retval, 'output' => $output ) );
			return false;
		}
		preg_match( '/^gpg: key ([A-Z0-9]+):/m', $output, $matches );
		if ( !$matches ) {
			$this->logger->warning( 'Added key but could not parse output',
				array( 'output' => $output ) );
			return false;
		}

		return $matches[1];
	}

	/**
	 * Call the GPG binary. Arguments can be packed in an array or passed unpacked.
	 * @param string[] $arguments Array of arguments
	 * @param string $retval Will be set to the return value
	 * @return string Standard output
	 */
	protected function call( $arguments, &$retval = null ) {
		if ( !is_array( $arguments ) ) {
			$arguments = func_get_args();
		}

		array_unshift( $arguments, $this->gpgBinary, '--no-options', '--homedir',
			$this->homeDir->getPath(), '--batch', '--yes', '--trust-model', 'always' );

		return call_user_func_array( $this->exec, array( $arguments, &$retval ) );
	}

	/**
	 * @param array $arguments Arguments, starting with the binary itself.
	 * @param int $retval Will be set to the return value.
	 * @return string Standard output.
	 */
	protected function exec( array $arguments, &$retval = null ) {
		if ( !$arguments ) {
			throw new \InvalidArgumentException( 'Nothing to execute' );
		}

		$command = escapeshellcmd( array_shift( $arguments ) );
		foreach ( $arguments as $argument ) {
			$command .= ' ' . escapeshellarg( $argument );
		}
		$command .= ' 2>&1';

		exec( $command, $lines, $retval );
		$this->logger->debug( 'exec called',
			array( 'command' => $command, 'lines' => $lines, 'retval' => $retval ) );

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}
}
