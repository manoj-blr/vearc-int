<?php

namespace Skaleet\Interview\TransactionProcessing\UseCase;

use DateTimeImmutable;
use Skaleet\Interview\TransactionProcessing\Domain\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\AccountDoesNotExistException;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\ClientSufficientBalanceException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\AccountingEntry;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Model\TransactionLog;
use Skaleet\Interview\TransactionProcessing\Domain\Service\CurrencyFormatter;
use Skaleet\Interview\TransactionProcessing\Domain\TransactionRepository;
use Skaleet\Interview\TransactionProcessing\UseCase\Validator\PayByCardValidator;

class PayByCardCommandHandler
{
    public function __construct(private TransactionRepository $transactionRepository, private AccountRegistry $accountRegistry)
    {

    }


    /**
     * @throws AccountDoesNotExistException
     * @throws ClientSufficientBalanceException
     */
    public function handle(PayByCardCommand $command): void
    {

        $payByCardValidator = new PayByCardValidator($command, $this->accountRegistry);
        $transactionDetails = $payByCardValidator->validate();

        $amount = CurrencyFormatter::toBase($command->amount, $command->currency);

        $clientBalance = $transactionDetails['clientAccount']->balance->value;
        $merchantBalance = $transactionDetails['merchantAccount']->balance->value;
        $clientNewBalance = $clientBalance - $amount;
        $merchantNewBalance = $merchantBalance + $amount;

        $transactionId = uniqid();
        $transactionDate = (new DateTimeImmutable())->createFromFormat('d/m/Y H:i:s', date('d/m/Y H:i:s'));
        $transactions = new TransactionLog($transactionId, $transactionDate, [new AccountingEntry($command->clientAccountNumber, new Amount(-$amount, $command->currency), new Amount($clientNewBalance, $command->currency)), new AccountingEntry($command->merchantAccountNumber, new Amount($amount, $command->currency), new Amount($merchantNewBalance, $command->currency)),]);

        $this->transactionRepository->add($transactions);

        // echoing for simplicity. on real world app, this would be returning the transaction object with details
        // such as client_id, merchant_ids, transaction amount, transaction status and other details.
        echo 'Transaction successfully completed.';

    }
}
