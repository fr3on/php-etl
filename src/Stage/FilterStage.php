<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @implements StageInterface<TIn, TIn|null>
 */
class FilterStage implements StageInterface
{
    /** @var callable(TIn): bool */
    private $predicate;

    /**
     * @param callable(TIn): bool $predicate
     */
    public function __construct(callable $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * @param TIn $row
     * @return TIn|null
     */
    public function process(mixed $row): mixed
    {
        return ($this->predicate)($row) ? $row : null;
    }
}
