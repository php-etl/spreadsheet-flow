<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use functional\Kiboko\Component\Flow\Spreadsheet\CSV\CsvExtractorTestCase;
use Kiboko\Component\Flow\Spreadsheet\CSV\Safe\Extractor;

final class ExtractorTest extends CsvExtractorTestCase
{
    public function testExtractCsvSuccessful()
    {
        $this->extractCsv(Extractor::class);
    }
}
