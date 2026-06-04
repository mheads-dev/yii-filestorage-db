<?php

declare(strict_types=1);

namespace Mheads\Yii\Filestorage\Db\Tests;

use Mheads\Yii\Filestorage\Db\Tests\Support\DbHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Connection\ConnectionProvider;
use Yiisoft\Db\Migration\Command\DownCommand;
use Yiisoft\Db\Migration\Command\UpdateCommand;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\Migrator;
use Yiisoft\Db\Migration\Runner\DownRunner;
use Yiisoft\Db\Migration\Runner\UpdateRunner;
use Yiisoft\Db\Migration\Service\MigrationService;
use Yiisoft\Injector\Injector;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
	private bool $shouldReloadFixture = false;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		$db = static::createConnection();
		ConnectionProvider::set($db);
		self::runMigrations();
		DbHelper::loadFixture($db);
	}

	public static function tearDownAfterClass(): void
	{
		self::rollbackMigrations();
		ConnectionProvider::get()->close();
		ConnectionProvider::remove();

		parent::tearDownAfterClass();
	}

	protected function tearDown(): void
	{
		if ($this->shouldReloadFixture)
		{
			$this->reloadFixture();
			$this->shouldReloadFixture = false;
		}

		parent::tearDown();
	}

	abstract protected static function createConnection(): ConnectionInterface;


	private static function createUpdateCommand(): UpdateCommand
	{
		$migrator = self::createMigrator();
		$migrationService = self::createMigrationService($migrator);
		return new UpdateCommand(new UpdateRunner($migrator), $migrationService, $migrator);
	}

	private static function createDownCommand(): DownCommand
	{
		$migrator = self::createMigrator();
		$migrationService = self::createMigrationService($migrator);
		return new DownCommand(new DownRunner($migrator), $migrationService, $migrator);
	}

	private static function createMigrator(): Migrator
	{
		return new Migrator(self::db(), new NullMigrationInformer());
	}

	private static function createMigrationService(Migrator $migrator): MigrationService
	{
		$migrationService = new MigrationService(
			self::db(),
			new Injector(),
			$migrator,
		);
		$migrationService->setSourcePaths([self::migrationPath()]);

		return $migrationService;
	}

	private static function migrationPath(): string
	{
		return dirname(__DIR__) . '/migrations';
	}

	private static function runMigrations(): void
	{
		$input = new ArrayInput([
			'--path'      => [self::migrationPath()],
			'--force-yes' => true,
		]);
		$input->setInteractive(false);

		self::createUpdateCommand()->run($input, new NullOutput());
	}

	private static function rollbackMigrations(): void
	{
		$input = new ArrayInput([
			'--path'      => [self::migrationPath()],
			'--all'       => true,
			'--force-yes' => true,
		]);
		$input->setInteractive(false);

		self::createDownCommand()->run($input, new NullOutput());
	}

	/**
	 * Call this method in tests which modifies the database state to reload the connection and fixture after the tests.
	 */
	protected function reloadFixtureAfterTest(): void
	{
		$this->shouldReloadFixture = true;
	}

	protected static function reloadFixture(): void
	{
		ConnectionProvider::get()->close();

		$db = static::createConnection();
		ConnectionProvider::set($db);

		DbHelper::loadFixture($db);
	}

	protected static function db(): ConnectionInterface
	{
		return ConnectionProvider::get();
	}
}
