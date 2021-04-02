<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\CSV\Safe\Loader;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Vfs\FileSystem;

final class LoaderTest extends TestCase
{
    use PipelineAssertTrait;

    private ?FileSystem $fs = null;
    private ?Writer $writer = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();

        $this->writer = WriterEntityFactory::createCSVWriter();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;

        $this->writer = null;
    }

    public function testLoadCsvSuccessful()
    {
        $this->writer->openToFile('vfs://test.csv');

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
            new Loader($this->writer)
        );
    }

    public function testLoadCsvWithLogger()
    {
        $this->writer->openToFile('vfs://test.csv');

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
            new Loader($this->writer, new TestLogger())
        );

        $this->assertFileEquals(__DIR__.'/../data/users.csv', 'vfs://test.csv');
    }
}
