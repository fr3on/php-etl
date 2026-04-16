<?php

declare(strict_types=1);

namespace Fr3on\Etl\Loader;

use PDO;
use RuntimeException;

/**
 * High-performance bulk PDO loader.
 * Buffers rows and executes bulk inserts for maximum performance.
 * 
 * @implements LoaderInterface<array<string, mixed>>
 */
class BulkPdoLoader implements LoaderInterface
{
    /** @var array<int, array<string, mixed>> */
    private array $buffer = [];

    /**
     * @param PDO $pdo The PDO connection.
     * @param string $tableName The target table name.
     * @param int $batchSize The number of rows to insert in a single query.
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $tableName,
        private readonly int $batchSize = 1000
    ) {
        // Ensure PDO is set to throw exceptions
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param array<string, mixed> $row
     */
    public function write(mixed $row): void
    {
        $this->buffer[] = (array)$row;

        if (count($this->buffer) >= $this->batchSize) {
            $this->flush();
        }
    }

    /**
     * Finalize the sink by flushing any remaining rows in the buffer.
     */
    public function flush(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        $this->executeBulkInsert($this->buffer);
        $this->buffer = [];
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     */
    private function executeBulkInsert(array $batch): void
    {
        $columns = array_keys($batch[0]);
        $columnList = implode(', ', array_map(fn($c) => "`$c`", $columns));
        
        // Prepare placeholders: (?, ?, ?), (?, ?, ?)
        $rowPlaceholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($batch), $rowPlaceholders));

        $sql = "INSERT INTO `{$this->tableName}` ({$columnList}) VALUES {$allPlaceholders}";
        
        $stmt = $this->pdo->prepare($sql);
        
        $values = [];
        foreach ($batch as $row) {
            foreach ($columns as $column) {
                $values[] = $row[$column] ?? null;
            }
        }

        $stmt->execute($values);
    }
}
