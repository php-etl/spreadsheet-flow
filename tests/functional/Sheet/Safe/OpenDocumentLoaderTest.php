<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\ODS;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Loader;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class OpenDocumentLoaderTest extends TestCase
{
    use PipelineAssertTrait;

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
        $this->writer->openToFile('vfs://test.ods');

        $this->assertPipelineDoesLoadLike(
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

//        $this->assertFileEquals(__DIR__.'/../data/users.xlsx', 'vfs://test.ods');
    }
}
