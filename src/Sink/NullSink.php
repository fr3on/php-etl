<?php

declare(strict_types=1);

namespace Fr3on\Etl\Sink;

/**
 * @template TIn
 * @implements SinkInterface<TIn>
 */
class NullSink implements SinkInterface
{
    public function write(mixed $row): void {}
    public function flush(): void {}
}
