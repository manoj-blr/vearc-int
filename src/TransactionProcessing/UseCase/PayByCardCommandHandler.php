<?php

namespace Skaleet\Interview\TransactionProcessing\UseCase;

use DateTimeImmutable;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\UseCase\CurrencyFormatter;
use Skaleet\Interview\TransactionProcessing\Domain\Model\TransactionLog;
use Skaleet\Interview\TransactionProcessing\Domain\Model\AccountingEntry;
use Skaleet\Interview\TransactionProcessing\Domain\TransactionRepository;
use Skaleet\Interview\TransactionProcessing\Infrastructure\DB;
use Skaleet\Interview\TransactionProcessing\Infrastructure\InMemoryDatabase;
use Skaleet\Interview\TransactionProcessing\Infrastructure\PersistentDatabase;
use Skaleet\Interview\TransactionProcessing\UseCase\Validator\PayByCardValidator;

class PayByCardCommandHandler
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private AccountRegistry       $accountRegistry,
    ) {}


    public function handle(PayByCardCommand $command): void
    {

        $payByCardValidator = new PayByCardValidator($command, $this->accountRegistry);
        $payByCardValidator->validate();

        $currency = $this->accountRegistry->loadByNumber($command->clientAccountNumber)->balance->currency;
        $amount = CurrencyFormatter::toBase($command->amount, $currency);
        $clientNewBalance = $this->accountRegistry->loadByNumber($command->clientAccountNumber)->balance->value - $amount;
        $merchantNewBalance = $this->accountRegistry->loadByNumber($command->merchantAccountNumber)->balance->value + $amount;

        $transactionId = uniqid();
        $transactionDate = (new DateTimeImmutable())->createFromFormat('d/m/Y H:i:s', date('d/m/Y H:i:s'));
        $transactions = new TransactionLog($transactionId, $transactionDate, [
            new AccountingEntry($command->clientAccountNumber, new Amount(-$amount, $command->currency), new Amount($clientNewBalance, $command->currency)),
            new AccountingEntry($command->merchantAccountNumber, new Amount($amount, $command->currency), new Amount($merchantNewBalance, $command->currency)),
        ]);

        (new DB($transactions))->saveTrasaction();

        // echoing for simplicity. on real world app, this would be returning the transaction object with details 
        // such as client_id, merchant_ids, transaction amount, transaction status and other details.
        echo 'Transaction successfully completed.';
    }
}
