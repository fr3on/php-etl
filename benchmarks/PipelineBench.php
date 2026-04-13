<?php

declare(strict_types=1);

namespace Fr3on\Etl\Benchmarks;

use Fr3on\Etl\Extract;
use Fr3on\Etl\Source\GeneratorSource;
use Fr3on\Etl\Sink\NullSink;

/**
 * @BeforeMethods({"setUp"})
 */
class PipelineBench
{
    private GeneratorSource $source;
    private NullSink $sink;

    public function setUp(): void
    {
        $this->source = new GeneratorSource(function () {
            for ($i = 0; $i < 100_000; $i++) {
                yield $i;
            }
        });
        $this->sink = new NullSink();
    }

    /**
     * @Revs(5)
     * @Iterations(3)
     */
    public function benchStandardPipeline(): void
    {
        Extract::from($this->source)
            ->map(fn($n) => $n * 2)
            ->filter(fn($n) => $n % 2 === 0)
            ->into($this->sink)
            ->run();
    }
}
