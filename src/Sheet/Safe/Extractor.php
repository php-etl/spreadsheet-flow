<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Psr\Log\LoggerInterface;

class Extractor implements ExtractorInterface, FlushableInterface
{
    private ?LoggerInterface $logger = null;

    public function __construct(
        private string $filePath,
        private ReaderInterface $reader,
        private string $sheetName,
        private int $skipLines = 0
    ) {
        $this->reader->open($this->filePath);
    }

    public function extract(): iterable
    {
        $sheet = $this->findSheet($this->sheetName);

        $currentLine = $this->skipLines + 1;

        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex === $currentLine) {
                $columns = $row->toArray();
                $columnCount = count($columns);
            }

            if ($rowIndex > $currentLine) {
                $line = $row->toArray();
                $cellCount = count($row->getCells());
            }

            if (empty($line)) {
                continue;
            } elseif ($cellCount > $columnCount) {
                throw new \RuntimeException(strtr('The line %line% contains too much values: found %actual% values, was expecting %expected% values.', ['%line%' => $currentLine, '%expected%' => $columnCount, '%actual%' => $cellCount]));
            } elseif ($cellCount < $columnCount) {
                throw new \RuntimeException(strtr('The line %line% does not contain the proper values count: found %actual% values, was expecting %expected% values.', ['%line%' => $currentLine, '%expected%' => $columnCount, '%actual%' => $cellCount]));
            }

            yield array_combine($columns, $line);
        }
    }

    public function flush(): ResultBucketInterface
    {
        $this->reader->close();

        return new EmptyResultBucket();
    }

    public function findSheet(string $name): SheetInterface
    {
        foreach ($this->reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() === $name) {
                return $sheet;
            }
        }

        throw new \OutOfBoundsException('No sheet with the name %name% can be found.', ['%name%' => $name]);
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }
}
