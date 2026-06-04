# Internals

## PHPUnit

Run the default test suite:

```shell
./vendor/bin/phpunit
```

Run a specific DB suite:

```shell
./vendor/bin/phpunit --testsuite Sqlite
```

Docker-backed suites are available through `make`:

```shell
make test-mysql
make test-pgsql
make test-mssql
make test-sqlite
make test-oracle
```

## Static Analysis

```shell
./vendor/bin/psalm --no-cache
```

## Code Style

```shell
./vendor/bin/php-cs-fixer fix --dry-run --diff
```
