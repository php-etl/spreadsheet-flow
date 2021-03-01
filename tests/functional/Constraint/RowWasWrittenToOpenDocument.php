<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

final class RowWasWrittenToOpenDocument extends Constraint
{
    private static $zipErrors = [
        \ZipArchive::ER_OK => 'Ok',
        \ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
        \ZipArchive::ER_RENAME => 'Renaming temporary file failed',
        \ZipArchive::ER_CLOSE => 'Closing zip archive failed',
        \ZipArchive::ER_SEEK => 'Seek error',
        \ZipArchive::ER_READ => 'Read error',
        \ZipArchive::ER_WRITE => 'Write error',
        \ZipArchive::ER_CRC => 'CRC error',
        \ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
        \ZipArchive::ER_NOENT => 'No such file',
        \ZipArchive::ER_EXISTS => 'File already exists',
        \ZipArchive::ER_OPEN => 'Can\'t open file',
        \ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
        \ZipArchive::ER_ZLIB => 'Zlib error',
        \ZipArchive::ER_MEMORY => 'Memory allocation failure',
        \ZipArchive::ER_CHANGED => 'Entry has been changed',
        \ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
        \ZipArchive::ER_EOF => 'Premature EOF',
        \ZipArchive::ER_INVAL => 'Invalid argument',
        \ZipArchive::ER_NOZIP => 'Not a zip archive',
        \ZipArchive::ER_INTERNAL => 'Internal error',
        \ZipArchive::ER_INCONS => 'Zip archive inconsistent',
        \ZipArchive::ER_REMOVE => 'Can\'t remove file',
        \ZipArchive::ER_DELETED => 'Entry has been deleted',
        \ZipArchive::ER_ENCRNOTSUPP => 'Encryption method not support',
        \ZipArchive::ER_RDONLY => 'Read-only archive',
        \ZipArchive::ER_NOPASSWD => 'No password provided',
        \ZipArchive::ER_WRONGPASSWD => 'Wrong password provided',
        \ZipArchive::ER_OPNOTSUPP => 'Operation not supported',
        \ZipArchive::ER_INUSE => 'Resource still in use',
        \ZipArchive::ER_TELL => 'Tell error',
        \ZipArchive::ER_COMPRESSED_DATA => 'Compressed data invalid',
        \ZipArchive::ER_CANCELLED => 'Operation cancelled',
    ];

    public function __construct(private Constraint $constraint, private string $sheet)
    {}

    private function toFailureString(int $failureCode): string
    {
        return self::$zipErrors[$failureCode];
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return \sprintf('OpenDocument file with written row %s', $this->constraint->toString());
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        $zip = new \ZipArchive();
        if (true !== ($failureCode = $zip->open($other, \ZipArchive::RDONLY))) {
            $this->fail($other, sprintf('could not open zip file, got %s', $this->toFailureString($failureCode)));
        }
        $stream = $zip->getStream('content.xml');
        if (false === ($xml = \simplexml_load_string(\stream_get_contents($stream)))) {
            $this->fail($other, 'could not read contents');
        }
        $sheets = $xml->xpath(sprintf('//table:table[@table:name="%s"]', $this->sheet));

        if (count($sheets) !== 1) {
            $this->fail($other, 'Multiple sheets were found, there seems to be an issue in the file');
        }

        return $this->constraint->matches(OpenDocument\DOMHelper::toArray(\dom_import_simplexml($sheets[0])));
    }
}
