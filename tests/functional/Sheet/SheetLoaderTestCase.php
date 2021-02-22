<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Loader;
use Kiboko\Component\Pipeline\Pipeline;
use Kiboko\Component\Pipeline\PipelineRunner;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

class SheetLoaderTestCase extends TestCase
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

    public function testLoadXlsx(): void
    {
        $iterator = new \ArrayIterator([
            [
                'first name' => 'john',
                'last name' => 'doe',
            ],
            [
                'first name' => 'jean',
                'last name' => 'dupont',
            ],
        ]);

        $writer = WriterEntityFactory::createXLSXWriter();

        $pipeline = new Pipeline(new PipelineRunner(null), $iterator);

        $writer->openToFile('vfs://test.xlsx');

        $pipeline
            ->load(new Loader($writer, $iterator))
            ->run();

        $this->assertEquals(
            hash('sha1', str_replace(' ', '', file_get_contents('tests/functional/Sheet/result-to-load.xlsx'))),
            hash('sha1', str_replace(' ', '', file_get_contents('vfs://test.xlsx')))
        );
    }
}
