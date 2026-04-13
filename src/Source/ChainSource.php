<?php

declare(strict_types=1);

namespace Fr3on\Etl\Source;

use Generator;

/**
 * @template TRow
 * @implements SourceInterface<TRow>
 */
class ChainSource implements SourceInterface
{
    /** @var list<SourceInterface<TRow>> */
    private array $sources;

    /**
     * @param SourceInterface<TRow> ...$sources
     */
    public function __construct(SourceInterface ...$sources)
    {
        $this->sources = array_values($sources);
    }

    /**
     * @return Generator<int, TRow>
     */
    public function getIterator(): Generator
    {
        foreach ($this->sources as $source) {
            yield from $source->getIterator();
        }
    }
}
