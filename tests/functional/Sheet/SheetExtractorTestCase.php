<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\XLSX\Creator\HelperFactory;
use Box\Spout\Reader\XLSX\Creator\InternalEntityFactory;
use Box\Spout\Reader\XLSX\Creator\ManagerFactory;
use Box\Spout\Reader\XLSX\Manager\OptionsManager;
use Box\Spout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use Box\Spout\Reader\XLSX\Reader;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

class SheetExtractorTestCase extends TestCase
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

    public function extractSheet(string $extractorClass): void
    {
        $helperFactory = new HelperFactory();
        $managerFactory = new ManagerFactory(
            $helperFactory,
            new CachingStrategyFactory()
        );

        $reader = new Reader(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new InternalEntityFactory(
                $managerFactory,
                $helperFactory
            ),
            $managerFactory
        );

        $reader->open('tests/functional/Sheet/source-to-extract.xlsx');

        $iterator = $reader->getSheetIterator();
        $iterator->rewind();

        $extractor = new $extractorClass($iterator->current(), 0);

        $result = [];
        foreach ($extractor->extract() as $line) {
            $result[] = $line;
        }

        $this->assertEquals(
            [
                [
                    'first name' => 'john',
                    'last name' => 'doe'
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont'
                ]
            ],
            $result
        );
    }
}
