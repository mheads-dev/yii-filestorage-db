<?php

declare(strict_types=1);

namespace App\Examples\Support;

use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

use function basename;
use function file_exists;
use function filesize;
use function is_readable;
use function mime_content_type;

final class UploadedFileFactory
{
    public static function fromLocalPath(
        string $path,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
    ): UploadedFileInterface {
        if(!class_exists(UploadedFile::class) || !class_exists(StreamFactory::class)) {
            throw new RuntimeException(
                'Package "httpsoft/http-message" is required for examples/support/UploadedFileFactory.php',
            );
        }

        if(!file_exists($path) || !is_readable($path)) {
            throw new RuntimeException("File \"{$path}\" not found or not readable.");
        }

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromFile($path, 'rb');

        return new UploadedFile(
            $stream,
            (int)filesize($path),
            UPLOAD_ERR_OK,
            $clientFilename ?? basename($path),
            $clientMediaType ?? (mime_content_type($path) ?: 'application/octet-stream'),
        );
    }
}
