<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\ODS;
use functional\Kiboko\Component\Flow\Spreadsheet\OpenDocumentAssertTrait;
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
final class OpenDocumentLoaderTest extends TestCase
{
    use LoaderAssertTrait;
    use OpenDocumentAssertTrait;

    private ?vfsStreamDirectory $fs = null;
    private ?ODS\Writer $writer = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();

        $this->writer = WriterEntityFactory::createODSWriter();
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

        $this->writer->openToFile(/* 'vfs://test.ods' */ $path);

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

        $this->assertRowWasWrittenToOpenDocument(
            /* 'vfs://test.ods' */ $path,
            'Sheet1',
            ['first name', 'last name'],
        );

        $this->assertRowWasWrittenToOpenDocument(
            /* 'vfs://test.ods' */ $path,
            'Sheet1',
            ['john', 'doe'],
        );

        $this->assertRowWasWrittenToOpenDocument(
            /* 'vfs://test.ods' */ $path,
            'Sheet1',
            ['jean', 'dupont'],
        );
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
