<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db\Tests\Driver\Common;

use Mheads\Yii\Filestorage\Db\DbRepository;
use Mheads\Yii\Filestorage\Db\FileTable;
use Mheads\Yii\Filestorage\Db\Tests\Support\CreateUploadedFileMockTrait;
use Mheads\Yii\Filestorage\Db\Tests\Support\DbHelper;
use Mheads\Yii\Filestorage\Db\Tests\TestCase;
use Mheads\Yii\Filestorage\Entity\File;
use Mheads\Yii\Filestorage\Exception\AddException;
use Mheads\Yii\Filestorage\Exception\FindException;
use Mheads\Yii\Filestorage\Exception\InvalidConfigException;
use Mheads\Yii\Filestorage\Exception\RemoveException;
use stdClass;

use function sprintf;

abstract class DbRepositoryTestCase extends TestCase
{
	use CreateUploadedFileMockTrait;

	public function testAddFindAndRemove(): void
	{
		$repository = new DbRepository(self::db());

		$uploadedFile = $this->createUploadedFileMock(
			'image.png',
			'file-content',
		);

		$file = $repository->createFromUploadedFile(
			$uploadedFile,
			'avatars',
			'upload',
			'Avatar',
		);

		$id = $repository->add($file);
		self::assertGreaterThan(0, $id);
		self::assertSame($id, $file->getId());

		$found = $repository->findById($id);
		self::assertNotNull($found);
		self::assertSame('upload', $found->getStoreName());
		self::assertSame('avatars', $found->getGroupName());
		self::assertSame('image.png', $found->getOriginalName());
		self::assertSame('Avatar', $found->getDescription());
		self::assertSame('text/plain', $found->getContentType());

		$repository->remove($found);
		self::assertNull($repository->findById($id));
	}

	public function testAddThrowsWhenFileAlreadyHasIdWithoutWrapping(): void
	{
		$repository = new DbRepository(self::db());
		$file = new File();
		$file->assignId(1);
		$file->setStoreName('upload');
		$file->setGroupName('avatars');
		$file->setOriginalName('image.png');

		try
		{
			$repository->add($file);
			self::fail('AddException expected.');
		}
		catch(AddException $e)
		{
			self::assertSame('File already added', $e->getMessage());
			self::assertNull($e->getPrevious());
		}
	}

	public function testRemoveThrowsWhenFileHasNoIdWithoutWrapping(): void
	{
		$repository = new DbRepository(self::db());
		$file = new File();
		$file->setStoreName('upload');
		$file->setGroupName('avatars');
		$file->setOriginalName('image.png');

		try
		{
			$repository->remove($file);
			self::fail('RemoveException expected.');
		}
		catch(RemoveException $e)
		{
			self::assertSame('Cannot remove file without ID', $e->getMessage());
			self::assertNull($e->getPrevious());
		}
	}

	public function testConstructThrowsWhenFileClassIsInvalid(): void
	{
		$this->expectException(InvalidConfigException::class);
		new DbRepository(self::db(), fileClass: stdClass::class);
	}

	public function testAddWrapsDbException(): void
	{
		$repository = new DbRepository(self::db(), 'missing_table');
		$file = new File();
		$file->setStoreName('upload');
		$file->setGroupName('avatars');
		$file->setOriginalName('image.png');

		try
		{
			$repository->add($file);
			self::fail('AddException expected.');
		}
		catch(AddException $e)
		{
			self::assertNotNull($e->getPrevious());
		}
	}

	public function testFindByIdThrowsWhenRequiredColumnIsInvalid(): void
	{
		self::db()->createCommand()->insert(DbRepository::DEFAULT_TABLE_NAME, [
			FileTable::COL_STORE_NAME    => '   ',
			FileTable::COL_GROUP_NAME    => 'avatars',
			FileTable::COL_ORIGINAL_NAME => 'image.png',
		])->execute();

		$sql = sprintf(
			'SELECT MAX([[%s]]) FROM [[%s]]',
			FileTable::COL_ID,
			DbRepository::DEFAULT_TABLE_NAME,
		);
		$id = (int)self::db()
			->createCommand(DbHelper::replaceQuotes($sql, self::db()->getDriverName()))
			->queryScalar();

		$repository = new DbRepository(self::db());
		$this->expectException(FindException::class);
		$this->expectExceptionMessage(FileTable::COL_STORE_NAME);
		$repository->findById($id);
	}
}
