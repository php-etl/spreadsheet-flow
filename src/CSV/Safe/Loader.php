<?php

namespace Kiboko\Component\Flow\Spreadsheet\CSV\Safe;

use Box\Spout\Writer\CSV\Writer;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Component\Flow\Spreadsheet\Sheet;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;

class Loader implements LoaderInterface, FlushableInterface
{
    private LoaderInterface $inner;

    public function __construct(Writer $writer)
    {
        $this->inner = new Sheet\Safe\Loader($writer);
    }

    public function load(): \Generator
    {
        return $this->inner->load();
    }

    public function flush(): ResultBucketInterface
    {
        $this->inner->flush();

        return new EmptyResultBucket();
    }
}
