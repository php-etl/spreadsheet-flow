<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\XLSX;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Extractor;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class ExcelExtractorTest extends TestCase
{
    use PipelineAssertTrait;

    private ?FileSystem $fs = null;
    private ?XLSX\Reader $reader = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();

        $helperFactory = new XLSX\Creator\HelperFactory();
        $managerFactory = new XLSX\Creator\ManagerFactory(
            $helperFactory,
            new XLSX\Manager\SharedStringsCaching\CachingStrategyFactory()
        );

        $this->reader = new XLSX\Reader(
            new XLSX\Manager\OptionsManager(),
            new GlobalFunctionsHelper(),
            new XLSX\Creator\InternalEntityFactory(
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

    public function testExtractFile(): void
    {
        $this->reader->open(__DIR__ . '/../data/users.xlsx');

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
                [
                    'first name' => 'amanda',
                    'last name' => 'cole',
                ],
                [
                    'first name' => 'bernard',
                    'last name' => 'durand',
                ],
            ],
            $extractor->extract()
        );
    }

    public function testExtractFileSkippingLines(): void
    {
        $this->reader->open(__DIR__ . '/../data/users-with-2-headers.xlsx');

        $extractor = new Extractor($this->reader, 'Sheet1', 2);

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
                [
                    'first name' => 'amanda',
                    'last name' => 'cole',
                ],
                [
                    'first name' => 'bernard',
                    'last name' => 'durand',
                ],
            ],
            $extractor->extract()
        );
    }

    public function testExtractEmptyFile(): void
    {
        $this->reader->open(__DIR__ . '/../data/empty-file.xlsx');

        $extractor = new Extractor($this->reader, 'Sheet1', 0);

        $this->assertDoesIterateLike(
            [],
            $extractor->extract()
        );
    }
}
