<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\WriterInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Component\Bucket\RejectionResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

readonly class Loader implements LoaderInterface, FlushableInterface
{
    public function __construct(
        private WriterInterface $writer,
        private LoggerInterface $logger = new NullLogger()
    ) {}

    public function load(): \Generator
    {
        $line = yield;
        try {
            $this->writer->addRow(
                new Row(array_map(fn ($value) => new Cell($value), array_keys($line)), null)
            );
        } catch (IOException|WriterNotOpenedException $exception) {
            $this->logger->error('Impossible to load data to the given CSV file.', ['line' => $line, 'message' => $exception->getMessage(), 'previous' => $exception->getPrevious()]);
            $line = yield new RejectionResultBucket(
                'Impossible to load data to the given CSV file.',
                $exception,
                $line
            );
        }

        while (true) {
            try {
                $this->writer->addRow(
                    new Row(array_map(fn ($value) => new Cell($value), $line), null)
                );
            } catch (IOException|WriterNotOpenedException $exception) {
                $this->logger->error('Impossible to load data to the given CSV file.', ['line' => $line, 'message' => $exception->getMessage(), 'previous' => $exception->getPrevious()]);
                $line = yield new RejectionResultBucket(
                    'Impossible to load data to the given CSV file.',
                    $exception,
                    $line
                );
            }

            $line = yield new AcceptanceResultBucket($line);
        }
    }

    public function flush(): ResultBucketInterface
    {
        $this->writer->close();

        return new EmptyResultBucket();
    }
}
