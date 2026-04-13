<?php

declare(strict_types=1);

namespace Fr3on\Etl;

use Fr3on\Etl\Source\SourceInterface;
use Fr3on\Etl\Sink\SinkInterface;
use Fr3on\Etl\Stage\MapStage;
use Fr3on\Etl\Stage\FilterStage;
use Fr3on\Etl\Stage\BatchStage;
use Fr3on\Etl\Stage\UnbatchStage;
use Fr3on\Etl\Stage\LimitStage;
use Fr3on\Etl\Stage\SkipStage;
use Fr3on\Etl\Stage\TapStage;
use Fr3on\Etl\Stage\RenameStage;
use Fr3on\Etl\Stage\CastStage;
use Fr3on\Etl\Stage\FlatMapStage;
use Fr3on\Etl\Error\ErrorPolicy;

/**
 * Fluent factory for starting a pipeline.
 * 
 * @template TRow
 */
class Extract
{
    /** @var array<int, \Fr3on\Etl\Stage\StageInterface<mixed, mixed>> */
    private array $stages = [];
    private ErrorPolicy $errorPolicy = ErrorPolicy::THROW;

    /**
     * @param SourceInterface<TRow> $source
     */
    public function __construct(private readonly SourceInterface $source) {}

    /**
     * @template TIn
     * @param SourceInterface<TIn> $source
     * @return self<TIn>
     */
    public static function from(SourceInterface $source): self
    {
        return new self($source);
    }

    /**
     * @param callable(mixed): mixed $callback
     * @return self<TRow>
     */
    public function map(callable $callback): self
    {
        $this->stages[] = new MapStage($callback);
        return $this;
    }

    /**
     * @param callable(mixed): bool $predicate
     * @return self<TRow>
     */
    public function filter(callable $predicate): self
    {
        $this->stages[] = new FilterStage($predicate);
        return $this;
    }

    /**
     * @param callable(mixed): iterable<mixed> $callback
     * @return self<TRow>
     */
    public function flatMap(callable $callback): self
    {
        $this->stages[] = new FlatMapStage($callback);
        return $this;
    }

    /**
     * @return self<TRow>
     */
    public function batch(int $size): self
    {
        $this->stages[] = new BatchStage($size);
        return $this;
    }

    /**
     * @return self<TRow>
     */
    public function unbatch(): self
    {
        $this->stages[] = new UnbatchStage();
        return $this;
    }

    /**
     * @return self<TRow>
     */
    public function limit(int $limit): self
    {
        $this->stages[] = new LimitStage($limit);
        return $this;
    }

    /**
     * @return self<TRow>
     */
    public function skip(int $skip): self
    {
        $this->stages[] = new SkipStage($skip);
        return $this;
    }

    /**
     * @return self<TRow>
     */
    public function tap(callable $callback): self
    {
        $this->stages[] = new TapStage($callback);
        return $this;
    }

    /**
     * @param array<string, string> $mapping
     * @return self<TRow>
     */
    public function rename(array $mapping): self
    {
        $this->stages[] = new RenameStage($mapping);
        return $this;
    }

    /**
     * @param array<string, string> $specs
     * @return self<TRow>
     */
    public function cast(array $specs): self
    {
        $this->stages[] = new CastStage($specs);
        return $this;
    }

    /**
     * @return self<TRow>
     */
    public function withErrorPolicy(ErrorPolicy $policy): self
    {
        $this->errorPolicy = $policy;
        return $this;
    }

    /**
     * @template TOut
     * @param SinkInterface<TOut> $sink
     * @return Pipeline<TRow, TOut>
     */
    public function into(SinkInterface $sink): Pipeline
    {
        $pipeline = new Pipeline($this->source, $sink);
        $pipeline->withErrorPolicy($this->errorPolicy);
        
        foreach ($this->stages as $stage) {
            $pipeline->addStage($stage);
        }

        return $pipeline;
    }

    /**
     * Convenience method to run immediately into a BlackHole/NullSink or if into was already called.
     * Note: into() usually returns the pipeline to be run.
     */
}
