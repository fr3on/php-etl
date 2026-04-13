<?php

declare(strict_types=1);

namespace Fr3on\Etl\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Fr3on\Etl\Extract;
use Fr3on\Etl\Source\GeneratorSource;
use Fr3on\Etl\Sink\NullSink;

class MemorySafetyTest extends TestCase
{
    /**
     * Processing 100k rows should not exceed the memory limit if the pipeline is lazy.
     */
    public function testLargeDatasetMemoryUsage(): void
    {
        // Use a generator to avoid creating a large array in memory
        $source = new GeneratorSource(function () {
            for ($i = 0; $i < 100_000; $i++) {
                yield ['id' => $i, 'data' => str_repeat('a', 100)];
            }
        });

        $sink = new NullSink();

        $startMemory = memory_get_usage();

        Extract::from($source)
            ->map(fn($row) => ['id' => $row['id'], 'data' => strtoupper($row['data'])])
            ->batch(100)
            ->unbatch()
            ->into($sink)
            ->run();

        $endMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();

        // Check that memory didn't balloon significantly (e.g. not > 5MB additional)
        $this->assertLessThan(5 * 1024 * 1024, $endMemory - $startMemory, 'Memory leaked or too much was materialized.');
        // If it materialized 100k rows of ~100 bytes each, it would be > 10MB. 
    }
}
