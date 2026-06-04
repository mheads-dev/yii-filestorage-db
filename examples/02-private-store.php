<?php

declare(strict_types=1);

use App\Examples\Support\UploadedFileFactory;
use Mheads\Yii\Filestorage\Db\DbRepository;
use Mheads\Yii\Filestorage\Storage;
use Mheads\Yii\Filestorage\StorageProvider;
use Mheads\Yii\Filestorage\Store\FileSystem\PrivateFileSystemStore;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/support/UploadedFileFactory.php';
require __DIR__ . '/support/getDbConnection.php';

$db = getDbConnection();
$repository = new DbRepository($db);

$privateRoot = __DIR__ . '/runtime/private-upload';
if(!is_dir($privateRoot)) {
    mkdir($privateRoot, 0o777, true);
}

$storage = new Storage(
    repository: $repository,
    stores: [
        new PrivateFileSystemStore(
            name: 'private',
            path: $privateRoot,
        ),
    ],
    defaultStoreName: 'private',
);

StorageProvider::set($storage);

$sourcePath = __DIR__ . '/runtime/source-private.txt';
file_put_contents($sourcePath, "Hello from private store example.\n");

$file = $storage->add(
    uploadedFile: UploadedFileFactory::fromLocalPath($sourcePath, 'demo-private.txt', 'text/plain'),
    groupName: 'internal',
    description: 'Private demo file',
);

printf("Saved id: %d\n", (int)$file->getId());
printf("Relative path: %s\n", (string)$file->getRelativePath());
printf("URL is null for private store: %s\n", var_export($file->getUrl(), true));
printf("Loaded content: %s\n", (string)$file->getContent());

$found = $storage->findById((int)$file->getId());
printf("Found by id: %s\n", $found?->getOriginalName() ?? 'not found');
