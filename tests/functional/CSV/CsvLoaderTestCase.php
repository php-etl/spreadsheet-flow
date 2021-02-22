<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Writer\CSV\Manager\OptionsManager;
use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\Flow\Spreadsheet\CSV\Safe\Loader;
use Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed\Loader as FingerLoader;
use Kiboko\Component\Pipeline\Pipeline;
use Kiboko\Component\Pipeline\PipelineRunner;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

class CsvLoaderTestCase extends TestCase
{
    private ?FileSystem $fs = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;
    }

    public function testLoadCsv(): void
    {
        $writer = new Writer(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new HelperFactory()
        );

        $pipeline = new Pipeline(
            new PipelineRunner(null),
            new \ArrayIterator([
                [
                    'first name' => 'john',
                    'last name' => 'doe',
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont',
                ],
            ])
        );

        $writer->openToFile('vfs://test.csv');

        $pipeline->load(new Loader($writer));
        $pipeline->run();

        $this->assertEquals(
            file_get_contents('tests/functional/CSV/result-to-load.csv'),
            file_get_contents('vfs://test.csv')
        );
    }

    public function testLoadFingersCrossedCsv(): void
    {
        $writer = new Writer(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new HelperFactory()
        );

        $pipeline = new Pipeline(
            new PipelineRunner(null),
            new \ArrayIterator([
                [
                    'first name' => 'john',
                    'last name' => 'doe',
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont',
                ],
            ])
        );

        $writer->openToFile('vfs://test.csv');

        $pipeline->load(new FingerLoader($writer));
        $pipeline->run();

        $this->assertEquals(
            file_get_contents('tests/functional/CSV/result-to-load.csv'),
            file_get_contents('vfs://test.csv')
        );
    }
}
