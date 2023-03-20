<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet;

use functional\Kiboko\Component\Flow\Spreadsheet\Constraint\RowWasWrittenToExcel;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\TraversableContainsEqual;

trait ExcelAssertTrait
{
    abstract public static function assertThat($value, Constraint $constraint, string $message = ''): void;

    protected function assertRowWasWrittenToExcel(string $actual, string $sheet, array $expected, $message = ''): void
    {
        $this->assertThat($actual, new RowWasWrittenToExcel(new TraversableContainsEqual($expected), $sheet), $message);
    }

    protected function assertRowWasNotWrittenToExcel(string $actual, string $sheet, array $expected, $message = ''): void
    {
        $this->assertThat($actual, new LogicalNot(new RowWasWrittenToExcel(new TraversableContainsEqual($expected), $sheet)), $message);
    }
}
