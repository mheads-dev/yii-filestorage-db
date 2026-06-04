<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

use function getenv;

final class SqliteHelper extends ConnectionHelper
{
	public function createConnection(): ConnectionInterface
	{
		$path = getenv('YII_SQLITE_PATH') ?: '/tmp/mhfs-test.sqlite';
		$pdoDriver = new Driver("sqlite:$path");
		$pdoDriver->charset('UTF8');

		return new Connection($pdoDriver, $this->createSchemaCache());
	}
}
