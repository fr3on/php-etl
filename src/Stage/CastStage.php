<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @implements StageInterface<array<string, mixed>, array<string, mixed>>
 */
class CastStage implements StageInterface
{
    /**
     * @param array<string, string> $specs [field => type] (int, float, string, bool)
     */
    public function __construct(private readonly array $specs) {}

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function process(mixed $row): array
    {
        foreach ($this->specs as $field => $type) {
            if (!isset($row[$field])) {
                continue;
            }

            /** @var mixed $value */
            $value = $row[$field];

            $row[$field] = match ($type) {
                'int' => is_scalar($value) ? (int) $value : 0,
                'float' => is_scalar($value) ? (float) $value : 0.0,
                'string' => is_scalar($value) || (is_object($value) && method_exists($value, '__toString')) ? (string) $value : '',
                'bool' => (bool) $value,
                default => $value,
            };
        }
        return $row;
    }
}
