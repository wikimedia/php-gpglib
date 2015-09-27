<?php

namespace GpgLib;

abstract class GpgLibFactory {
	/**
	 * @return GpgLib
	 */
	abstract public function create();
}
