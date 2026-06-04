<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

final class M260421000001CreateFileStorage implements RevertibleMigrationInterface
{
	private const string TABLE = 'mh_filestorage_file';
	private const string ORACLE_SEQUENCE = 'MH_FILESTORAGE_FILE_SEQ';
	private const string ORACLE_TRIGGER = 'MH_FILESTORAGE_FILE_BI';

	public function up(MigrationBuilder $b): void
	{
		$cb = $b->columnBuilder();
		$driverName = $b->getDb()->getDriverName();
		$idColumn = $driverName === 'oci'
			? $cb::integer()->notNull()
			: $cb::bigPrimaryKey();
		if($driverName === 'mysql')
		{
			$idColumn = $idColumn->unsigned();
		}

		$b->createTable(self::TABLE, [
			'id'            => $idColumn,
			'store_name'    => $cb::string(255)->notNull(),
			'external_id'   => $cb::string(1000),
			'group_name'    => $cb::string(255)->notNull(),
			'relative_path' => $cb::string(1000),
			'original_name' => $cb::string(1000)->notNull(),
			'height'        => $cb::bigint(),
			'width'         => $cb::bigint(),
			'file_size'     => $cb::bigint(),
			'content_type'  => $cb::string(255),
			'description'   => $cb::string(1000),
			'updated_at'    => $cb::bigint(),
			'created_at'    => $cb::bigint(),
		]);

		if($driverName === 'oci')
		{
			$b->getDb()->createCommand(
				'ALTER TABLE "' . self::TABLE . '" ADD CONSTRAINT "mh_filestorage_file_PK" PRIMARY KEY ("id")',
			)->execute();
			$b->getDb()->createCommand('CREATE SEQUENCE "' . self::ORACLE_SEQUENCE . '"')->execute();
			$b->getDb()->createCommand(
				'CREATE OR REPLACE TRIGGER "' . self::ORACLE_TRIGGER . '" '
				. 'BEFORE INSERT ON "' . self::TABLE . '" FOR EACH ROW '
				. 'BEGIN '
				. '<<COLUMN_SEQUENCES>> '
				. 'BEGIN '
				. 'IF INSERTING AND :NEW."id" IS NULL THEN '
				. 'SELECT "' . self::ORACLE_SEQUENCE . '".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; '
				. 'END IF; '
				. 'END COLUMN_SEQUENCES; '
				. 'END;',
			)->execute();
		}

		$b->createIndex(self::TABLE, 'idx_mh_filestorage_file_store_name', 'store_name');
		$b->createIndex(self::TABLE, 'idx_mh_filestorage_file_group_name', 'group_name');
	}

	public function down(MigrationBuilder $b): void
	{
		if($b->getDb()->getDriverName() === 'oci')
		{
			$b->getDb()->createCommand(
				'BEGIN EXECUTE IMMEDIATE \'DROP TRIGGER "' . self::ORACLE_TRIGGER
				. '"\'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -4080 THEN RAISE; END IF; END;',
			)->execute();
			$b->getDb()->createCommand(
				'BEGIN EXECUTE IMMEDIATE \'DROP SEQUENCE "' . self::ORACLE_SEQUENCE
				. '"\'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;',
			)->execute();
		}

		$b->dropTable(self::TABLE);
	}
}
