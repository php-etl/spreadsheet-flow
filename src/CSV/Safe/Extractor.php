<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Pipeline\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    private ExtractorInterface $inner;

    /**
     * @throws ReaderNotOpenedException
     */
    public function __construct(
        Reader $reader,
        int $skipLines
    ) {
        $iterator = $reader->getSheetIterator();
        $iterator->rewind();

        $this->inner = new Sheet\Safe\Extractor(
            $iterator->current(),
            $skipLines
        );
    }

    public function extract(): iterable
    {
        return $this->inner->extract();
    }
}
