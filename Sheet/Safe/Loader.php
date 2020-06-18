<?php

namespace Kiboko\Component\ETL\Flow\Spout\Sheet\Safe;

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
        $headers = [];
        while (true) {
            $line = yield;

            if ($isFirstLine === true) {
                $this->writer->addRow($headers = array_keys($line));
                $isFirstLine = false;
            }

            $this->writer->addRow($this->orderColumns($headers, $line));

            yield new AcceptanceResultBucket($line);
        }
    }

    private function orderColumns(array $headers, array $line)
    {
        $result = [];
        foreach ($headers as $cell) {
            $result[$cell] = $line[$cell] ?? null;
        }

        return $result;
    }
}
