<?php

declare(strict_types=1);

namespace Fr3on\Etl\Benchmarks;

use Fr3on\Etl\Pipeline;
use Fr3on\Etl\Extractor\CsvExtractor;
use Fr3on\Etl\Extractor\JsonExtractor;
use Fr3on\Etl\Sink\NullSink;
use Fr3on\Etl\Tests\FakeDataGenerator;

/**
 * @BeforeMethods({"setUp"})
 * @AfterMethods({"tearDown"})
 */
class ExtractorBench
{
    private string $csvFile;
    private string $jsonlFile;
    private NullSink $sink;

    public function setUp(): void
    {
        $this->csvFile = tempnam(sys_get_temp_dir(), 'bench_csv_');
        $this->jsonlFile = tempnam(sys_get_temp_dir(), 'bench_jsonl_');
        
        // Generate 100,000 rows for benchmarking
        FakeDataGenerator::streamToCsv($this->csvFile, 100000);
        FakeDataGenerator::streamToJsonLines($this->jsonlFile, 100000);
        
        $this->sink = new NullSink();
    }

    public function tearDown(): void
    {
        if (file_exists($this->csvFile)) unlink($this->csvFile);
        if (file_exists($this->jsonlFile)) unlink($this->jsonlFile);
    }

    /**
     * @Revs(5)
     * @Iterations(3)
     */
    public function benchCsvExtractor(): void
    {
        Pipeline::create()
            ->extract(new CsvExtractor($this->csvFile))
            ->load($this->sink)
            ->run();
    }

    /**
     * @Revs(5)
     * @Iterations(3)
     */
    public function benchJsonLinesExtractor(): void
    {
        Pipeline::create()
            ->extract(new JsonExtractor($this->jsonlFile, jsonLines: true))
            ->load($this->sink)
            ->run();
    }
}
