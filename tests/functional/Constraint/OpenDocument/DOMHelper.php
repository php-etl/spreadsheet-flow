<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Constraint\OpenDocument;

final class DOMHelper
{
    public static function toArray(\DOMNode $node)
    {
        return iterator_to_array(self::rowValuesToArray($node), false);
    }

    public static function rowValuesToArray(\DOMNode $node): \Iterator
    {
        /** @var \DOMNode $node */
        foreach ($node->childNodes as $node) {
            if ($node->nodeName !== 'table:table-row') {
                continue;
            }

            yield iterator_to_array(self::cellValuesToArray($node), false);
        }
    }

    public static function cellValuesToArray(\DOMNode $row): \Iterator
    {
        /** @var \DOMNode $node */
        foreach ($row->childNodes as $node) {
            if ($node->nodeName !== 'table:table-cell') {
                continue;
            }

            yield $node->textContent;
        }
    }
}
