<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db\Tests\Support;

class ModelFactory
{
	public static function create(array $rows): array
	{
		$models = [];

		foreach ($rows as $row)
		{
			$class = $row['type'];

			$model = new $class();
			$model->populateRecord($row);

			$models[] = $model;
		}

		return $models;
	}
}
