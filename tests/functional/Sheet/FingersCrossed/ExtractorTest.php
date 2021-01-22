<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Common\Helper\Escaper\XLSX;
use Box\Spout\Reader\Common\Manager\RowManager;
use Box\Spout\Reader\Common\XMLProcessor;
use Box\Spout\Reader\Wrapper\XMLReader;
use Box\Spout\Reader\XLSX\Creator\HelperFactory;
use Box\Spout\Reader\XLSX\Creator\InternalEntityFactory;
use Box\Spout\Reader\XLSX\Creator\ManagerFactory;
use Box\Spout\Reader\XLSX\Helper\CellValueFormatter;
use Box\Spout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use Box\Spout\Reader\XLSX\Manager\SharedStringsManager;
use Box\Spout\Reader\XLSX\Manager\StyleManager;
use Box\Spout\Reader\XLSX\Manager\WorkbookRelationshipsManager;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\Sheet;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Extractor;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class ExtractorTest extends TestCase
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

    public function testExtractXlsxSuccessful(): void
    {
        $filePath = 'tests/functional/Sheet/FingersCrossed/source-to-extract.xlsx';
        $helperFactory = new HelperFactory();
        $cachingStrategyFactory = new CachingStrategyFactory();
        $entityFactory = new InternalEntityFactory(
            new ManagerFactory($helperFactory, $cachingStrategyFactory),
            $helperFactory
        );
        $workbookRelasionshipManager = new WorkbookRelationshipsManager($filePath, $entityFactory);
        $xmlReader = new XMLReader();
        $sharedValueManager = new SharedStringsManager(
            $filePath,
            'tmp/',
            $workbookRelasionshipManager,
            $entityFactory,
            $helperFactory,
            $cachingStrategyFactory
        );
        $sharedValueManager->extractSharedStrings();

        $sheet = new Sheet(
            new RowIterator(
                $filePath,
                'xl/worksheets/sheet1.xml',
                true,
                $xmlReader,
                new XMLProcessor($xmlReader),
                new CellValueFormatter(
                    $sharedValueManager,
                    new StyleManager($filePath, $workbookRelasionshipManager, $entityFactory),
                    true,
                    false,
                    new XLSX()
                ),
                new RowManager($entityFactory),
                $entityFactory
            ),
            0,
            'big piece of sheet',
            true,
            true
        );

        $extractor = new Extractor($sheet, 0);

        $result = [];
        foreach ($extractor->extract() as $line) {
            $result[] = $line;
        }

        // TODO: check why extract returns an empty line
        $this->assertEquals(
            [
                [
                    'first name' => 'john',
                    'last name' => 'doe'
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont'
                ],
                [
                    'first name' => null,
                    'last name' => null
                ]
            ],
            $result
        );
    }
}
