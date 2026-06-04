<?php

declare(strict_types=1);

use Yiisoft\Db\Connection\ConnectionInterface;

function getDbConnection(): ConnectionInterface
{
    throw new RuntimeException('Provide ConnectionInterface from your application container.');
}
