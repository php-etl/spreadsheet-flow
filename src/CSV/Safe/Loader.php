<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Pipeline\LoaderInterface;

class Loader implements LoaderInterface
{
    /** @var LoaderInterface */
    private $inner;

    public function __construct(Writer $writer)
    {
        $this->inner = new Sheet\Safe\Loader($writer);
    }

    public function load(): \Generator
    {
        return $this->inner->load();
    }
}
