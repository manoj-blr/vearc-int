<?php

namespace Skaleet\Interview\TransactionProcessing\Infrastructure;

use Skaleet\Interview\TransactionProcessing\Domain\Exception\AccountDoesNotExistException;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\InvalidDatabaseException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\TransactionLog;

class DB
{
    const DB_IN_MEMORY = 'in-memory';    //in-memory, persistent,  mysql, etc
    const DB_MYSQL = 'mysql';
    const DB_PERSISTENT = 'persistent';
    private ?string $database = null;
    private ?TransactionLog $transactionLog = null;

    public function __construct(TransactionLog $transactionLog)
    {
//        $this->database = 'in-memory';  // could be fetched from configuration. ex - from .env file.
         $this->database = 'persistent';  // could be fetched from configuration. ex - from .env file.
        $this->transactionLog = $transactionLog;
    }

    /**
     * @throws AccountDoesNotExistException
     * @throws InvalidDatabaseException
     */
    public function saveTrasaction(): void
    {
        switch ($this->database) {
            case self::DB_IN_MEMORY:
                $this->saveInMemoryTransaction();
                break;
            case self::DB_PERSISTENT:
                $this->savePersistentTransaction();
                break;
            case self::DB_MYSQL:
                $this->saveMySQLTransaction();
                break;
            default:
                throw new InvalidDatabaseException($this->database);
        }
    }

    /**
     * Save transactions to In Memory
     * @throws AccountDoesNotExistException
     */
    private function saveInMemoryTransaction(): void
    {
        $data = unserialize(file_get_contents('db'));
        $inMemory = new InMemoryDatabase($data->getAccounts(), $data->getTransactions());
        $inMemory->add($this->transactionLog);
    }

    /**
     * Save transactions to Persistent DB
     * @throws AccountDoesNotExistException
     */
    private function savePersistentTransaction(): void
    {
        $data = unserialize(file_get_contents('db'));
        $persistent = new PersistentDatabase($data->getAccounts(), $data->getTransactions());
        $persistent->add($this->transactionLog);
    }

    private function saveMySQLTransaction()
    {
        // mysql implementation
    }
}
