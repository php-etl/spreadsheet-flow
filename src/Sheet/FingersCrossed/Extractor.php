<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Reader\ReaderInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    public function __construct(
        private ReaderInterface $reader,
        private string $name,
        private int $skipLines = 0
    ) {
    }

    public function extract(): iterable
    {
        $sheet = $this->findSheet($this->name);

        if ($this->skipLines > 0){
            $this->skipLines = 0;
        }

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
                $line = array_slice($line, 0, $columnCount, true);
            } elseif ($cellCount < $columnCount) {
                $line = array_pad($line, $columnCount - $cellCount, null);
            }

            yield array_combine($columns, $line);
        }
    }

    public function findSheet(string $name)
    {
        foreach ($this->reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() === $name) {
                return $sheet;
            }
        }

        throw new \OutOfBoundsException('No sheet with the name %name% can be found.', ['%name%' => $name]);
    }
}
