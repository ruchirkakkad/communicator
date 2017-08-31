<?php

namespace Picahoo\Communicator\Facades;

use Illuminate\Support\Facades\Facade;

class Communicator extends Facade
{

	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'communicator';
	}

}