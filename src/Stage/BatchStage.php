<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @implements StatefulStageInterface<TIn, array<int, TIn>|null>
 */
class BatchStage implements StatefulStageInterface
{
    /** @var array<int, TIn> */
    private array $buffer = [];

    public function __construct(private readonly int $size) {}

    /**
     * @param TIn $row
     * @return array<int, TIn>|null
     */
    public function process(mixed $row): ?array
    {
        $this->buffer[] = $row;
        if (count($this->buffer) >= $this->size) {
            $batch = $this->buffer;
            $this->buffer = [];
            return $batch;
        }
        return null;
    }

    /**
     * @return array<int, TIn>|null
     */
    public function flush(): ?array
    {
        if (empty($this->buffer)) {
            return null;
        }
        $batch = $this->buffer;
        $this->buffer = [];
        return $batch;
    }

    public function reset(): void
    {
        $this->buffer = [];
    }
}
