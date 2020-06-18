<?php

namespace Kiboko\Component\ETL\Flow\Spout\Sheet\FingersCrossed;

use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\ETL\Bucket\AcceptanceResultBucket;
use Kiboko\Component\ETL\Contracts\LoaderInterface;

class Loader implements LoaderInterface
{
    /** @var Writer */
    private $writer;

    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    public function load(): \Generator
    {
        $isFirstLine = true;
        while (true) {
            $line = yield;

            if ($isFirstLine === true) {
                $this->writer->addRow(array_keys($line));
                $isFirstLine = false;
            }

            $this->writer->addRow($line);

            yield new AcceptanceResultBucket($line);
        }
    }
}
