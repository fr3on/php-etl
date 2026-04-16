<?php

declare(strict_types=1);

namespace Fr3on\Etl\Extractor;

use Fr3on\Etl\Source\SourceInterface;

/**
 * @template TRow
 * @extends SourceInterface<TRow>
 */
interface ExtractorInterface extends SourceInterface
{
}
