<?php

namespace Skaleet\Interview\TransactionProcessing\UseCase\Validator;


use Skaleet\Interview\TransactionProcessing\Domain\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\AccountDoesNotExistException;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\ClientSufficientBalanceException;
use Skaleet\Interview\TransactionProcessing\Domain\Service\CurrencyFormatter;
use Skaleet\Interview\TransactionProcessing\UseCase\PayByCardCommand;

class PayByCardValidator
{

    private $transactionDetails = [];

    public function __construct(private PayByCardCommand $command, private AccountRegistry $accountRegistry)
    {
    }

    /**
     * Validate before processing the payment. Validations are performed in the order.
     * Each validations throw exceptions on failure. Thus early existing the code flow.
     * The handler/controller gets executed only if the validations pass.
     * @throws AccountDoesNotExistException
     * @throws ClientSufficientBalanceException
     */
    public function validate(): array
    {
        $this->validateAccountsExist()
            ->validateAmount()
            ->validateCurrency()
            ->validateClientSufficientBalance();
        return $this->transactionDetails;

    }

    /**
     * Validate that the client has sufficient balance for the transaction.
     * Throw an exception if the client does not have sufficient balance.
     * @throws ClientSufficientBalanceException
     */
    private function validateClientSufficientBalance(): self|ClientSufficientBalanceException
    {
        $amount = CurrencyFormatter::toBase($this->command->amount, $this->transactionDetails['clientAccount']->balance->currency);
        $clientAccountBalance = $this->transactionDetails['clientAccount']->balance->value;

        if ($clientAccountBalance < $amount) {
            throw new ClientSufficientBalanceException();
        }

        return $this;
    }

    /**
     * Validate that the currency matches the currency of the client and merchant accounts.
     * Throw an exception if the currency does not match.
     */
    private function validateCurrency(): self|\InvalidArgumentException
    {


        $currencies = [$this->command->currency, $this->transactionDetails['clientAccount']->balance->currency, $this->transactionDetails['merchantAccount']->balance->currency];

        if (count(array_unique($currencies)) > 1) {
            throw new \InvalidArgumentException("Mismatch in transaction currency.");
        }

        return $this;
    }

    /**
     * Validate that the amount is greater than zero.
     * Throw an exception if the amount is not greater than zero.
     */
    private function validateAmount(): self|\InvalidArgumentException
    {
        if ($this->command->amount <= 0) {
            throw new \InvalidArgumentException("Amount must be greater than zero.");
        }

        return $this;
    }

    /**
     * Validate whether the client and merchant account exists or not.
     * If the account exists add client and merchant account details to transactionDetails array
     * Throw an exception if either the client or merchant account does not exist.
     * @throws AccountDoesNotExistException
     */
    private function validateAccountsExist(): self|AccountDoesNotExistException
    {
        $clientAccount = $this->accountRegistry->loadByNumber($this->command->clientAccountNumber);
        $merchantAccount = $this->accountRegistry->loadByNumber($this->command->merchantAccountNumber);
        if ($clientAccount === null) {
            throw new AccountDoesNotExistException($this->command->clientAccountNumber);
        }

        if ($merchantAccount === null) {
            throw new AccountDoesNotExistException($this->command->merchantAccountNumber);
        }

        $this->transactionDetails['clientAccount'] = $clientAccount;
        $this->transactionDetails['merchantAccount'] = $merchantAccount;

        return $this;
    }
}
