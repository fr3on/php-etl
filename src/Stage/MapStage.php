<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @template TOut
 * @implements StageInterface<TIn, TOut>
 */
class MapStage implements StageInterface
{
    /** @var callable(TIn): TOut */
    private $callback;

    /**
     * @param callable(TIn): TOut $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param TIn $row
     * @return TOut
     */
    public function process(mixed $row): mixed
    {
        return ($this->callback)($row);
    }
}
