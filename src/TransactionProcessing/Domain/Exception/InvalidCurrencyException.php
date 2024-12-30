<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Exception;


class InvalidCurrencyException extends \Exception
{
    public function __construct(string $clientAccountCurrency, string $merchantAccountCurrency)
    {
        parent::__construct("Mismatch in transaction currency. Client account currency: " . $clientAccountCurrency. " whereas Merchant account currency: " . $merchantAccountCurrency);
    }

}