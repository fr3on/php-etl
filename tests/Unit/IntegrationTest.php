<?php

declare(strict_types=1);

namespace Fr3on\Etl\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Fr3on\Etl\Pipeline;
use Fr3on\Etl\Extractor\CsvExtractor;
use Fr3on\Etl\Loader\BulkPdoLoader;
use Fr3on\Etl\Tests\FakeDataGenerator;
use PDO;

class IntegrationTest extends TestCase
{
    private string $csvFile;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->csvFile = tempnam(sys_get_temp_dir(), 'etl_integ_');
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec('CREATE TABLE clean_ledger (id TEXT, amount REAL)');
        
        $data = [
            ['tx_id' => 'TX001', 'amount' => '100.50', 'status' => 'SUCCESS'],
            ['tx_id' => 'TX002', 'amount' => '200.00', 'status' => 'FAILED'],
            ['tx_id' => 'TX003', 'amount' => '50.25', 'status' => 'SUCCESS'],
        ];
        FakeDataGenerator::writeToCsv($this->csvFile, $data);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->csvFile)) unlink($this->csvFile);
    }

    public function testTransactionLogPipeline(): void
    {
        // Generate 10,000 rows
        FakeDataGenerator::streamToCsv($this->csvFile, 10000);

        // Replicating the user's desired syntax
        $pipeline = Pipeline::create()
            ->extract(new CsvExtractor($this->csvFile))
            ->filter(fn(array $row) => $row['status'] === 'SUCCESS')
            ->map(fn(array $row) => [
                'id' => $row['tx_id'],
                'amount' => (float) $row['amount']
            ])
            ->chunk(1000)
            ->load(new BulkPdoLoader($this->pdo, 'clean_ledger'));

        $result = $pipeline->run();

        $this->assertTrue($result->isSuccessful());
        
        // Check database count
        // We can't know the exact count since status is random, but it should be > 0 and < 10000
        $dbCount = $this->pdo->query('SELECT COUNT(*) FROM clean_ledger')->fetchColumn();
        $this->assertGreaterThan(0, $dbCount);
        $this->assertLessThanOrEqual(10000, $dbCount);
        
        // The number of skipped rows should be (total - dbCount)
        $this->assertEquals($dbCount, $result->rowsProcessed);
    }
}
