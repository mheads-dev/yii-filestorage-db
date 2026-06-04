<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Pgsql\Connection;
use Yiisoft\Db\Pgsql\Driver;

use function getenv;

final class PgsqlHelper extends ConnectionHelper
{
	public function createConnection(): ConnectionInterface
	{
		$database = getenv('YII_PGSQL_DATABASE') ?: 'mhfs-test';
		$host = getenv('YII_PGSQL_HOST') ?: '127.0.0.1';
		$port = getenv('YII_PGSQL_PORT') ?: '5432';
		$user = getenv('YII_PGSQL_USER') ?: 'yii';
		$password = getenv('YII_PGSQL_PASSWORD') ?: 'q1w2e3r4';

		$pdoDriver = new Driver("pgsql:host=$host;dbname=$database;port=$port", $user, $password);

		return new Connection($pdoDriver, $this->createSchemaCache());
	}
}
