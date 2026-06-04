<?php

declare(strict_types=1);

use App\Examples\Support\UploadedFileFactory;
use Mheads\Yii\Filestorage\Db\DbRepository;
use Mheads\Yii\Filestorage\Storage;
use Mheads\Yii\Filestorage\StorageProvider;
use Mheads\Yii\Filestorage\Store\FileSystem\PrivateFileSystemStore;
use Mheads\Yii\Filestorage\Store\FileSystem\PublicFileSystemStore;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/support/UploadedFileFactory.php';
require __DIR__ . '/support/getDbConnection.php';

$db = getDbConnection();
$repository = new DbRepository($db);

$publicRoot = __DIR__ . '/runtime/mixed-public';
$privateRoot = __DIR__ . '/runtime/mixed-private';

if(!is_dir($publicRoot)) {
    mkdir($publicRoot, 0o777, true);
}
if(!is_dir($privateRoot)) {
    mkdir($privateRoot, 0o777, true);
}

$storage = new Storage(
    repository: $repository,
    stores: [
        new PublicFileSystemStore(
            name: 'public',
            path: $publicRoot,
            baseUrl: 'https://cdn.example.com/mixed',
        ),
        new PrivateFileSystemStore(
            name: 'private',
            path: $privateRoot,
        ),
    ],
    defaultStoreName: 'public',
);

StorageProvider::set($storage);

$sourcePublic = __DIR__ . '/runtime/source-mixed-public.txt';
$sourcePrivate = __DIR__ . '/runtime/source-mixed-private.txt';
file_put_contents($sourcePublic, "Public document\n");
file_put_contents($sourcePrivate, "Private document\n");

$publicFile = $storage->add(
    uploadedFile: UploadedFileFactory::fromLocalPath($sourcePublic, 'public.txt', 'text/plain'),
    groupName: 'docs',
    storeName: 'public',
    description: 'Public document',
);

$privateFile = $storage->add(
    uploadedFile: UploadedFileFactory::fromLocalPath($sourcePrivate, 'private.txt', 'text/plain'),
    groupName: 'docs',
    storeName: 'private',
    description: 'Private document',
);

printf("Public file id=%d, url=%s\n", (int)$publicFile->getId(), (string)$publicFile->getUrl());
printf("Private file id=%d, url=%s\n", (int)$privateFile->getId(), var_export($privateFile->getUrl(), true));

$foundPublic = $storage->findById((int)$publicFile->getId());
$foundPrivate = $storage->findById((int)$privateFile->getId());
printf("Found public by id: %s\n", $foundPublic?->getOriginalName() ?? 'not found');
printf("Found private by id: %s\n", $foundPrivate?->getOriginalName() ?? 'not found');
