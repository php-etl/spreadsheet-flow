<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\FingersCrossed;

use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Pipeline\LoaderInterface;

class Loader implements LoaderInterface
{
    private LoaderInterface $inner;

    public function __construct(Writer $writer)
    {
        $this->inner = new Sheet\FingersCrossed\Loader($writer);
    }

    public function load(): \Generator
    {
        return $this->inner->load();
    }
}
