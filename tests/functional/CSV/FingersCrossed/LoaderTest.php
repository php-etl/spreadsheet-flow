<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use functional\Kiboko\Component\Flow\Spreadsheet\CSV\CsvLoaderTestCase;

final class LoaderTest extends CsvLoaderTestCase
{
    public function testLoadCsvSuccessful(): void
    {
        $this->testLoadFingersCrossedCsv();
    }
}
