# Prerequisites and Installation

## Requirements

- PHP 8.3 - 8.5.
- `mheads/yii-filestorage`.
- `yiisoft/db` `^2.0.1`.

## Supported Databases

Install the matching database driver:

- MySQL: `yiisoft/db-mysql`.
- PostgreSQL: `yiisoft/db-pgsql`.
- MSSQL: `yiisoft/db-mssql`.
- SQLite: `yiisoft/db-sqlite`.
- Oracle: `yiisoft/db-oracle`.

## Installation

Install the adapter:

```shell
composer require mheads/yii-filestorage-db
```

Install your database driver, for example:

```shell
composer require yiisoft/db-mysql
```

Optional, if you apply package migrations through `yiisoft/db-migration`, install it as a development dependency:

```shell
composer require --dev yiisoft/db-migration
```

## Next Steps

- [Migrations](migrations.md)
- [Configuration](configuration.md)
- [Repository](repositories.md)
