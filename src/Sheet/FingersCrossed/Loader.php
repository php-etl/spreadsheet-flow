<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\WriterInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Contract\Pipeline\LoaderInterface;

class Loader implements LoaderInterface
{
    public function __construct(
        private WriterInterface $writer
    ) {
    }

    public function load(): \Generator
    {
        $isFirstLine = true;
        while (true) {
            $line = yield;

            if ($isFirstLine === true) {
                $this->writer->addRow(
                    new Row(array_map(fn ($value) => new Cell($value), array_keys($line)), null)
                );
                $isFirstLine = false;
            }

            $line = new Row(array_map(fn ($value) => new Cell($value), $line), null);

            $this->writer->addRow($line);

            yield new AcceptanceResultBucket($line);
        }
    }
}
