<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db\Tests\Driver\Pgsql;

use Mheads\Yii\Filestorage\Db\Tests\Driver\Common\DbRepositoryTestCase;
use Mheads\Yii\Filestorage\Db\Tests\Support\PgsqlHelper;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class DbRepositoryTest extends DbRepositoryTestCase
{
	protected static function createConnection(): ConnectionInterface
	{
		return (new PgsqlHelper())->createConnection();
	}
}
