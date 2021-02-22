<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\XLSX\Creator\HelperFactory;
use Box\Spout\Reader\XLSX\Creator\InternalEntityFactory;
use Box\Spout\Reader\XLSX\Creator\ManagerFactory;
use Box\Spout\Reader\XLSX\Manager\OptionsManager;
use Box\Spout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use Box\Spout\Reader\XLSX\Reader;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Extractor;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class ExtractorTest extends TestCase
{
    use PipelineAssertTrait;

    private ?FileSystem $fs = null;
    private ?Reader $reader = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();

        $helperFactory = new HelperFactory();
        $managerFactory = new ManagerFactory(
            $helperFactory,
            new CachingStrategyFactory()
        );

        $this->reader = new Reader(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new InternalEntityFactory(
                $managerFactory,
                $helperFactory
            ),
            $managerFactory
        );
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;

        $this->reader = null;
    }

    public function testExtractXlsxSuccessful(): void
    {
        $this->reader->open(__DIR__ . '/../source-to-extract.xlsx');

        $extractor = new Extractor($this->reader, 'Sheet1', 0);

        $this->assertDoesIterateLike(
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
            $extractor->extract()
        );
    }

    public function testExtractEmptySheet(): void
    {
        $this->reader->open(__DIR__ . '/../source-to-extract-empty.xlsx');

        $extractor = new Extractor($this->reader, 'Sheet1', 0);

        $this->assertDoesIterateLike(
            [],
            $extractor->extract()
        );
    }
}
