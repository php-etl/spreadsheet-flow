<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Pipeline\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    /**
     * @throws ReaderNotOpenedException
     */
    public function __construct(
        private Reader $reader,
        private int $skipLines = 0
    ) {
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
