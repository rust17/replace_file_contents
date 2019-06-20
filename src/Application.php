<?php

namespace Circle33\ReplaceFileContents;

use Circle33\ReplaceFileContents\Commands\TransformAbsolutePath;
use Symfony\Component\Console\Application as BasicApplication;

class Application extends BasicApplication
{
	public function __construct()
	{
		parent::__construct();

		$this->add(new TransformAbsolutePath());
	}
}