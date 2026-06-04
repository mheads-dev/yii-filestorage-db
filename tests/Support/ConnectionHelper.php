<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db\Tests\Support;

use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Test\Support\SimpleCache\MemorySimpleCache;

abstract class ConnectionHelper
{
	protected function createSchemaCache(): SchemaCache
	{
		return new SchemaCache(
			new MemorySimpleCache(),
		);
	}
}
