<?php

declare(strict_types=1);

namespace Fr3on\Etl\Branch;

use Fr3on\Etl\Stage\StageInterface;
use Fr3on\Etl\Pipeline;

/**
 * @template TRow
 * @implements StageInterface<TRow, TRow>
 */
class BranchStage implements StageInterface
{
    /** @var array<int, array{predicate: callable(TRow): bool, pipeline: Pipeline<TRow, mixed>}> */
    private array $branches = [];

    /**
     * @param callable(TRow): bool $predicate
     * @param Pipeline<TRow, mixed> $pipeline
     * @return self<TRow>
     */
    public function addBranch(callable $predicate, Pipeline $pipeline): self
    {
        $this->branches[] = [
            'predicate' => $predicate,
            'pipeline' => $pipeline,
        ];
        return $this;
    }

    /**
     * @param TRow $row
     * @return TRow
     */
    public function process(mixed $row): mixed
    {
        foreach ($this->branches as $branch) {
            if (($branch['predicate'])($row)) {
                $branch['pipeline']->processRowManual($row);
            }
        }
        return $row;
    }
}
