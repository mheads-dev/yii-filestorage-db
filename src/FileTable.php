<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db;

final class FileTable
{
	public const string TABLE = 'mh_filestorage_file';

	public const string COL_ID = 'id';
	public const string COL_STORE_NAME = 'store_name';
	public const string COL_EXTERNAL_ID = 'external_id';
	public const string COL_GROUP_NAME = 'group_name';
	public const string COL_RELATIVE_PATH = 'relative_path';
	public const string COL_ORIGINAL_NAME = 'original_name';
	public const string COL_HEIGHT = 'height';
	public const string COL_WIDTH = 'width';
	public const string COL_FILE_SIZE = 'file_size';
	public const string COL_CONTENT_TYPE = 'content_type';
	public const string COL_DESCRIPTION = 'description';
	public const string COL_UPDATED_AT = 'updated_at';
	public const string COL_CREATED_AT = 'created_at';
}

