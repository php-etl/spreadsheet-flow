<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Reader\SheetInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    public function __construct(
        private SheetInterface $sheet,
        private int $skipLines = 0
    ) {
    }

    public function extract(): iterable
    {
        $iterator = $this->sheet->getRowIterator();
        $iterator->rewind();

        $this->skipLines($iterator, $this->skipLines);

        $columns = $iterator->current();
        $columnCount = count($columns);

        $currentLine = $this->skipLines + 1;
        while ($iterator->valid()) {
            $iterator->next();

            $line = $iterator->current();
            $cellCount = count($line);
            ++$currentLine;

            if ($cellCount > $columnCount) {
                throw new \RuntimeException(strtr(
                    'The line %line% contains too much values: found %actual% values, was expecting %expected% values.',
                    [
                        '%line%' => $currentLine,
                        '%expected%' => $columnCount,
                        '%actual%' => $cellCount,
                    ]
                ));
            } elseif ($cellCount > $columnCount) {
                throw new \RuntimeException(strtr(
                    'The line %line% does not contain the proper values count: found %actual% values, was expecting %expected% values.',
                    [
                        '%line%' => $currentLine,
                        '%expected%' => $columnCount,
                        '%actual%' => $cellCount,
                    ]
                ));
            }

            yield array_combine($columns, $line);
        }
    }

    private function skipLines(\Iterator $iterator, int $skipLines): void
    {
        for ($i = 0; $i < $skipLines; $i++) {
            $iterator->next();

            if (!$iterator->valid()) {
                throw new \RuntimeException('Reached unexpected end of source.');
            }
        }
    }
}
