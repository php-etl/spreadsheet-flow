<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\CSV\Creator\InternalEntityFactory;
use Box\Spout\Reader\CSV\Manager\OptionsManager;
use Box\Spout\Reader\CSV\Reader;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineRunner;
use Kiboko\Component\Flow\Spreadsheet\CSV\Safe\Extractor;
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
final class ExtractorTest extends TestCase
{
    use ExtractorAssertTrait;

    private ?vfsStreamDirectory $fs = null;
    private ?Reader $reader = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();

        $this->reader = new Reader(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new InternalEntityFactory(
                new HelperFactory()
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
    public function extractCSVFile(): void
    {
        $this->reader->open(__DIR__.'/../data/users.csv');

        $extractor = new Extractor($this->reader, 0);

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
            ],
            $extractor
        );
    }

    /**
     * @test
     */
    public function extractEmptyCSVFile(): void
    {
        $this->reader->open(__DIR__.'/../data/empty-file.csv');

        $extractor = new Extractor($this->reader, 0);

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
