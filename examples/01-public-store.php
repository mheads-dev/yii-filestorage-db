<?php

declare(strict_types=1);

use App\Examples\Support\UploadedFileFactory;
use Mheads\Yii\Filestorage\Db\DbRepository;
use Mheads\Yii\Filestorage\Storage;
use Mheads\Yii\Filestorage\StorageProvider;
use Mheads\Yii\Filestorage\Store\FileSystem\PublicFileSystemStore;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/support/UploadedFileFactory.php';
require __DIR__ . '/support/getDbConnection.php';

$db = getDbConnection();
$repository = new DbRepository($db);

$publicRoot = __DIR__ . '/runtime/public-upload';
if(!is_dir($publicRoot)) {
    mkdir($publicRoot, 0o777, true);
}

$storage = new Storage(
    repository: $repository,
    stores: [
        new PublicFileSystemStore(
            name: 'public',
            path: $publicRoot,
            baseUrl: 'https://cdn.example.com/uploads',
        ),
    ],
    defaultStoreName: 'public',
);

StorageProvider::set($storage);

$sourcePath = __DIR__ . '/runtime/source-public.txt';
file_put_contents($sourcePath, "Hello from public store example.\n");

$file = $storage->add(
    uploadedFile: UploadedFileFactory::fromLocalPath($sourcePath, 'demo-public.txt', 'text/plain'),
    groupName: 'docs',
    description: 'Public demo file',
);

printf("Saved id: %d\n", (int)$file->getId());
printf("Relative path: %s\n", (string)$file->getRelativePath());
printf("Public URL: %s\n", (string)$file->getUrl());

$found = $storage->findById((int)$file->getId());
printf("Found by id: %s\n", $found?->getOriginalName() ?? 'not found');
