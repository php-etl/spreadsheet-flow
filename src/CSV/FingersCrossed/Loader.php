<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;

class Loader implements LoaderInterface, FlushableInterface
{
    public function __construct(private Writer $writer)
    {
    }

    public function load(): \Generator
    {
        $line = yield;
        $this->writer->addRow(
            new Row(array_map(fn ($value) => new Cell($value), array_keys($line)), null)
        );

        while (true) {
            $this->writer->addRow(
                new Row(array_map(fn ($value) => new Cell($value), $line), null)
            );

            $line = yield new AcceptanceResultBucket($line);
        }
    }

    public function flush(): ResultBucketInterface
    {
        $this->writer->close();

        return new EmptyResultBucket();
    }
}
