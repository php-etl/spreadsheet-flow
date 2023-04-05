<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\CSV\Writer;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineRunner;
use Kiboko\Component\Flow\Spreadsheet\CSV\Safe\Loader;
use Kiboko\Component\PHPUnitExtension\Assert\LoaderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 *
 * @coversNothing
 */
final class LoaderTest extends TestCase
{
    use LoaderAssertTrait;

    private ?vfsStreamDirectory $fs = null;
    private ?Writer $writer = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();

        $this->writer = WriterEntityFactory::createCSVWriter();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
        vfsStreamWrapper::unregister();

        $this->writer = null;
    }

    /**
     * @test
     */
    public function loadCsvSuccessful(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');

        $this->writer->openToFile($path);

        $this->assertLoaderLoadsLike(
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
            new Loader($this->writer)
        );
    }

    /**
     * @test
     */
    public function loadCsvWithLogger(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');

        $this->writer->openToFile($path);

        $this->assertLoaderLoadsLike(
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
            new Loader($this->writer, new NullLogger())
        );

        $this->assertFileEquals(__DIR__.'/../data/users.csv', $path);
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
