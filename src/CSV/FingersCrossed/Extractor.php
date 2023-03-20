<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\ReaderInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Extractor implements ExtractorInterface
{
    public function __construct(private readonly ReaderInterface $reader, private readonly int $skipLines = 0, private readonly LoggerInterface $logger = new NullLogger())
    {
    }

    public function extract(): iterable
    {
        $sheet = $this->reader->getSheetIterator();

        $currentLine = $this->skipLines + 1;

        $columns = [];
        $columnCount = 0;
        $cellCount = 0;

        /**
         * @var int $rowIndex
         * @var Row $row
         */
        foreach ($sheet->current()->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex === $currentLine) {
                $columns = $row->toArray();
                $columnCount = \count($columns);
            }

            if ($rowIndex > $currentLine) {
                $line = $row->toArray();
                $cellCount = \count($line);
            }

            if (empty($line)) {
                continue;
            }
            if ($cellCount > $columnCount) {
                $line = \array_slice($line, 0, $columnCount, true);
            } elseif ($cellCount < $columnCount) {
                $line = array_pad($line, $columnCount - $cellCount, null);
            }

            yield new AcceptanceResultBucket(array_combine($columns, $line));
        }
    }
}
