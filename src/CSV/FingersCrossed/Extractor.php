<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\ReaderInterface;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Extractor implements ExtractorInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private ReaderInterface $reader,
        private int $skipLines = 0,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
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
