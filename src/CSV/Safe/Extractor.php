<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

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
}
