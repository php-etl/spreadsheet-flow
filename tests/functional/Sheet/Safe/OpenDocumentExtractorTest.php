<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\ODS;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Extractor;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class OpenDocumentExtractorTest extends TestCase
{
    use PipelineAssertTrait;

    private ?FileSystem $fs = null;
    private ?ODS\Reader $reader = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();

        $helperFactory = new ODS\Creator\HelperFactory();
        $managerFactory = new ODS\Creator\ManagerFactory();

        $this->reader = new ODS\Reader(
            new ODS\Manager\OptionsManager(),
            new GlobalFunctionsHelper(),
            new ODS\Creator\InternalEntityFactory(
                $helperFactory,
                $managerFactory,
            )
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
        $this->reader->open(__DIR__ . '/../data/users.ods');

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

    public function testExtractFileSkippingLines(): void
    {
        $this->reader->open(__DIR__ . '/../data/users-with-2-empty-headers.ods');

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
            ],
            $extractor->extract()
        );
    }

    public function testExtractEmptyFile(): void
    {
        $this->reader->open(__DIR__ . '/../data/empty-file.ods');

        $extractor = new Extractor($this->reader, 'Sheet1', 0);

        $this->assertDoesIterateLike(
            [],
            $extractor->extract()
        );
    }
}
