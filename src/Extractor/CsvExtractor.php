<?php

declare(strict_types=1);

namespace Fr3on\Etl\Extractor;

use Generator;
use SplFileObject;
use RuntimeException;

/**
 * High-performance streaming CSV extractor.
 * 
 * @implements ExtractorInterface<array<string, mixed>|list<mixed>>
 */
class CsvExtractor implements ExtractorInterface
{
    /**
     * @param string $filePath Path to the CSV file.
     * @param string $delimiter The field delimiter (one single-byte character only).
     * @param string $enclosure The field enclosure character (one single-byte character only).
     * @param string $escape The escape character (at most one single-byte character).
     * @param bool $hasHeader Whether the first row contains column headers.
     */
    public function __construct(
        private readonly string $filePath,
        private readonly string $delimiter = ',',
        private readonly string $enclosure = '"',
        private readonly string $escape = '\\',
        private readonly bool $hasHeader = true
    ) {}

    /**
     * @return Generator<int, array<string, mixed>|list<mixed>>
     */
    public function getIterator(): Generator
    {
        if (!file_exists($this->filePath)) {
            throw new RuntimeException("CSV file not found: {$this->filePath}");
        }

        $file = new SplFileObject($this->filePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

        $headers = null;
        $rowNumber = 0;

        foreach ($file as $row) {
            if (!is_array($row) || $row === [null] || empty($row)) {
                continue;
            }

            if ($this->hasHeader && $headers === null) {
                $headers = array_map(fn($v) => (string)$v, $row);
                continue;
            }

            if ($this->hasHeader && $headers !== null) {
                if (count($headers) !== count($row)) {
                    continue;
                }
                
                $data = array_combine($headers, $row);
                yield $rowNumber++ => $data;
            } else {
                yield $rowNumber++ => $row;
            }
        }
    }
}
