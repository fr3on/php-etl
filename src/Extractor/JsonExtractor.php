<?php

declare(strict_types=1);

namespace Fr3on\Etl\Extractor;

use Generator;
use RuntimeException;

/**
 * JSON extractor supporting both Standard JSON and JSON Lines (ndjson).
 * 
 * @implements ExtractorInterface<mixed>
 */
class JsonExtractor implements ExtractorInterface
{
    /**
     * @param string $filePath Path to the JSON file.
     * @param bool $jsonLines Whether the file is in JSON Lines format (one JSON object per line).
     */
    public function __construct(
        private readonly string $filePath,
        private readonly bool $jsonLines = false
    ) {}

    /**
     * @return Generator<int, mixed>
     */
    public function getIterator(): Generator
    {
        if (!file_exists($this->filePath)) {
            throw new RuntimeException("JSON file not found: {$this->filePath}");
        }

        if ($this->jsonLines) {
            yield from $this->iterateJsonLines();
        } else {
            yield from $this->iterateStandardJson();
        }
    }

    /**
     * @return Generator<int, mixed>
     */
    private function iterateJsonLines(): Generator
    {
        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException("Could not open JSON Lines file: {$this->filePath}");
        }

        try {
            $rowNumber = 0;
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '') {
                    continue;
                }
                
                $data = json_decode($trimmed, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                  // Skip invalid lines in ETL is often preferred over crashing,
                  // but we should probably follow the ErrorPolicy defined in Pipeline.
                  // For now, we skip to maintain flow.
                  continue;
                }
                
                yield $rowNumber++ => $data;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return Generator<int, mixed>
     */
    private function iterateStandardJson(): Generator
    {
        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new RuntimeException("Could not read JSON file: {$this->filePath}");
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON format: " . json_last_error_msg());
        }

        if (is_array($data)) {
            $rowNumber = 0;
            foreach ($data as $row) {
                yield $rowNumber++ => $row;
            }
        } else {
            yield 0 => $data;
        }
    }
}
