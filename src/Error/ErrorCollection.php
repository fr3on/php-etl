<?php

declare(strict_types=1);

namespace Fr3on\Etl\Error;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template TRow
 * @implements IteratorAggregate<int, ErrorRow<TRow>>
 */
class ErrorCollection implements IteratorAggregate, Countable
{
    /** @var array<int, ErrorRow<TRow>> */
    private array $errors = [];

    /**
     * @param ErrorRow<TRow> $error
     */
    public function add(ErrorRow $error): void
    {
        $this->errors[] = $error;
    }

    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * @return Traversable<int, ErrorRow<TRow>>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->errors);
    }

    /**
     * @return array<int, ErrorRow<TRow>>
     */
    public function all(): array
    {
        return $this->errors;
    }
}
