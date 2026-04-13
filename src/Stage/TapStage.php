<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @implements StageInterface<TIn, TIn>
 */
class TapStage implements StageInterface
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

    /**
     * @param TIn $row
     * @return TIn
     */
    public function process(mixed $row): mixed
    {
        ($this->callback)($row);
        return $row;
    }
}
