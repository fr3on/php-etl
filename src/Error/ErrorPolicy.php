<?php

declare(strict_types=1);

namespace Fr3on\Etl\Error;

enum ErrorPolicy: string
{
    /** Catch and collect errors without stopping the pipeline. */
    case COLLECT = 'collect';

    /** Silently skip rows that cause errors. */
    case SKIP = 'skip';

    /** Throw exceptions and halt the pipeline. */
    case THROW = 'throw';
}
