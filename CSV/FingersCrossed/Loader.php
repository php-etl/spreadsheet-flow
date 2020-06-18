<?php

namespace Kiboko\Component\ETL\Flow\Spout\CSV\FingersCrossed;

use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\ETL\Contracts\LoaderInterface;
use Kiboko\Component\ETL\Flow\Spout\Sheet;

class Loader implements LoaderInterface
{
    /** @var LoaderInterface */
    private $inner;

    public function __construct(Writer $writer)
    {
        $this->inner = new Sheet\FingersCrossed\Loader($writer);
    }

    public function load(): \Generator
    {
        return $this->inner->load();
    }
}
