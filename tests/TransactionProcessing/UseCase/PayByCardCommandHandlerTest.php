<?php

namespace Skaleet\Interview\Tests\TransactionProcessing\UseCase;

use PHPUnit\Framework\TestCase;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\AccountDoesNotExistException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Infrastructure\ExistingAccounts;
use Skaleet\Interview\TransactionProcessing\Infrastructure\InMemoryDatabase;
use Skaleet\Interview\TransactionProcessing\UseCase\PayByCardCommand;
use Skaleet\Interview\TransactionProcessing\UseCase\PayByCardCommandHandler;
use Throwable;

class PayByCardCommandHandlerTest extends TestCase
{
    public PayByCardCommandHandler $handler;
    public InMemoryDatabase $inMemoryDatabase;

    /**
     * @throws Throwable
     */
    public function test_account_does_not_exists_exception(): void
    {

        $command = new PayByCardCommand(clientAccountNumber: "invalid-client-account", merchantAccountNumber: "456789", amount: 10,
            currency: "EUR");

        $this->expectException(AccountDoesNotExistException::class);
        $this->handler->handle($command);
        // No assertions needed here as the test will fail if the exception is not thrown
    }

    public function test_invalid_amount_exception(): void
    {
        $command = new PayByCardCommand(clientAccountNumber: "123456", merchantAccountNumber: "456789", amount: -10,
            currency: "EUR");

        $this->expectException(\InvalidArgumentException::class);
        $this->handler->handle($command);
        // No assertions needed here as the test will fail if the exception is not thrown
    }

    public function test_invalid_currency_exception(): void
    {
        $command = new PayByCardCommand(clientAccountNumber: "123456", merchantAccountNumber: "456789", amount: 10, currency: "USD");
        $this->expectException(\InvalidArgumentException::class);
        $this->handler->handle($command);
        // No assertions needed here as the test will fail if the exception is not thrown
    }

    public function test_success_transaction(): void
    {

        $command = new PayByCardCommand(clientAccountNumber: "123456", merchantAccountNumber: "456789", amount: 10, currency: "EUR");


        $this->expectOutputString('Transaction successfully completed.');
        $successMessage = $this->handler->handle($command);
        echo $successMessage;

    }

    /*public function dataProvider(): array
    {

        return [[new Account(ExistingAccounts::BANK_EUR, new Amount(-2150_00, "EUR")), new Account(ExistingAccounts::CLIENT_EUR, new Amount(150_00, "EUR")), new Account(ExistingAccounts::MERCHANT_EUR, new Amount(2000_00, "EUR")), new Account(ExistingAccounts::BANK_USD, new Amount(-1825_00, "USD")), new Account(ExistingAccounts::CLIENT_USD, new Amount(75_00, "USD")), new Account(ExistingAccounts::MERCHANT_USD, new Amount(1750_00, "USD")),], [new TransactionLog("abcd", DateTimeImmutable::createFromFormat("d/m/Y H:i:s", "30/01/2023 11:14:42"), [new AccountingEntry(ExistingAccounts::BANK_EUR, new Amount(-150_00, "EUR"), new Amount(-150_00, "EUR")), new AccountingEntry(ExistingAccounts::CLIENT_EUR, new Amount(150_00, "EUR"), new Amount(150_00, "EUR")),]), new TransactionLog("efgh", DateTimeImmutable::createFromFormat("d/m/Y H:i:s", "30/01/2023 13:37:42"), [new AccountingEntry(ExistingAccounts::BANK_EUR, new Amount(-2000_00, "EUR"), new Amount(-2150_00, "EUR")), new AccountingEntry(ExistingAccounts::MERCHANT_EUR, new Amount(2000_00, "EUR"), new Amount(2000_00, "EUR")),]),

            new TransactionLog("dcba", DateTimeImmutable::createFromFormat("d/m/Y H:i:s", "30/01/2023 14:13:37"), [new AccountingEntry(ExistingAccounts::BANK_USD, new Amount(-75_00, "USD"), new Amount(-75_00, "USD")), new AccountingEntry(ExistingAccounts::CLIENT_USD, new Amount(75_00, "USD"), new Amount(75_00, "USD")),]), new TransactionLog("hgfe", DateTimeImmutable::createFromFormat("d/m/Y H:i:s", "30/01/2023 16:32:48"), [new AccountingEntry(ExistingAccounts::BANK_USD, new Amount(-1750_00, "USD"), new Amount(-1825_00, "USD")), new AccountingEntry(ExistingAccounts::MERCHANT_USD, new Amount(1750_00, "USD"), new Amount(1750_00, "USD")),]),]];


    }*/

    protected function setUp(): void
    {
        // Set up dummy data for the InMemoryDatabase
        $this->inMemoryDatabase = new InMemoryDatabase([new Account(ExistingAccounts::BANK_EUR, new Amount(2150_00, "EUR")), new Account(ExistingAccounts::CLIENT_EUR, new Amount(150_00, "EUR")), new Account(ExistingAccounts::MERCHANT_EUR, new Amount(2000_00, "EUR")), new Account(ExistingAccounts::CLIENT_USD, new Amount(100_00, "USD")),], [] // No transaction logs initially
        );

        $this->handler = new PayByCardCommandHandler($this->inMemoryDatabase, $this->inMemoryDatabase);
    }
}
