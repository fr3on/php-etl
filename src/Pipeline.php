<?php

declare(strict_types=1);

namespace Fr3on\Etl;

use Fr3on\Etl\Source\SourceInterface;
use Fr3on\Etl\Stage\StageInterface;
use Fr3on\Etl\Stage\LimitStage;
use Fr3on\Etl\Stage\StatefulStageInterface;
use Fr3on\Etl\Sink\SinkInterface;
use Fr3on\Etl\Error\ErrorPolicy;
use Fr3on\Etl\Error\ErrorCollection;
use Fr3on\Etl\Error\ErrorRow;
use Throwable;

/**
 * @template TIn
 * @template TOut
 */
class Pipeline
{
    /** @var array<int, StageInterface<mixed, mixed>> */
    private array $stages = [];
    private ErrorPolicy $errorPolicy = ErrorPolicy::THROW;

    /**
     * @param SourceInterface<TIn> $source
     * @param SinkInterface<TOut> $sink
     */
    public function __construct(
        private readonly SourceInterface $source,
        private readonly SinkInterface $sink
    ) {}

    /**
     * @param StageInterface<mixed, mixed> $stage
     * @return self<TIn, TOut>
     */
    public function addStage(StageInterface $stage): self
    {
        $this->stages[] = $stage;
        return $this;
    }

    /**
     * @return self<TIn, TOut>
     */
    public function withErrorPolicy(ErrorPolicy $policy): self
    {
        $this->errorPolicy = $policy;
        return $this;
    }

    /**
     * @return PipelineResult<TIn>
     */
    public function run(): PipelineResult
    {
        $start = microtime(true);
        /** @var ErrorCollection<TIn> $errors */
        $errors = new ErrorCollection();
        $rowsProcessed = 0;
        $rowsSkipped = 0;
        $rowNumber = 0;

        foreach ($this->source->getIterator() as $row) {
            $rowNumber++;
            try {
                $processedRow = $this->processRowSequential($row, 0);
                
                if ($processedRow === null) {
                    $rowsSkipped++;
                    if ($this->shouldStop()) break;
                    continue;
                }

                $this->writeToSink($processedRow, $rowsProcessed);

            } catch (Throwable $e) {
                if ($this->errorPolicy === ErrorPolicy::THROW) throw $e;
                if ($this->errorPolicy === ErrorPolicy::COLLECT) {
                    $errors->add(new ErrorRow($row, $e, 'pipeline', $rowNumber));
                }
                $rowsSkipped++;
            }

            if ($this->shouldStop()) break;
        }

        // Handle remaining rows in stateful stages (e.g. BatchStage)
        foreach ($this->stages as $index => $stage) {
            if ($stage instanceof StatefulStageInterface) {
                $flushed = $stage->flush();
                if ($flushed !== null) {
                    // Pass flushed row to SUBSEQUENT stages
                    try {
                        $processedFlushed = $this->processRowSequential($flushed, $index + 1);
                        if ($processedFlushed !== null) {
                            $this->writeToSink($processedFlushed, $rowsProcessed);
                        }
                    } catch (Throwable $e) {
                        // In flush mode, we typically just throw or collect if policy allows
                        if ($this->errorPolicy === ErrorPolicy::THROW) throw $e;
                        $errors->add(new ErrorRow($flushed, $e, 'flush', 0));
                    }
                }
            }
        }

        $this->sink->flush();

        return new PipelineResult(
            $rowsProcessed,
            $rowsSkipped,
            $errors,
            microtime(true) - $start
        );
    }

    /**
     * @param mixed $row
     * @param int &$processedCount
     */
    private function writeToSink(mixed $row, int &$processedCount): void
    {
        if (is_iterable($row)) {
            foreach ($row as $subRow) {
                $this->sink->write($subRow);
                $processedCount++;
            }
        } else {
            $this->sink->write($row);
            $processedCount++;
        }
    }

    private function processRowSequential(mixed $row, int $startIndex): mixed
    {
        $current = $row;
        $count = count($this->stages);
        for ($i = $startIndex; $i < $count; $i++) {
            $stage = $this->stages[$i];
            $current = $stage->process($current);
            if ($current === null) {
                return null;
            }
        }
        return $current;
    }

    private function shouldStop(): bool
    {
        foreach ($this->stages as $stage) {
            if ($stage instanceof LimitStage && $stage->isFinished()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Internal helper for BranchStage etc.
     */
    public function processRowManual(mixed $row): void
    {
        $processed = $this->processRowSequential($row, 0);
        if ($processed !== null) {
            $dummyCount = 0;
            $this->writeToSink($processed, $dummyCount);
        }
    }
}
