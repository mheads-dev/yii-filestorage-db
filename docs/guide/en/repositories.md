# Repository

`DbRepository` stores file metadata with `yiisoft/db` and implements `Mheads\Yii\Filestorage\Repository\RepositoryInterface` from the core package.

```php
use Mheads\Yii\Filestorage\Db\DbRepository;

$repository = new DbRepository($db);
```

Use it when you need persistent metadata storage and do not want to write a custom repository.

## Constructor

```php
public function __construct(
    ConnectionInterface $db,
    string $tableName = DbRepository::DEFAULT_TABLE_NAME,
    string $fileClass = File::class,
)
```

Constructor parameters:

- `$db`: `Yiisoft\Db\Connection\ConnectionInterface`.
- `$tableName`: database table used for file metadata. Defaults to `mh_filestorage_file`.
- `$fileClass`: class used when files are created or loaded. It must implement `FileInterface`.

## Behavior

- `findById()` reads a row and creates a file object.
- `add()` inserts metadata and assigns the generated ID to the file object.
- `remove()` deletes metadata by file ID.
- `createFromUploadedFile()` delegates file object creation to the core package helper.

Physical file writes and removals are handled by `Storage` and configured stores, not by `DbRepository` directly.
