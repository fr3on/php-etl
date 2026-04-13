# php-etl

A modern, typed, and lazy PHP 8.4+ data pipeline library.

`php-etl` is a high-performance ETL (Extract, Transform, Load) library designed to handle massive datasets with a constant memory footprint. Using PHP Generators under the hood, it processes data row-by-row, ensuring even the largest pipelines remain memory-safe.

## ✨ Features

- **🚀 Lazy Evaluation**: Entirely stream-based using Generators. Never materialize large arrays again.
- **🛡️ Typed Pipelines**: Full support for PHPStan generics (`Pipeline<TIn, TOut>`).
- **🔗 Fluent API**: Easy-to-read pipeline construction via `Extract::from()`.
- **💥 Error Resilience**: Configurable error policies (`COLLECT`, `SKIP`, `THROW`) to manage row-level failures.
- **📦 Zero Dependencies**: Lightweight core with no runtime dependencies.
- **⚡ Batching Support**: Built-in support for processing rows in batches (useful for bulk inserts).

## 🚀 Installation

```bash
composer require fr3on/php-etl
```

## 🛠 Usage

### Basic Example

```php
use Fr3on\Etl\Extract;
use Fr3on\Etl\Source\ArraySource;
use Fr3on\Etl\Sink\CallbackSink;

Extract::from(new ArraySource([1, 2, 3, 4, 5]))
    ->map(fn($n) => $n * 10)
    ->filter(fn($n) => $n > 25)
    ->into(new CallbackSink(fn($n) => print($n . PHP_EOL)))
    ->run();
// Output: 30, 40, 50
```

### Error Collection

```php
use Fr3on\Etl\Error\ErrorPolicy;

$result = Extract::from($source)
    ->map($riskyTransform)
    ->withErrorPolicy(ErrorPolicy::COLLECT)
    ->into($sink)
    ->run();

foreach ($result->errors as $error) {
    echo "Error on row {$error->rowNumber} at stage '{$error->stageName}': {$error->exception->getMessage()}\n";
}
```

### Batch Processing

```php
Extract::from($source)
    ->batch(500)
    ->map(fn(array $batch) => $db->bulkInsert($batch))
    ->unbatch()
    ->into($sink)
    ->run();
```

## 🧪 Testing & Benchmarking

The core library is tested for memory safety and high throughput (>2M rows/sec on standard hardware).

```bash
composer test         # Run PHPUnit
composer test:memory  # Run with strict 32M memory limit
composer analyse      # Run PHPStan Level 9
composer bench        # Run PHPBench
```

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.
