<?php

declare(strict_types=1);

namespace Fr3on\Etl\Tests\Unit\Extractor;

use PHPUnit\Framework\TestCase;
use Fr3on\Etl\Extractor\JsonExtractor;
use Fr3on\Etl\Tests\FakeDataGenerator;

class JsonExtractorTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'etl_json_test_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testExtractsStandardJson(): void
    {
        $data = [
            ['id' => 1, 'val' => 'A'],
            ['id' => 2, 'val' => 'B']
        ];
        FakeDataGenerator::writeToJson($this->tempFile, $data);

        $extractor = new JsonExtractor($this->tempFile);
        $results = iterator_to_array($extractor->getIterator());

        $this->assertCount(2, $results);
        $this->assertEquals('A', $results[0]['val']);
    }

    public function testExtractsJsonLines(): void
    {
        $data = [
            ['id' => 1, 'val' => 'A'],
            ['id' => 2, 'val' => 'B']
        ];
        FakeDataGenerator::writeToJson($this->tempFile, $data, jsonLines: true);

        $extractor = new JsonExtractor($this->tempFile, jsonLines: true);
        $results = iterator_to_array($extractor->getIterator());

        $this->assertCount(2, $results);
        $this->assertEquals('A', $results[0]['val']);
        $this->assertEquals('B', $results[1]['val']);
    }

    public function testMemoryEfficiencyJsonLines(): void
    {
        // Generate 50,000 rows
        FakeDataGenerator::streamToJsonLines($this->tempFile, 50000);

        $extractor = new JsonExtractor($this->tempFile, jsonLines: true);
        
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
