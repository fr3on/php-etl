<?php

declare(strict_types=1);

namespace Fr3on\Etl\Loader;

use Fr3on\Etl\Sink\SinkInterface;

/**
 * @template TIn
 * @extends SinkInterface<TIn>
 */
interface LoaderInterface extends SinkInterface
{
}
