<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Writer\CSV\Manager\OptionsManager;
use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed\Loader;
use Kiboko\Component\Pipeline\Pipeline;
use Kiboko\Component\Pipeline\PipelineRunner;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class LoaderTest extends TestCase
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

    public function testLoadCsvSuccessful(): void
    {
        $writer = new Writer(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new HelperFactory()
        );

        $loader = new Loader($writer);

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

        $pipeline->load($loader);
        $pipeline->run();

        $loader->load();

        $this->assertEquals(
            file_get_contents('tests/functional/CSV/FingersCrossed/result-to-load.csv'),
            file_get_contents('vfs://test.csv')
        );
    }
}
