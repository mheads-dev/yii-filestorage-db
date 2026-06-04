# Errors and Exceptions

`DbRepository` uses core package exceptions:

- `Mheads\Yii\Filestorage\Exception\AddException`
- `Mheads\Yii\Filestorage\Exception\FindException`
- `Mheads\Yii\Filestorage\Exception\RemoveException`
- `Mheads\Yii\Filestorage\Exception\InvalidConfigException`

Recommended handling:

- Catch `AddException` in upload flows.
- Catch `FindException` when metadata lookup is part of a user request.
- Catch `RemoveException` in delete flows if missing metadata should be reported.
- Treat `InvalidConfigException` as a setup error and fail fast.

`DbRepository` wraps lower-level database exceptions into these repository-level exceptions.
