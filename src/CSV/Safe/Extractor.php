<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Reader\CSV\Reader;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Pipeline\ExtractorInterface;

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
            ...(new \LimitIterator($reader->getSheetIterator(), 0, 1)),
            $skipLines
        );
    }

    public function extract(): iterable
    {
        return $this->inner->extract();
    }
}
