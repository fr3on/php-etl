<?php

declare(strict_types=1);

namespace Fr3on\Etl\Tests\Unit\Extractor;

use PHPUnit\Framework\TestCase;
use Fr3on\Etl\Extractor\CsvExtractor;
use Fr3on\Etl\Tests\FakeDataGenerator;

class CsvExtractorTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'etl_test_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testExtractsCsvWithHeaders(): void
    {
        $data = [
            ['id' => '1', 'name' => 'Alice'],
            ['id' => '2', 'name' => 'Bob']
        ];
        FakeDataGenerator::writeToCsv($this->tempFile, $data);

        $extractor = new CsvExtractor($this->tempFile);
        $results = iterator_to_array($extractor->getIterator());

        $this->assertCount(2, $results);
        $this->assertEquals('Alice', $results[0]['name']);
        $this->assertEquals('Bob', $results[1]['name']);
    }

    public function testExtractsCsvWithoutHeaders(): void
    {
        $data = [
            ['1', 'Alice'],
            ['2', 'Bob']
        ];
        $handle = fopen($this->tempFile, 'w');
        foreach ($data as $row) fputcsv($handle, $row, escape: '\\');
        fclose($handle);

        $extractor = new CsvExtractor($this->tempFile, hasHeader: false);
        $results = iterator_to_array($extractor->getIterator());

        $this->assertCount(2, $results);
        $this->assertEquals('Alice', $results[0][1]);
    }

    public function testMemoryEfficiency(): void
    {
        // Generate 50,000 rows
        FakeDataGenerator::streamToCsv($this->tempFile, 50000);

        $extractor = new CsvExtractor($this->tempFile);
        
        $count = 0;
        foreach ($extractor->getIterator() as $row) {
            $count++;
            if ($count === 1) {
                $this->assertArrayHasKey('tx_id', $row);
            }
        }

        $this->assertEquals(50000, $count);
    }
}
