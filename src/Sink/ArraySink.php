<?php

declare(strict_types=1);

namespace Fr3on\Etl\Sink;

/**
 * @template TIn
 * @implements SinkInterface<TIn>
 */
class ArraySink implements SinkInterface
{
    /** @var array<int, TIn> */
    private array $data = [];

    public function write(mixed $row): void
    {
        $this->data[] = $row;
    }

    public function flush(): void {}

    /**
     * @return array<int, TIn>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
