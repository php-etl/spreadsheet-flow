<?php

namespace Kiboko\Component\ETL\Flow\Spout\CSV\Safe;

use Box\Spout\Reader\CSV\Reader;
use Kiboko\Component\ETL\Contracts\ExtractorInterface;
use Kiboko\Component\ETL\Flow\Spout\Sheet;

class Extractor implements ExtractorInterface
{
    /** @var ExtractorInterface */
    private $inner;

    /**
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function __construct(
        Reader $reader,
        int $skipLines
    ) {
        $this->inner = new Sheet\Safe\Extractor(
            ...(new \LimitIterator($reader->getSheetIterator(), 0, 1)), $skipLines
        );
    }

    public function extract(): iterable
    {
        return $this->inner->extract();
    }
}
