<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @template TOut
 * @extends StageInterface<TIn, TOut>
 */
interface StatefulStageInterface extends StageInterface
{
    /**
     * Return remaining buffered data at the end of the pipeline.
     * 
     * @return TOut|null
     */
    public function flush(): mixed;

    /**
     * Reset the stage for pipeline reuse.
     */
    public function reset(): void;
}
