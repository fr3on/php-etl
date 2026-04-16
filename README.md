# php-etl

A modern, typed, and lazy PHP 8.4+ data pipeline library.

`php-etl` is a high-performance ETL (Extract, Transform, Load) library designed to handle massive datasets with a constant memory footprint. Using PHP Generators under the hood, it processes data row-by-row, ensuring even the largest pipelines remain memory-safe.

## Features

- **Lazy Evaluation**: Entirely stream-based using Generators. Never materialize large arrays again.
- **Typed Pipelines**: Full support for PHPStan generics (`Pipeline<TIn, TOut>`).
- **High-Performance Extractors**: Built-in support for **CSV** and **JSON Lines** (ndjson) with zero memory overhead.
- **Batching & Chunking**: Efficiently process rows in batches using `chunk($size)` or `batch($size)`.
- **Fluent API**: Modern entry point via `Pipeline::create()`.
- **Error Resilience**: Configurable error policies (`COLLECT`, `SKIP`, `THROW`) to manage row-level failures.
- **Bulk Loading**: Built-in `BulkPdoLoader` for high-performance database inserts.
- **Zero Dependencies**: Lightweight core with no runtime dependencies.

## Installation

```bash
composer require fr3on/php-etl
```

## Usage

### Basic Example

```php
use Fr3on\Etl\Pipeline;
use Fr3on\Etl\Extractor\CsvExtractor;
use Fr3on\Etl\Sink\CallbackSink;

Pipeline::create()
    ->extract(new CsvExtractor('transactions.csv'))
    ->filter(fn($row) => $row['status'] === 'SUCCESS')
    ->map(fn($row) => ['id' => $row['tx_id'], 'amt' => (float)$row['amount']])
    ->into(new CallbackSink(fn($row) => print_r($row)))
    ->run();
```

### Bulk Loading Example

```php
use Fr3on\Etl\Loader\BulkPdoLoader;

Pipeline::create()
    ->extract(new CsvExtractor('large_data.csv'))
    ->chunk(1000)
    ->load(new BulkPdoLoader($pdo, 'target_table'))
    ->run();
```

## Performance

`php-etl` is built for extreme efficiency. Benchmarks on 100,000 rows demonstrate sub-second processing with a constant memory footprint.

| Extractor | Throughput | Peak Memory | Time (100k rows) |
| :--- | :--- | :--- | :--- |
| **JsonExtractor (JSONL)** | **~1,080,000 rows/sec** | **~2.0 MB** | **91 ms** |
| **CsvExtractor** | **~730,000 rows/sec** | **~2.0 MB** | **136 ms** |

*Benchmarks run on standard hardware (PHP 8.4, Opcache off). Results may vary depending on row complexity.*

## Testing & Benchmarking

The core library is tested for memory safety and high throughput.

```bash
composer test         # Run PHPUnit
composer test:memory  # Run with strict 32M memory limit
composer analyse      # Run PHPStan Level 9
composer bench        # Run PHPBench
```

## License

MIT License. See [LICENSE](LICENSE) for details.
