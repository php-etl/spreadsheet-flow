<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Writer\Common\Creator\InternalEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\XLSX\Creator\HelperFactory;
use Box\Spout\Writer\XLSX\Creator\ManagerFactory;
use Box\Spout\Writer\XLSX\Manager\OptionsManager;
use Box\Spout\Writer\XLSX\Writer;
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

    public function loadXlsx($loaderClass): void
    {
        $writer = new Writer(
            new OptionsManager(
                new StyleBuilder()
            ),
            new GlobalFunctionsHelper(),
            new HelperFactory(),
            new ManagerFactory(
                new InternalEntityFactory(),
                new HelperFactory()
            )
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

        $writer->openToFile('vfs://test.xlsx');

        $pipeline->load(new $loaderClass($writer));
        $pipeline->run();

        $this->assertEquals(
            file_get_contents('tests/functional/Sheet/result-to-load.xlsx'),
            file_get_contents('vfs://test.xlsx')
        );
    }
}
