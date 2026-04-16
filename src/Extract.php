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
 * Legacy fluent factory for starting a pipeline.
 * Use Pipeline::create() for new code.
 * 
 * @template TRow
 */
class Extract
{
    /**
     * @param SourceInterface<TRow> $source
     * @return PipelineBuilder<TRow>
     */
    public static function from(SourceInterface $source): PipelineBuilder
    {
        return new PipelineBuilder($source);
    }
}
