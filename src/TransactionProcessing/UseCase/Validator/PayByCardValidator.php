<?php

namespace Skaleet\Interview\TransactionProcessing\UseCase\Validator;

use Skaleet\Interview\TransactionProcessing\Domain\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\UseCase\PayByCardCommand;
use Skaleet\Interview\TransactionProcessing\UseCase\CurrencyFormatter;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\AccountDoesNotExistException;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\ClientSufficientBalanceException;

class PayByCardValidator
{
    public function __construct(
        private PayByCardCommand $command,
        private AccountRegistry  $accountRegistry,
    ) {}

    public function validate()
    {
        $this->validateAccountsExist()
            ->validateAmount()
            ->validateCurrency()
            ->validateClientSufficientBalance();
    }

    /**
     * Validate whether the client and merchant account exists or not.
     * Throw an exception if either the client or merchant account does not exist.
     */
    private function validateAccountsExist(): self|AccountDoesNotExistException
    {
        if ($this->accountRegistry->loadByNumber($this->command->clientAccountNumber) === null) {
            throw new AccountDoesNotExistException($this->command->clientAccountNumber);
        }

        if ($this->accountRegistry->loadByNumber($this->command->merchantAccountNumber) === null) {
            throw new AccountDoesNotExistException($this->command->merchantAccountNumber);
        }

        return $this;
    }

    private function validateAmount(): self|\InvalidArgumentException
    {
        if ($this->command->amount <= 0) {
            throw new \InvalidArgumentException("Amount must be greater than zero.");
        }

        return $this;
    }

    private function validateCurrency(): self|\InvalidArgumentException
    {
        $currencies = [
            $this->command->currency,
            $this->accountRegistry->loadByNumber($this->command->clientAccountNumber)->balance->currency,
            $this->accountRegistry->loadByNumber($this->command->merchantAccountNumber)->balance->currency
        ];

        if (count(array_unique($currencies)) > 1) {
            throw new \InvalidArgumentException("Mismatch in transaction currency.");
        }

        return $this;
    }

    private function validateClientSufficientBalance(): self|ClientSufficientBalanceException
    {
        $amount = CurrencyFormatter::toBase($this->command->amount, $this->accountRegistry->loadByNumber($this->command->clientAccountNumber)->balance->currency);
        $clientAccountBalance = $this->accountRegistry->loadByNumber($this->command->clientAccountNumber)->balance->value;

        if ($clientAccountBalance < $amount) {
            throw new ClientSufficientBalanceException();
        }

        return $this;
    }
}
