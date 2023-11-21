<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

readonly class Extractor implements ExtractorInterface
{
    public function __construct(
        private ReaderInterface $reader,
        private string $sheetName,
        private int $skipLines = 0,
        private LoggerInterface $logger = new NullLogger()
    ) {}

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

        if (null === $columns) {
            return;
        }
        $columnCount = is_countable($columns) ? \count($columns) : 0;

        foreach ($iterator as $currentLine => $row) {
            $line = $row->toArray();
            $cellCount = is_countable($line) ? \count($line) : 0;

            if ([] === $line) {
                continue;
            }
            if ($cellCount > $columnCount) {
                $line = \array_slice($line, 0, $columnCount, true);
            } elseif ($cellCount < $columnCount) {
                $line = array_pad($line, $columnCount - $cellCount, null);
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
        try {
            $iterator = $this->reader->getSheetIterator();

            foreach ($iterator as $sheet) {
                if ($sheet->getName() === $name) {
                    return $sheet;
                }
            }
        } catch (ReaderNotOpenedException $exception) {
            $this->logger->error('Impossible to extract data from the given Spreadsheet file.', ['message' => $exception->getMessage(), 'previous' => $exception->getPrevious()]);
        }

        throw new \OutOfBoundsException(strtr('No sheet with the name %name% can be found.', ['%name%' => $name]));
    }
}
