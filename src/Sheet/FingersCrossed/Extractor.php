<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Reader\SheetInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    public function __construct(
        private SheetInterface $sheet,
        private int $skipLines = 0
    ) {}

    public function extract(): iterable
    {
        $iterator = $this->sheet->getRowIterator();
        $iterator->rewind();

        $this->skipLines($iterator, $this->skipLines);

        $columns = $iterator->current()->toArray();
        $columnCount = count($columns);

        while ($iterator->valid()) {
            $iterator->next();

            $line = $iterator->current()->toArray();
            $cellCount = count($line);

            if ($cellCount > $columnCount) {
                $line = array_slice($line, 0, $columnCount, true);
            } else if ($cellCount < $columnCount) {
                $line = array_pad($line, $columnCount - $cellCount, null);
            }

            yield array_combine($columns, $line);
        }
    }

    private function skipLines(\Iterator $iterator, int $skipLines)
    {
        for ($i = 0; $i < $skipLines; $i++) {
            $iterator->next();

            if (!$iterator->valid()) {
                throw new \RuntimeException('Reached unexpected end of source.');
            }
        }
    }
}
