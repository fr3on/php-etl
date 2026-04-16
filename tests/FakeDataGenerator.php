<?php

declare(strict_types=1);

namespace Fr3on\Etl\Tests;

class FakeDataGenerator
{
    /**
     * @return array<string, mixed>
     */
    private static function generateOneRow(int $index): array
    {
        return [
            'tx_id' => 'TX' . str_pad((string)$index, 7, '0', STR_PAD_LEFT),
            'amount' => round(mt_rand(100, 10000) / 100, 2),
            'status' => mt_rand(0, 1) ? 'SUCCESS' : 'FAILED',
            'created_at' => date('Y-m-d H:i:s', time() - mt_rand(0, 86400))
        ];
    }

    public static function streamToCsv(string $filePath, int $count): void
    {
        $handle = fopen($filePath, 'w');
        fputcsv($handle, array_keys(self::generateOneRow(1)), escape: '\\');
        for ($i = 1; $i <= $count; $i++) {
            fputcsv($handle, self::generateOneRow($i), escape: '\\');
        }
        fclose($handle);
    }

    public static function streamToJsonLines(string $filePath, int $count): void
    {
        $handle = fopen($filePath, 'w');
        for ($i = 1; $i <= $count; $i++) {
            fwrite($handle, json_encode(self::generateOneRow($i)) . PHP_EOL);
        }
        fclose($handle);
    }

    public static function writeToCsv(string $filePath, array $data): void
    {
        $handle = fopen($filePath, 'w');
        fputcsv($handle, array_keys($data[0]), escape: '\\');
        foreach ($data as $row) {
            fputcsv($handle, $row, escape: '\\');
        }
        fclose($handle);
    }

    public static function writeToJson(string $filePath, array $data, bool $jsonLines = false): void
    {
        if ($jsonLines) {
            $handle = fopen($filePath, 'w');
            foreach ($data as $row) {
                fwrite($handle, json_encode($row) . PHP_EOL);
            }
            fclose($handle);
        } else {
            file_put_contents($filePath, json_encode($data));
        }
    }
}
