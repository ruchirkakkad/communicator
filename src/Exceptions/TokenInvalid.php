<?php

namespace Picahoo\Communicator\Exceptions;

class TokenInvalid extends \Exception
{
	protected $message;
	protected $code;
    protected $file;
    protected $line;

	public function __construct($message)
	{
		$this->message = $message;
    }
}