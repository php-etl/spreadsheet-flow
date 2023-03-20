<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\CSV\Writer;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineRunner;
use Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed\Loader;
use Kiboko\Component\PHPUnitExtension\Assert\LoaderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

/**
 * @internal
 *
 * @coversNothing
 */
final class LoaderTest extends TestCase
{
    use LoaderAssertTrait;

    private ?FileSystem $fs = null;
    private ?Writer $writer = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();

        $this->writer = WriterEntityFactory::createCSVWriter();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;

        $this->writer = null;
    }

    /**
     * @test
     */
    public function loadCsvSuccessful(): void
    {
        $this->writer->openToFile('vfs://test.csv');

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

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
