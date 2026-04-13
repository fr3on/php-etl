<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @implements StageInterface<TIn, TIn|null>
 */
class SkipStage implements StageInterface
{
    private int $count = 0;

    public function __construct(private readonly int $skip) {}

    /**
     * @param TIn $row
     * @return TIn|null
     */
    public function process(mixed $row): mixed
    {
        if ($this->count < $this->skip) {
            $this->count++;
            return null;
        }

        return $row;
    }
}
