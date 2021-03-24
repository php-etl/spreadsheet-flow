<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Extractor implements ExtractorInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private Reader $reader,
        private int $skipLines = 0,
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $encoding = 'UTF-8',
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->reader->setFieldDelimiter($this->delimiter);
        $this->reader->setFieldEnclosure($this->enclosure);
        $this->reader->setEncoding($this->encoding);
    }

    public function extract(): iterable
    {
        $sheet = $this->reader->getSheetIterator();

        $currentLine = $this->skipLines + 1;

        /**
         * @var int $rowIndex
         * @var Row $row
         */
        foreach ($sheet->current()->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex === $currentLine) {
                $columns = $row->toArray();
                $columnCount = count($columns);
            }

            if ($rowIndex > $currentLine) {
                $line = $row->toArray();
                $cellCount = count($line);
            }

            if (empty($line)) {
                continue;
            } elseif ($cellCount > $columnCount) {
                $line = array_slice($line, 0, $columnCount, true);
            } elseif ($cellCount < $columnCount) {
                $line = array_pad($line, $columnCount - $cellCount, null);
            }

            yield array_combine($columns, $line);
        }
    }
}
