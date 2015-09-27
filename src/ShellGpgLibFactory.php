<?php

namespace GpgLib;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ShellGpgLibFactory extends GpgLibFactory implements LoggerAwareInterface {
	/** @var TempDirFactory */
	protected $tempDirFactory;

	/** @var string Path to the GPG binary */
	protected $gpgBinary;

	/** @var LoggerInterface */
	public $logger;

	public function __construct() {
		$this->tempDirFactory = new TempDirFactory();
		$this->gpgBinary = 'gpg';
		$this->logger = new NullLogger();
	}

	/**
	 * Use a TempDirFactory with non-default parameters
	 * @param TempDirFactory $tempDirFactory
	 */
	public function setTempDirFactory( TempDirFactory $tempDirFactory ) {
		$this->tempDirFactory = $tempDirFactory;
	}

	/**
	 * @param string $gpgBinary Path to the GPG binary
	 */
	public function setGpgBinary( $gpgBinary ) {
		$this->gpgBinary = $gpgBinary;
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
	 * @return ShellGpgLib
	 */
	public function create() {
		return new ShellGpgLib( $this->gpgBinary, $this->tempDirFactory );
	}
}
