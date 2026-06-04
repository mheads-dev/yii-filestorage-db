# mheads/yii-filestorage-db

Database metadata repository adapter for [`mheads/yii-filestorage`](https://github.com/mheads-dev/yii-filestorage).

The package provides `DbRepository`, a `RepositoryInterface` implementation that stores file metadata in a database through `yiisoft/db`.
Physical file storage remains the responsibility of the core package stores, for example `PublicFileSystemStore` and `PrivateFileSystemStore`.

Use it when you want the core `Storage` facade with persistent database-backed file metadata.

## Installation

```shell
composer require mheads/yii-filestorage-db
```

Also install the database driver used by your project:

```shell
composer require yiisoft/db-mysql
```

Apply the migration for `mh_filestorage_file` before adding files:

- [Migrations](docs/guide/en/migrations.md)

For full requirements and supported drivers, see [Prerequisites and installation](docs/guide/en/prerequisites-and-installation.md).

## Quick Start

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

```php
$file = $storage->add($uploadedFile, groupName: 'products');

$url = $file->getUrl();

$sameFile = $storage->findById($file->getId());
if($sameFile !== null) {
    $storage->remove($sameFile);
}
```

`StorageProvider::set()` is required only when file objects call `getUrl()`, `getContent()`, or `getResource()` directly.
If you do not register the provider, call these methods through `Storage` instead.

## Documentation

- [Guide](docs/guide/en/README.md)
- [Internals](docs/internals.md)
- [Examples](examples/README.md)
