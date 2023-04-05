<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX;
use functional\Kiboko\Component\Flow\Spreadsheet\ExcelAssertTrait;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineRunner;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Loader;
use Kiboko\Component\PHPUnitExtension\Assert\LoaderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ExcelLoaderTest extends TestCase
{
    use LoaderAssertTrait;
    use ExcelAssertTrait;

    private ?vfsStreamDirectory $fs = null;
    private ?XLSX\Writer $writer = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();

        $this->writer = WriterEntityFactory::createXLSXWriter();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
        vfsStreamWrapper::unregister();

        $this->writer = null;
    }

    /**
     * @test
     */
    public function load(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'spreadsheet_');

        $this->writer->openToFile(/* 'vfs://test.xlsx' */ $path);

        $this->assertLoaderLoadsLike(
            [
                [
                    'first name' => 'john',
                    'last name' => 'doe',
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont',
                ],
            ],
            [
                [
                    'first name' => 'john',
                    'last name' => 'doe',
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont',
                ],
            ],
            new Loader($this->writer, 'Sheet1')
        );

        $this->assertRowWasWrittenToExcel(
            /* 'vfs://test.xlsx' */ $path,
            'Sheet1',
            ['first name', 'last name'],
        );

        $this->assertRowWasWrittenToExcel(
            /* 'vfs://test.xlsx' */ $path,
            'Sheet1',
            ['john', 'doe'],
        );

        $this->assertRowWasWrittenToExcel(
            /* 'vfs://test.xlsx' */ $path,
            'Sheet1',
            ['jean', 'dupont'],
        );
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
