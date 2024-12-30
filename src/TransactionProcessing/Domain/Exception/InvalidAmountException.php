<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Exception;

class InvalidAmountException  extends \Exception
{
    public function __construct(string $amount)
    {
        parent::__construct("Amount must be a positive number. Passed amount: $amount is invalid");
    }
}
