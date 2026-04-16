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
use Fr3on\Etl\Stage\StageInterface;
use Fr3on\Etl\Error\ErrorPolicy;
use RuntimeException;

/**
 * Fluent builder for creating and running a pipeline.
 * 
 * @template TRow
 */
class PipelineBuilder
{
    /** @var SourceInterface<TRow>|null */
    private ?SourceInterface $source = null;
    
    /** @var array<int, StageInterface<mixed, mixed>> */
    private array $stages = [];
    
    private ErrorPolicy $errorPolicy = ErrorPolicy::THROW;

    /**
     * @param SourceInterface<TRow>|null $source
     */
    public function __construct(?SourceInterface $source = null)
    {
        $this->source = $source;
    }

    /**
     * @template TIn
     * @param SourceInterface<TIn> $source
     * @return self<TIn>
     */
    public function extract(SourceInterface $source): self
    {
        $this->source = $source;
        return $this;
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
     * Alias for fetch/chunk.
     * 
     * @return self<TRow>
     */
    public function chunk(int $size): self
    {
        return $this->batch($size);
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
     * Alias for into().
     * 
     * @template TOut
     * @param SinkInterface<TOut> $sink
     * @return Pipeline<TRow, TOut>
     */
    public function load(SinkInterface $sink): Pipeline
    {
        return $this->into($sink);
    }

    /**
     * @template TOut
     * @param SinkInterface<TOut> $sink
     * @return Pipeline<TRow, TOut>
     */
    public function into(SinkInterface $sink): Pipeline
    {
        if ($this->source === null) {
            throw new RuntimeException('No source defined in pipeline. Call extract() first.');
        }

        $pipeline = new Pipeline($this->source, $sink);
        $pipeline->withErrorPolicy($this->errorPolicy);
        
        foreach ($this->stages as $stage) {
            $pipeline->addStage($stage);
        }

        return $pipeline;
    }
}
