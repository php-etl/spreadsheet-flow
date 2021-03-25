<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\XLSX\Writer as XLSXWriter;
use Box\Spout\Writer\ODS\Writer as ODSWriter;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Loader implements LoaderInterface, FlushableInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private XLSXWriter|ODSWriter $writer,
        private string $sheetName,
        ?LoggerInterface $logger = null
    ) {
        $this->writer->getCurrentSheet()->setName($this->sheetName);
        $this->logger = $logger ?? new NullLogger();
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
