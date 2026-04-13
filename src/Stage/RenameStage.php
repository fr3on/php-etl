<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @implements StageInterface<array<string, mixed>, array<string, mixed>>
 */
class RenameStage implements StageInterface
{
    /**
     * @param array<string, string> $mapping [oldKey => newKey]
     */
    public function __construct(private readonly array $mapping) {}

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function process(mixed $row): array
    {
        $newRow = [];
        foreach ($row as $key => $value) {
            $targetKey = $this->mapping[$key] ?? $key;
            $newRow[$targetKey] = $value;
        }
        return $newRow;
    }
}
