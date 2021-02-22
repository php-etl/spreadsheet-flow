<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use functional\Kiboko\Component\Flow\Spreadsheet\Sheet\SheetLoaderTestCase;

final class LoaderTest extends SheetLoaderTestCase
{
    public function testLoadXlsxSuccessful()
    {
        $this->testLoadXlsx();
    }
}
