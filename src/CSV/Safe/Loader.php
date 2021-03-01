<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

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
        $headers = array_keys($line);
        $this->writer->addRow(
            new Row(array_map(fn ($value) => new Cell($value), array_keys($line)), null)
        );

        while (true) {
            $this->writer->addRow($this->orderColumns($headers, $line));

            $line = yield new AcceptanceResultBucket($line);
        }
    }

    private function orderColumns(array $headers, array $line): Row
    {
        $result = [];
        foreach ($headers as $cell) {
            $result[$cell] = new Cell($line[$cell] ?? null);
        }

        return new Row($result, null);
    }

    public function flush(): ResultBucketInterface
    {
        $this->writer->close();

        return new EmptyResultBucket();
    }
}
