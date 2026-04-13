<?php

declare(strict_types=1);

namespace Fr3on\Etl\Source;

use Generator;

/**
 * @template TRow
 * @implements SourceInterface<TRow>
 */
class GeneratorSource implements SourceInterface
{
    /** @var callable(): Generator<int, TRow> */
    private $generatorFactory;

    /**
     * @param callable(): Generator<int, TRow> $generatorFactory
     */
    public function __construct(callable $generatorFactory)
    {
        $this->generatorFactory = $generatorFactory;
    }

    /**
     * @return Generator<int, TRow>
     */
    public function getIterator(): Generator
    {
        yield from ($this->generatorFactory)();
    }
}
