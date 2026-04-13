<?php

declare(strict_types=1);

namespace Fr3on\Etl\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Fr3on\Etl\Extract;
use Fr3on\Etl\Source\ArraySource;
use Fr3on\Etl\Sink\ArraySink;
use Fr3on\Etl\Error\ErrorPolicy;

class BasicPipelineTest extends TestCase
{
    public function testSimpleMapFilterFlow(): void
    {
        $source = new ArraySource([1, 2, 3, 4, 5]);
        $sink = new ArraySink();

        $result = Extract::from($source)
            ->map(fn($n) => $n * 10)
            ->filter(fn($n) => $n > 25)
            ->into($sink)
            ->run();

        $this->assertEquals([30, 40, 50], $sink->getData());
        $this->assertEquals(3, $result->rowsProcessed);
        $this->assertEquals(2, $result->rowsSkipped);
        $this->assertTrue($result->isSuccessful());
    }

    public function testBatchUnbatchSymmetry(): void
    {
        $source = new ArraySource(range(1, 10));
        $sink = new ArraySink();

        $result = Extract::from($source)
            ->batch(3)
            ->unbatch()
            ->into($sink)
            ->run();

        $this->assertEquals(range(1, 10), $sink->getData());
        $this->assertEquals(10, $result->rowsProcessed);
    }

    public function testLimitAndSkip(): void
    {
        $source = new ArraySource(range(1, 10));
        $sink = new ArraySink();

        $result = Extract::from($source)
            ->skip(2)
            ->limit(3)
            ->into($sink)
            ->run();

        $this->assertEquals([3, 4, 5], $sink->getData());
        $this->assertEquals(3, $result->rowsProcessed);
    }

    public function testErrorCollectionPolicy(): void
    {
        $source = new ArraySource([1, 'fail', 3]);
        $sink = new ArraySink();

        $result = Extract::from($source)
            ->map(fn($v) => is_string($v) ? throw new \Exception('Boom') : $v)
            ->withErrorPolicy(ErrorPolicy::COLLECT)
            ->into($sink)
            ->run();

        $this->assertEquals([1, 3], $sink->getData());
        $this->assertEquals(2, $result->rowsProcessed);
        $this->assertEquals(1, $result->rowsSkipped);
        $this->assertCount(1, $result->errors);
        $this->assertEquals('Boom', $result->errors->all()[0]->exception->getMessage());
    }
}
