<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Writer\CSV\Manager\OptionsManager;
use Box\Spout\Writer\CSV\Writer;
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

    public function loadCsv(string $loaderClass): void
    {
        $writer = new Writer(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new HelperFactory()
        );

        $pipeline = new Pipeline(
            new PipelineRunner(),
            new \ArrayIterator([
                [
                    'first name' => 'john',
                    'last name' => 'doe'
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont'
                ]
            ])
        );

        $writer->openToFile('vfs://test.csv');

        $pipeline->load(new $loaderClass($writer));
        $pipeline->run();

        $this->assertEquals(
            file_get_contents('tests/functional/CSV/result-to-load.csv'),
            file_get_contents('vfs://test.csv')
        );
    }
}
