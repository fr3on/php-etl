<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @implements StageInterface<TIn, TIn|null>
 */
class LimitStage implements StageInterface
{
    private int $count = 0;

    public function __construct(private readonly int $limit) {}

    /**
     * @param TIn $row
     * @return TIn|null
     */
    public function process(mixed $row): mixed
    {
        if ($this->count >= $this->limit) {
            return null;
        }

        $this->count++;
        return $row;
    }

    public function isFinished(): bool
    {
        return $this->count >= $this->limit;
    }
}
