<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\WriterInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class Loader implements LoaderInterface, FlushableInterface
{
    public function __construct(
        private WriterInterface $writer,
        private string $sheetName,
        private LoggerInterface $logger = new NullLogger()
    ) {
        /* @phpstan-ignore-next-line */
        $this->writer->getCurrentSheet()->setName($this->sheetName);
    }

    public function load(): \Generator
    {
        $line = yield;
        $headers = array_keys($line);
        try {
            $this->writer->addRow(
                new Row(array_map(fn ($value) => new Cell($value), array_keys($line)), null)
            );
        } catch (IOException|WriterNotOpenedException $exception) {
            $this->logger->error('Impossible to load data to the given CSV file.', ['line' => $line, 'message' => $exception->getMessage(), 'previous' => $exception->getPrevious()]);

            return;
        }

        while (true) {
            try {
                $this->writer->addRow($this->orderColumns($headers, $line));
            } catch (IOException|WriterNotOpenedException $exception) {
                $this->logger->error('Impossible to load data to the given CSV file.', ['line' => $line, 'message' => $exception->getMessage(), 'previous' => $exception->getPrevious()]);
            }

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
