<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Extractor implements ExtractorInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private ReaderInterface $reader,
        private string $sheetName,
        private int $skipLines = 0,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function extract(): iterable
    {
        $sheet = $this->findSheet($this->sheetName);

        $iterator = $sheet->getRowIterator();
        $iterator = new \LimitIterator($iterator, $this->skipLines);
        $iterator->rewind();
        $iterator = new \NoRewindIterator($iterator);

        $columns = null;
        foreach (new \LimitIterator($iterator, 0, 1) as $currentLine => $row) {
            $columns = $row->toArray();
        }

        if ($columns === null) {
            return;
        }
        $columnCount = count($columns);

        foreach ($iterator as $currentLine => $row) {
            $line = $row->toArray();
            $cellCount = count($line);

            if ($line === []) {
                continue;
            } elseif ($cellCount > $columnCount) {
                throw new \RuntimeException(strtr('The line %line% contains too much values: found %actual% values, was expecting %expected% values.', ['%line%' => $currentLine, '%expected%' => $columnCount, '%actual%' => $cellCount]));
            } elseif ($cellCount < $columnCount) {
                throw new \RuntimeException(strtr('The line %line% does not contain the proper values count: found %actual% values, was expecting %expected% values.', ['%line%' => $currentLine, '%expected%' => $columnCount, '%actual%' => $cellCount]));
            }

            yield new AcceptanceResultBucket(array_combine($columns, $line));
        }
    }

    public function flush(): ResultBucketInterface
    {
        $this->reader->close();

        return new EmptyResultBucket();
    }

    private function findSheet(string $name): SheetInterface
    {
        foreach ($this->reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() === $name) {
                return $sheet;
            }
        }

        throw new \OutOfBoundsException('No sheet with the name %name% can be found.', ['%name%' => $name]);
    }
}
