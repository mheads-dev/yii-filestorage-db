<?php

namespace Mheads\Yii\Filestorage\Db\Tests\Support;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

trait CreateUploadedFileMockTrait
{
	/**
	 * Создает mock UploadedFile с заданным именем и содержимым
	 */
	protected function createUploadedFileMock(string $filename, string $content): UploadedFileInterface
	{
		// Создаем временный файл с содержимым
		$tempFile = tempnam(sys_get_temp_dir(), 'upload_test_');
		file_put_contents($tempFile, $content);

		// Создаем mock для StreamInterface
		$stream = $this->createMock(StreamInterface::class);
		$stream->method('getMetadata')->with('uri')->willReturn($tempFile);

		// Создаем mock для UploadedFileInterface
		$uploadedFile = $this->createMock(UploadedFileInterface::class);
		$uploadedFile->method('getClientFilename')->willReturn($filename);
		$uploadedFile->method('getSize')->willReturn(strlen($content));
		$uploadedFile->method('getClientMediaType')->willReturn('text/plain');
		$uploadedFile->method('getStream')->willReturn($stream);

		// Настраиваем метод moveTo для сохранения файла
		$uploadedFile->method('moveTo')->willReturnCallback(
			static function (string $targetPath) use ($tempFile, $content): void {
				// Создаем директорию, если она не существует
				$dir = dirname($targetPath);
				if(!is_dir($dir))
				{
					mkdir($dir, 0o777, true);
				}

				// Копируем содержимое временного файла в целевую директорию
				copy($tempFile, $targetPath);

				// Удаляем временный файл
				unlink($tempFile);
			},
		);

		return $uploadedFile;
	}
}