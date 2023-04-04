<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\ODS;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineRunner;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Extractor;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OpenDocumentExtractorTest extends TestCase
{
    use ExtractorAssertTrait;

    private ?vfsStreamDirectory $fs = null;
    private ?ODS\Reader $reader = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();

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
        $this->fs = null;
        vfsStreamWrapper::unregister();

        $this->reader = null;
    }

    /**
     * @test
     */
    public function extractFile(): void
    {
        $this->reader->open(__DIR__.'/../data/users.ods');

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

    /**
     * @test
     */
    public function extractFileSkippingLines(): void
    {
        $this->reader->open(__DIR__.'/../data/users-with-2-headers.ods');

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

    /**
     * @test
     */
    public function extractEmptyFile(): void
    {
        $this->reader->open(__DIR__.'/../data/empty-file.ods');

        $extractor = new Extractor($this->reader, 'Sheet1', 0);

        $this->assertExtractorExtractsLike(
            [],
            $extractor
        );
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
