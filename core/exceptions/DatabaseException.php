<?php

namespace Fckin\core\exceptions;

use Exception;
use Throwable;

class DatabaseException extends Exception
{
    protected $query;

    public function __construct($message = "", $code = 0, $query = "", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message} (Query: {$this->query})\n";
    }
}
