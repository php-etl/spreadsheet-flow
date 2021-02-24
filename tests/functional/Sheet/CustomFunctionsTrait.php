<?php


namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Extractor as FingersCrossedExtractor;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Extractor as SafeExtractor;

trait CustomFunctionsTrait
{
    public function getDataFromFile($file, $format = 'xlsx', $extractorType = 'safe'): iterable {
        $reader = match ($format) {
            'xlsx' => ReaderEntityFactory::createXLSXReader(),
            'ods' => ReaderEntityFactory::createODSReader(),
        };

        $extractor = match ($extractorType) {
            'safe' => new SafeExtractor($reader, 'Sheet1', 0),
            'fingersCrossed' => new FingersCrossedExtractor($reader, 'Sheet1', 0),
        };

        $reader->open($file);

        return $extractor->extract();
    }

    public function assertArraySimilar(array $expected, array $array)
    {
        $this->assertEquals([], array_diff_key($array, $expected));

        foreach ($expected as $key => $value) {
            if (is_array($value)) {
                $this->assertArraySimilar($value, $array[$key]);
            } else {
                $this->assertContains($value, $array);
            }
        }
    }
}
