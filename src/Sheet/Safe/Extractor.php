<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Reader\ReaderInterface;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;

class Extractor implements ExtractorInterface, FlushableInterface
{
    public function __construct(
        private ReaderInterface $reader,
        private int $skipLines = 0
    )
    {
    }

    public function extract(): iterable
    {
        $sheetIterator = $this->reader->getSheetIterator();
        $sheetIterator->rewind();

        $rowIterator = $sheetIterator->current()->getRowIterator();
        $rowIterator->rewind();

        $this->skipLines($rowIterator, $this->skipLines);
        $columns = $rowIterator->current()->toArray();
        $columnCount = count($columns);

        $currentLine = $this->skipLines + 1;

        while ($rowIterator->valid()) {
            $rowIterator->next();

            $line = $rowIterator->current()->toArray();
            $cellCount = count($line);
            ++$currentLine;

            if (empty($line)) {
                continue;
            } elseif ($cellCount > $columnCount) {
                throw new \RuntimeException(strtr(
                    'The line %line% contains too much values: found %actual% values, was expecting %expected% values.',
                    [
                        '%line%' => $currentLine,
                        '%expected%' => $columnCount,
                        '%actual%' => $cellCount,
                    ]
                ));
            } elseif ($cellCount < $columnCount) {
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

    public function flush(): ResultBucketInterface
    {
        $this->reader->close();
        return new EmptyResultBucket();
    }
}
