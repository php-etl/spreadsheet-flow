<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Constraint\Excel;

final class DOMHelper
{
    public static function toArray(\DOMNode $node)
    {
        return iterator_to_array(self::rowValuesToArray($node), false);
    }

    public static function rowValuesToArray(\DOMNode $node): \Iterator
    {
        $rows = $node->childNodes[0];
        /** @var \DOMNode $node */
        foreach ($rows->childNodes as $node) {
            if ($node->nodeName !== 'row') {
                continue;
            }

            yield iterator_to_array(self::cellValuesToArray($node), false);
        }
    }

    public static function cellValuesToArray(\DOMNode $row): \Iterator
    {
        /** @var \DOMNode $node */
        foreach ($row->childNodes as $node) {
            if ($node->nodeName !== 'c') {
                continue;
            }

            yield $node->textContent;
        }
    }
}
