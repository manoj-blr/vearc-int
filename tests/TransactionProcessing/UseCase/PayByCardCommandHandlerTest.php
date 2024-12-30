<?php

namespace Skaleet\Interview\Tests\TransactionProcessing\UseCase;

use Throwable;
use PHPUnit\Framework\TestCase;
use Skaleet\Interview\TransactionProcessing\Domain\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\UseCase\PayByCardCommand;
use Skaleet\Interview\TransactionProcessing\Domain\TransactionRepository;
use Skaleet\Interview\TransactionProcessing\UseCase\PayByCardCommandHandler;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\AccountDoesNotExistException;

class PayByCardCommandHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $accountRegistry = $this->createMock(AccountRegistry::class);

        $this->handler = new PayByCardCommandHandler($transactionRepository, $accountRegistry);
    }

    /**
     * @throws Throwable
     */
    public function test_account_does_not_exists_exception(): void
    {
        // Arrange: Create a command with invalid data (if applicable)
        $command = new PayByCardCommand(
            clientAccountNumber: "123789",
            merchantAccountNumber: "456789",
            amount: -10, // Invalid amount
            currency: "XYZ" // Unsupported currency
        );

        // Expectation: The method should throw a specific exception
        $this->expectException(AccountDoesNotExistException::class);
        
        // Act: Call the method that should throw the exception
        $this->handler->handle($command);

        // No assertions needed here as the test will fail if the exception is not thrown
    }
}
