<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Exception;

class ClientSufficientBalanceException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Client does not have enough funds to process the transaction");
    }
}
