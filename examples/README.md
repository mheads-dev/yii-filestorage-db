# Examples

`examples/*` are reference/demo scripts for `mheads/yii-filestorage-db`.
In real projects, adapt DB connection, paths, bootstrap, and launch method to your runtime.

## Example Map

| Scenario | File |
| --- | --- |
| Public store + DB repository | [01-public-store.php](01-public-store.php) |
| Private store + DB repository | [02-private-store.php](02-private-store.php) |
| Public and private stores in one storage | [03-public-and-private.php](03-public-and-private.php) |

## Preparation

1. Prepare `ConnectionInterface` from your application DI container.
2. Apply migration for `mh_filestorage_file`:
   - [../migrations/M260421000001CreateFileStorage.php](../migrations/M260421000001CreateFileStorage.php)
3. Helper [support/UploadedFileFactory.php](support/UploadedFileFactory.php) requires `httpsoft/http-message`.
4. Create file storage directories, or keep the directory creation code from the samples.

## How to Run in Your Project

1. Copy the needed example to your project.
2. Implement `getDbConnection()` in [support/getDbConnection.php](support/getDbConnection.php) using your project database connection pattern.
3. Run the script via your PHP launch flow.

Important: samples are not intended to run directly from `vendor/.../examples` without path and environment adaptation.

## Minimal `Connection` Example

This snippet additionally requires `yiisoft/cache`.

```php
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Mysql\Driver;
use Yiisoft\Db\Mysql\Dsn;

function getDbConnection(): Connection
{
    return new Connection(
        new Driver(
            new Dsn('mysql', 'db', 'app', '3306'),
            'user',
            'supersecretpassword',
        ),
        new SchemaCache(new ArrayCache()),
    );
}
```
