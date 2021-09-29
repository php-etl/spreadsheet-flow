<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\ODS;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Extractor;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Vfs\FileSystem;

final class OpenDocumentExtractorTest extends TestCase
{
    use ExtractorAssertTrait;

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

        $this->assertExtractorExtractsLike(
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
            $extractor
        );
    }

    public function testExtractFileSkippingLines(): void
    {
        $this->reader->open(__DIR__ . '/../data/users-with-2-headers.ods');

        $extractor = new Extractor($this->reader, 'Sheet1', 2);

        $this->assertExtractorExtractsLike(
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
            $extractor
        );
    }

    public function testExtractEmptyFile(): void
    {
        $this->reader->open(__DIR__ . '/../data/empty-file.ods');

        $extractor = new Extractor($this->reader, 'Sheet1', 0);

        $this->assertExtractorExtractsLike(
            [],
            $extractor
        );
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new \Kiboko\Component\Pipeline\PipelineRunner(
            new NullLogger()
        );
    }
}
