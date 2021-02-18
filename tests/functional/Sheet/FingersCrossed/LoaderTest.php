<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use functional\Kiboko\Component\Flow\Spreadsheet\Sheet\SheetLoaderTestCase;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Loader;

final class LoaderTest extends SheetLoaderTestCase
{
    public function testLoadXlsxSuccessful()
    {
        $this->testLoadXlsx();
    }
}
