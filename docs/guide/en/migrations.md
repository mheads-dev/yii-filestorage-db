# Migrations

`DbRepository` stores metadata in `mh_filestorage_file` by default.

Migration file:

- [M260421000001CreateFileStorage.php](../../../migrations/M260421000001CreateFileStorage.php)

## Wiring into `yiisoft/db-migration`

Add the installed package migrations path to your migration config:

```php
'yiisoft/db-migration' => [
    'sourcePaths' => [
        dirname(__DIR__, 2) . '/vendor/mheads/yii-filestorage-db/migrations',
    ],
],
```

Or copy/reference the migration from your centralized migration directory.

## Table

Default table name: `mh_filestorage_file`.

Columns:

- `id`
- `store_name`
- `external_id`
- `group_name`
- `relative_path`
- `original_name`
- `height`
- `width`
- `file_size`
- `content_type`
- `description`
- `created_at`
- `updated_at`

Indexes are created for `store_name` and `group_name`.

## Oracle

For Oracle, the migration also creates sequence `MH_FILESTORAGE_FILE_SEQ` and trigger `MH_FILESTORAGE_FILE_BI`.
Make sure both are applied; otherwise inserted IDs cannot be resolved correctly.
