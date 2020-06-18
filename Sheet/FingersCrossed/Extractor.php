<?php

namespace Kiboko\Component\ETL\Flow\Spout\Sheet\FingersCrossed;

use Box\Spout\Reader\SheetInterface;
use Kiboko\Component\ETL\Contracts\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    /** @var SheetInterface */
    private $sheet;
    /** @var int */
    private $skipLines;

    public function __construct(
        SheetInterface $sheet,
        int $skipLines = 0
    ) {
        $this->sheet = $sheet;
        $this->skipLines = $skipLines;
    }

    public function extract(): iterable
    {
        $iterator = $this->sheet->getRowIterator();
        $iterator->rewind();

        $this->skipLines($iterator, $this->skipLines);

        $columns = $iterator->current();
        $columnCount = count($columns);

        while ($iterator->valid()) {
            $iterator->next();

            $line = $iterator->current();
            $cellCount = count($line);

            if ($cellCount > $columnCount) {
                $line = array_slice($line, 0, $columnCount, true);
            } else if ($cellCount > $columnCount) {
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
