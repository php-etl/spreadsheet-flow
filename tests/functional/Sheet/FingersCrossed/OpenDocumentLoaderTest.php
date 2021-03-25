<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\ODS;
use functional\Kiboko\Component\Flow\Spreadsheet\OpenDocumentAssertTrait;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Loader;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class OpenDocumentLoaderTest extends TestCase
{
    use PipelineAssertTrait;
    use OpenDocumentAssertTrait;

    private ?FileSystem $fs = null;
    private ?ODS\Writer $writer = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();

        $this->writer = WriterEntityFactory::createODSWriter();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;

        $this->writer = null;
    }

    public function testLoad()
    {
        $path = tempnam(sys_get_temp_dir(), 'spreadsheet_');

        $this->writer->openToFile(/*'vfs://test.ods'*/$path);

        $this->assertPipelineLoadsLike(
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
            /*'vfs://test.ods'*/$path,
            'Sheet1',
            ['first name', 'last name'],
        );

        $this->assertRowWasWrittenToOpenDocument(
            /*'vfs://test.ods'*/$path,
            'Sheet1',
            ['john', 'doe'],
        );

        $this->assertRowWasWrittenToOpenDocument(
            /*'vfs://test.ods'*/$path,
            'Sheet1',
            ['jean', 'dupont'],
        );
    }
}
