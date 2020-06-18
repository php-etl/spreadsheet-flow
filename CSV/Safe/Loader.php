<?php

namespace Kiboko\Component\ETL\Flow\Spout\CSV\Safe;

use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\ETL\Contracts\LoaderInterface;
use Kiboko\Component\ETL\Flow\Spout\Sheet;

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
