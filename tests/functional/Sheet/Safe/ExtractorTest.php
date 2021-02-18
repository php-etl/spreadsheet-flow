<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use functional\Kiboko\Component\Flow\Spreadsheet\Sheet\SheetExtractorTestCase;

final class ExtractorTest extends SheetExtractorTestCase
{
    public function testExtractXlsxSuccessful(): void
    {
        $this->testExtractSheet();
    }
}
