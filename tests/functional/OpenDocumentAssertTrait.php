<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet;

use functional\Kiboko\Component\Flow\Spreadsheet\Constraint\RowWasWrittenToOpenDocument;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\TraversableContainsEqual;

trait OpenDocumentAssertTrait
{
    abstract public static function assertThat($value, Constraint $constraint, string $message = ''): void;

    protected function assertRowWasWrittenToOpenDocument(string $actual, string $sheet, array $expected, $message = '')
    {
        $this->assertThat($actual, new RowWasWrittenToOpenDocument(new TraversableContainsEqual($expected), $sheet), $message);
    }

    protected function assertRowWasNotWrittenToOpenDocument(string $actual, string $sheet, array $expected, $message = '')
    {
        $this->assertThat($actual, new LogicalNot(new RowWasWrittenToOpenDocument(new TraversableContainsEqual($expected), $sheet)), $message);
    }
}
