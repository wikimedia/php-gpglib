<?php

namespace GpgLib\Test;


use GpgLib\ShellGpgLibFactory;

class ShellGpgLibFactoryTest extends \PHPUnit_Framework_TestCase {
	public function testCreate() {
		$factory = new ShellGpgLibFactory();
		$gpgLib = $factory->create();

		$this->assertInstanceOf( '\GpgLib\GpgLib', $gpgLib );
	}
}
