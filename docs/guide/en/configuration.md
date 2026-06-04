# Configuration

`DbRepository` needs a configured `Yiisoft\Db\Connection\ConnectionInterface`.
The `Storage` facade and filesystem stores are provided by `mheads/yii-filestorage`.

## Manual Configuration

```php
use Mheads\Yii\Filestorage\Db\DbRepository;
use Mheads\Yii\Filestorage\Storage;
use Mheads\Yii\Filestorage\StorageProvider;
use Mheads\Yii\Filestorage\Store\FileSystem\PublicFileSystemStore;

$repository = new DbRepository($db);

$storage = new Storage(
    repository: $repository,
    stores: [
        new PublicFileSystemStore(
            name: 'upload',
            path: '/app/runtime/upload',
            baseUrl: 'https://cdn.example.com/upload',
        ),
    ],
    defaultStoreName: 'upload',
    defaultGroupName: 'common',
);

StorageProvider::set($storage);
```

`StorageProvider::set()` is needed when code calls `$file->getUrl()`, `$file->getContent()`, or `$file->getResource()` on file objects.
If your code only calls `$storage->getUrl($file)` and similar `Storage` methods, registering the provider is not required.

## DI Configuration

Example Yii config array:

```php
<?php

use Mheads\Yii\Filestorage\Db\DbRepository;
use Mheads\Yii\Filestorage\Repository\RepositoryInterface;
use Mheads\Yii\Filestorage\Storage;
use Mheads\Yii\Filestorage\StorageInterface;
use Mheads\Yii\Filestorage\Store\FileSystem\PrivateFileSystemStore;
use Mheads\Yii\Filestorage\Store\FileSystem\PublicFileSystemStore;

return [
    RepositoryInterface::class => [
        'class' => DbRepository::class,
    ],
    StorageInterface::class => [
        'class' => Storage::class,
        '__construct()' => [
            'stores' => [
                new PublicFileSystemStore(
                    name: 'upload',
                    path: dirname(__DIR__, 3) . '/public/upload',
                    baseUrl: '/upload',
                ),
                new PrivateFileSystemStore(
                    name: 'private',
                    path: dirname(__DIR__, 3) . '/runtime/private-upload',
                ),
            ],
            'defaultStoreName' => 'upload',
            'defaultGroupName' => 'common',
        ],
    ],
];
```

Bootstrap the storage provider only if file objects need direct access to storage methods:

```php
<?php

use Mheads\Yii\Filestorage\StorageInterface;
use Mheads\Yii\Filestorage\StorageProvider;
use Psr\Container\ContainerInterface;

return [
    static function (ContainerInterface $container): void {
        StorageProvider::set($container->get(StorageInterface::class));
    },
];
```

## Custom Table or File Class

The `DbRepository` constructor accepts a custom table name and file class:

```php
$repository = new DbRepository(
    db: $db,
    tableName: 'app_file',
    fileClass: AppFile::class,
);
```

The custom file class must implement `Mheads\Yii\Filestorage\Entity\FileInterface`.
