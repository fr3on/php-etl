<?php

declare(strict_types=1);

namespace Fr3on\Etl\Sink;

/**
 * @template TIn
 * @implements SinkInterface<TIn>
 */
class CallbackSink implements SinkInterface
{
    /** @var callable(TIn): void */
    private $callback;

    /**
     * @param callable(TIn): void $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function write(mixed $row): void
    {
        ($this->callback)($row);
    }

    public function flush(): void {}
}
