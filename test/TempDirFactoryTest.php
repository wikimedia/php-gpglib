<?php

namespace GpgLib\Test;


use GpgLib\TempDirFactory;

class TempDirFactoryTest extends \PHPUnit_Framework_TestCase {
	public function testCreate() {
		$factory = new TempDirFactory();
		$tempDir = $factory->create();

		$this->assertInstanceOf( '\GpgLib\TempDir', $tempDir );
	}
}
