<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Reader\ReaderInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    public function __construct(
        private ReaderInterface $reader,
        private int $skipLines = 0
    ) {
    }

    public function extract(): iterable
    {
        $sheet = $this->reader->getSheetIterator();

        $currentLine = $this->skipLines + 1;

        foreach ($sheet->current()->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex === $currentLine) {
                $columns = $row->toArray();
                $columnCount = count($columns);
            }

            if ($rowIndex > $currentLine) {
                $line = $row->getCells();
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
