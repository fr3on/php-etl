<?php

declare(strict_types=1);

namespace Fr3on\Etl\Source;

use IteratorAggregate;
use Generator;

/**
 * @template TRow
 * @extends IteratorAggregate<int, TRow>
 */
interface SourceInterface extends IteratorAggregate
{
    /**
     * @return Generator<int, TRow>
     */
    public function getIterator(): Generator;
}
