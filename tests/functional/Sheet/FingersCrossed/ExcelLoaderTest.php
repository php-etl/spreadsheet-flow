<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX;
use functional\Kiboko\Component\Flow\Spreadsheet\PipelineAssertTrait;
use functional\Kiboko\Component\Flow\Spreadsheet\Sheet\CustomFunctionsTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Loader;
use PHPUnit\Framework\TestCase;

final class ExcelLoaderTest extends TestCase
{
    use PipelineAssertTrait;
    use CustomFunctionsTrait;

    private ?XLSX\Writer $writer = null;

    protected function setUp(): void
    {
        $this->writer = WriterEntityFactory::createXLSXWriter();
    }

    protected function tearDown(): void
    {
        $this->writer = null;
    }

    public function testLoad()
    {
        $this->writer->openToFile(__DIR__ . '/../data/users-loaded.xlsx');

        $this->assertPipelineDoesLoadLike(
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

        $dataFile1 = $this->getDataFromFile(__DIR__ . '/../data/users.xlsx', 'xlsx', 'fingersCrossed');
        $dataFile2 = $this->getDataFromFile(__DIR__ . '/../data/users-loaded.xlsx', 'xlsx', 'fingersCrossed');

        $this->assertArraySimilar(iterator_to_array($dataFile1), iterator_to_array($dataFile2));
    }
}
