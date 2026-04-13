<?php

declare(strict_types=1);

namespace Fr3on\Etl\Sink;

/**
 * @template TIn
 */
interface SinkInterface
{
    /**
     * @param TIn $row
     */
    public function write(mixed $row): void;

    /**
     * Finalize the sink (e.g., flush buffers, close files).
     */
    public function flush(): void;
}
