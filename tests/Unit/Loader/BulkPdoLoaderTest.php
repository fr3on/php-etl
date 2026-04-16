<?php

declare(strict_types=1);

namespace Fr3on\Etl\Tests\Unit\Loader;

use PHPUnit\Framework\TestCase;
use Fr3on\Etl\Loader\BulkPdoLoader;
use PDO;

class BulkPdoLoaderTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec('CREATE TABLE transactions (tx_id TEXT, amount REAL, status TEXT, created_at TEXT)');
    }

    public function testBulkInsert(): void
    {
        $loader = new BulkPdoLoader($this->pdo, 'transactions', batchSize: 2);
        
        $loader->write(['tx_id' => 'TX001', 'amount' => 10.0, 'status' => 'SUCCESS', 'created_at' => '2024-01-01']);
        
        // Should not be inserted yet (batch size is 2)
        $count = $this->pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
        $this->assertEquals(0, $count);

        $loader->write(['tx_id' => 'TX002', 'amount' => 20.0, 'status' => 'SUCCESS', 'created_at' => '2024-01-01']);
        
        // Should be inserted now
        $count = $this->pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
        $this->assertEquals(2, $count);
    }

    public function testFlushInsertsRemaining(): void
    {
        $loader = new BulkPdoLoader($this->pdo, 'transactions', batchSize: 10);
        $loader->write(['tx_id' => 'TX001', 'amount' => 10.0, 'status' => 'SUCCESS', 'created_at' => '2024-01-01']);
        
        $count = $this->pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
        $this->assertEquals(0, $count);

        $loader->flush();

        $count = $this->pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
        $this->assertEquals(1, $count);
    }
}
