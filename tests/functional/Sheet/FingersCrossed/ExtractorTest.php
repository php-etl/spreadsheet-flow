<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use functional\Kiboko\Component\Flow\Spreadsheet\Sheet\SheetExtractorTestCase;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Extractor;

final class ExtractorTest extends SheetExtractorTestCase
{
    public function testExtractXlsxSuccessful(): void
    {
        $this->extractSheet();
    }

    private function extractSheet()
    {
        // TODO
    }
}
