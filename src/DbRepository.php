<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db;

use DateTimeImmutable;
use Mheads\Yii\Filestorage\Db\FileTable as FT;
use Mheads\Yii\Filestorage\Entity\File;
use Mheads\Yii\Filestorage\Entity\FileInterface;
use Mheads\Yii\Filestorage\Exception\AddException;
use Mheads\Yii\Filestorage\Exception\FindException;
use Mheads\Yii\Filestorage\Exception\InvalidConfigException;
use Mheads\Yii\Filestorage\Exception\RemoveException;
use Mheads\Yii\Filestorage\Helper;
use Mheads\Yii\Filestorage\Repository\RepositoryInterface;
use Override;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidCallException;

use function is_a;
use function is_array;
use function is_scalar;
use function preg_replace;
use function sprintf;
use function str_contains;
use function strval;
use function trim;

/**
 * Repository implementation over yiisoft/db connection.
 *
 * Supported DB drivers:
 * - mysql
 * - pgsql
 * - mssql
 * - sqlite
 * - oci
 *
 * @psalm-type DbFileRow = array{
 *     id: ?string,
 *     store_name: string,
 *     external_id: ?string,
 *     group_name: string,
 *     relative_path: ?string,
 *     original_name: string,
 *     height: ?int,
 *     width: ?int,
 *     file_size: ?int,
 *     content_type: ?string,
 *     description: ?string,
 *     created_at: ?int,
 *     updated_at: ?int
 * }
 */
final class DbRepository implements RepositoryInterface
{
	public const string DEFAULT_TABLE_NAME = FT::TABLE;
	private ?string $oracleSequenceName = null;

	/**
	 * @param class-string<FileInterface> $fileClass
	 * @throws InvalidConfigException
	 */
	public function __construct(
		private readonly ConnectionInterface $db,
		private readonly string $tableName = self::DEFAULT_TABLE_NAME,
		private readonly string $fileClass = File::class,
	) {
		if(!is_a($this->fileClass, FileInterface::class, true))
		{
			throw new InvalidConfigException(
				sprintf(
					'File class must implement %s, %s given',
					FileInterface::class,
					$this->fileClass,
				),
			);
		}
	}

	#[Override]
	public function findById(int|string $id): ?FileInterface
	{
		try
		{
			$row = $this->db
				->createQuery()
				->from($this->tableName)
				->where([FT::COL_ID => $id])
				->one();

			if(!is_array($row))
			{
				return null;
			}

			return $this->createFileFromArray($this->normalizeRow($row));
		}
		catch(FindException $e)
		{
			throw $e;
		}
		catch(Throwable $e)
		{
			throw new FindException('Failed to find file: ' . $e->getMessage(), 0, $e);
		}
	}

	#[Override]
	public function add(FileInterface $file): int|string
	{
		try
		{
			if($file->getId() !== null)
			{
				throw new AddException('File already added');
			}

			$this->db->createCommand()->insert(
				$this->tableName,
				$this->convertFileToDbArray($file),
			)->execute();

			$id = $this->resolveLastInsertId();
			if($id === '')
			{
				throw new AddException('Database did not return valid inserted id');
			}

			$file->assignId($id);

			return $id;
		}
		catch(AddException $e)
		{
			throw $e;
		}
		catch(Throwable $e)
		{
			throw new AddException('Failed to save file: ' . $e->getMessage(), 0, $e);
		}
	}

	#[Override]
	public function remove(FileInterface $file): void
	{
		try
		{
			$id = $file->getId();
			if($id === null)
			{
				throw new RemoveException('Cannot remove file without ID');
			}

			$deleted = $this->db
				->createCommand()
				->delete($this->tableName, [FT::COL_ID => $id])
				->execute();

			if($deleted === 0)
			{
				throw new RemoveException("File with id {$id} not found");
			}
		}
		catch(RemoveException $e)
		{
			throw $e;
		}
		catch(Throwable $e)
		{
			throw new RemoveException('Failed to remove file: ' . $e->getMessage(), 0, $e);
		}
	}

	#[Override]
	public function createFromUploadedFile(
		UploadedFileInterface $uploadedFile,
		string $groupName,
		string $storeName,
		?string $description = null,
	): FileInterface {
		return Helper::createFileFromUploadedFile(
			$uploadedFile,
			$groupName,
			$storeName,
			$this->fileClass,
			$description,
		);
	}

	/**
	 * @return array{
	 *     store_name: string,
	 *     external_id: ?string,
	 *     group_name: string,
	 *     relative_path: ?string,
	 *     original_name: string,
	 *     height: ?int,
	 *     width: ?int,
	 *     file_size: ?int,
	 *     content_type: ?string,
	 *     description: ?string,
	 *     created_at: ?int,
	 *     updated_at: ?int
	 * }
	 */
	private function convertFileToDbArray(FileInterface $file): array
	{
		return [
			FT::COL_STORE_NAME    => $file->getStoreName(),
			FT::COL_EXTERNAL_ID   => $file->getExternalId(),
			FT::COL_GROUP_NAME    => $file->getGroupName(),
			FT::COL_RELATIVE_PATH => $file->getRelativePath(),
			FT::COL_ORIGINAL_NAME => $file->getOriginalName(),
			FT::COL_HEIGHT        => $file->getHeight(),
			FT::COL_WIDTH         => $file->getWidth(),
			FT::COL_FILE_SIZE     => $file->getFileSize(),
			FT::COL_CONTENT_TYPE  => $file->getContentType(),
			FT::COL_DESCRIPTION   => $file->getDescription(),
			FT::COL_CREATED_AT    => $file->getCreatedAt()?->getTimestamp(),
			FT::COL_UPDATED_AT    => $file->getUpdatedAt()?->getTimestamp(),
		];
	}

	/**
	 * @throws InvalidCallException
	 * @throws Exception
	 * @throws Throwable
	 */
	private function resolveLastInsertId(): int|string
	{
		$driverName = $this->db->getDriverName();

		if($driverName === 'pgsql')
		{
			$sequenceName = $this->resolvePgsqlSequenceName();
			return $this->db->getLastInsertId($sequenceName);
		}

		if($driverName === 'oci')
		{
			$sequenceName = $this->resolveOracleSequenceName();
			return $this->db->getLastInsertId($sequenceName);
		}

		return $this->db->getLastInsertId();
	}

	private function resolvePgsqlSequenceName(): string
	{
		$table = preg_replace('/["`]/', '', $this->tableName) ?? $this->tableName;
		if(str_contains($table, '.'))
		{
			$parts = explode('.', $table, 2);
			$schema = $parts[0];
			$tableName = $parts[1] ?? '';
			return $schema . '.' . $tableName . '_' . FT::COL_ID . '_seq';
		}

		return $table . '_' . FT::COL_ID . '_seq';
	}

	/**
	 * @throws \Yiisoft\Db\Exception\InvalidConfigException
	 * @throws Throwable
	 * @throws Exception
	 * @throws InvalidCallException
	 */
	private function resolveOracleSequenceName(): string
	{
		if($this->oracleSequenceName !== null)
		{
			return $this->oracleSequenceName;
		}

		$table = preg_replace('/["`]/', '', $this->tableName) ?? $this->tableName;
		if(str_contains($table, '.'))
		{
			$parts = explode('.', $table, 2);
			$table = $parts[1] ?? $table;
		}

		$sequenceName = $this->db->createQuery()
			->select('SEQUENCE_NAME')
			->from('USER_TAB_IDENTITY_COLS')
			->where(['TABLE_NAME' => $table])
			->scalar();
		if(is_scalar($sequenceName) && trim((string)$sequenceName) !== '')
		{
			$this->oracleSequenceName = (string)$sequenceName;
			return $this->oracleSequenceName;
		}

		$sequenceName = strtoupper($table) . '_SEQ';
		$exists = $this->db->createQuery()
			->from('USER_SEQUENCES')
			->where(['SEQUENCE_NAME' => $sequenceName])
			->exists();

		if($exists)
		{
			$this->oracleSequenceName = $sequenceName;
			return $this->oracleSequenceName;
		}

		throw new InvalidCallException('Unable to resolve Oracle identity sequence name.');
	}

	/**
	 * @param array<array-key, mixed> $row
	 * @return DbFileRow
	 * @throws FindException
	 */
	private function normalizeRow(array $row): array
	{
		$storeName = $this->requireNonEmptyString(
			$this->asString($row[FT::COL_STORE_NAME] ?? ''),
			FT::COL_STORE_NAME,
		);
		$groupName = $this->requireNonEmptyString(
			$this->asString($row[FT::COL_GROUP_NAME] ?? ''),
			FT::COL_GROUP_NAME,
		);
		$originalName = $this->requireNonEmptyString(
			$this->asString($row[FT::COL_ORIGINAL_NAME] ?? ''),
			FT::COL_ORIGINAL_NAME,
		);

		return [
			FT::COL_ID            => $this->asNullableString($row[FT::COL_ID] ?? null),
			FT::COL_STORE_NAME    => $storeName,
			FT::COL_EXTERNAL_ID   => $this->asNullableString($row[FT::COL_EXTERNAL_ID] ?? null),
			FT::COL_GROUP_NAME    => $groupName,
			FT::COL_RELATIVE_PATH => $this->asNullableString($row[FT::COL_RELATIVE_PATH] ?? null),
			FT::COL_ORIGINAL_NAME => $originalName,
			FT::COL_HEIGHT        => $this->asNullableInt($row[FT::COL_HEIGHT] ?? null),
			FT::COL_WIDTH         => $this->asNullableInt($row[FT::COL_WIDTH] ?? null),
			FT::COL_FILE_SIZE     => $this->asNullableInt($row[FT::COL_FILE_SIZE] ?? null),
			FT::COL_CONTENT_TYPE  => $this->asNullableString($row[FT::COL_CONTENT_TYPE] ?? null),
			FT::COL_DESCRIPTION   => $this->asNullableString($row[FT::COL_DESCRIPTION] ?? null),
			FT::COL_CREATED_AT    => $this->asNullableInt($row[FT::COL_CREATED_AT] ?? null),
			FT::COL_UPDATED_AT    => $this->asNullableInt($row[FT::COL_UPDATED_AT] ?? null),
		];
	}

	/**
	 * @param DbFileRow $data
	 */
	private function createFileFromArray(array $data): FileInterface
	{
		/** @var class-string<FileInterface> $fileClass */
		$fileClass = $this->fileClass;
		$file = new $fileClass();

		if($data[FT::COL_ID] !== null)
		{
			$file->assignId($data[FT::COL_ID]);
		}

		$file->setStoreName($data[FT::COL_STORE_NAME]);
		$file->setExternalId($data[FT::COL_EXTERNAL_ID]);
		$file->setGroupName($data[FT::COL_GROUP_NAME]);
		$file->setRelativePath($data[FT::COL_RELATIVE_PATH]);
		$file->setOriginalName($data[FT::COL_ORIGINAL_NAME]);
		$file->setHeight($data[FT::COL_HEIGHT]);
		$file->setWidth($data[FT::COL_WIDTH]);
		$file->setFileSize($data[FT::COL_FILE_SIZE]);
		$file->setContentType($data[FT::COL_CONTENT_TYPE]);
		$file->setDescription($data[FT::COL_DESCRIPTION]);

		if($data[FT::COL_CREATED_AT] !== null)
		{
			$createdAt = DateTimeImmutable::createFromFormat('U', (string)$data[FT::COL_CREATED_AT]);
			$file->setCreatedAt($createdAt === false ? null : $createdAt);
		}
		if($data[FT::COL_UPDATED_AT] !== null)
		{
			$updatedAt = DateTimeImmutable::createFromFormat('U', (string)$data[FT::COL_UPDATED_AT]);
			$file->setUpdatedAt($updatedAt === false ? null : $updatedAt);
		}

		return $file;
	}

	private function asString(mixed $value): string
	{
		return is_scalar($value) ? strval($value) : '';
	}

	private function asNullableString(mixed $value): ?string
	{
		if($value === null)
		{
			return null;
		}

		return is_scalar($value) ? strval($value) : null;
	}

	private function asNullableInt(mixed $value): ?int
	{
		if($value === null)
		{
			return null;
		}

		return is_scalar($value) ? (int)$value : null;
	}

	/**
	 * @throws FindException
	 */
	private function requireNonEmptyString(string $value, string $column): string
	{
		if(trim($value) === '')
		{
			throw new FindException("Invalid value for required column \"{$column}\".");
		}

		return $value;
	}
}
