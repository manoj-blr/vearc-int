<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Exception;

class InvalidDatabaseException extends \Exception
{
    public function __construct(string $database)
    {
        parent::__construct("Invalid database: $database");
    }
}
