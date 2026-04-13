<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @template TOut
 * @implements StageInterface<TIn, iterable<TOut>>
 */
class FlatMapStage implements StageInterface
{
    /** @var callable(TIn): iterable<TOut> */
    private $callback;

    /**
     * @param callable(TIn): iterable<TOut> $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param TIn $row
     * @return iterable<TOut>
     */
    public function process(mixed $row): mixed
    {
        return ($this->callback)($row);
    }
}
