<?php

namespace Circle33\TransformAbsolutePath;

use Circle33\TransformAbsolutePath\Commands\CorrectToAbsolutePath;
use Symfony\Component\Console\Application as BasicApplication;

class Application extends BasicApplication
{
	public function __construct()
	{
		parent::__construct();

		$this->add(new CorrectToAbsolutePath());
	}
}