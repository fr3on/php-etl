<?php

declare(strict_types=1);

namespace Fr3on\Etl\Branch;

use Fr3on\Etl\Source\SourceInterface;
use Generator;

/**
 * @template TRow
 * @implements SourceInterface<TRow>
 */
class MergeSource implements SourceInterface
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
        // Simple round-robin merge
        $iterators = [];
        foreach ($this->sources as $source) {
            $iterators[] = $source->getIterator();
        }
        
        while (!empty($iterators)) {
            foreach ($iterators as $index => $iterator) {
                if ($iterator->valid()) {
                    yield $iterator->current();
                    $iterator->next();
                } else {
                    unset($iterators[$index]);
                }
            }
        }
    }
}
