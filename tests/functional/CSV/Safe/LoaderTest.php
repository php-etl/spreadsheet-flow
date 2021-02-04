<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use functional\Kiboko\Component\Flow\Spreadsheet\CSV\CsvLoaderTestCase;
use Kiboko\Component\Flow\Spreadsheet\CSV\Safe\Loader;

final class LoaderTest extends CsvLoaderTestCase
{
    public function testLoadCsvSuccessful(): void
    {
        $this->loadCsv(Loader::class);
    }
}
