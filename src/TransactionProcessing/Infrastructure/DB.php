<?php

namespace Skaleet\Interview\TransactionProcessing\Infrastructure;

use Skaleet\Interview\TransactionProcessing\Domain\Model\TransactionLog;
use Skaleet\Interview\TransactionProcessing\Domain\Exception\InvalidDatabaseException;

class DB
{
    private $database = null;    //in-memory, persistent,  mysql, etc
    private $transactionLog = null;
    const DB_IN_MEMORY = 'in-memory';
    const DB_MYSQL = 'mysql';
    const DB_PERSISTENT = 'persistent';

    public function __construct(TransactionLog $transactionLog)
    {
        // $this->database = 'in-memory';  // could be fetched from configuration. ex - from .env file.
        $this->database = 'persistent';  // could be fetched from configuration. ex - from .env file.
        $this->transactionLog = $transactionLog;
    }



    public function saveTrasaction()
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

    private function saveInMemoryTransaction()
    {
        try {
            $data = unserialize(file_get_contents('db'));
            $inMemory = new InMemoryDatabase($data->getAccounts(), $data->getTransactions());
            $inMemory->add($this->transactionLog);
        } catch (\Exception $ex) {
            throw $ex->getMessage();
        }
    }

    private function savePersistentTransaction()
    {
        try {
            $data = unserialize(file_get_contents('db'));
            $persistent = new PersistentDatabase($data->getAccounts(), $data->getTransactions());
            $persistent->add($this->transactionLog);
        } catch (\Exception $ex) {
            throw $ex->getMessage();
        }
    }

    private function saveMySQLTransaction()
    {
        // mysql implementation
    }
}
