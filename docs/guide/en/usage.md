# Usage with Storage

This page assumes that `Storage` is already configured with `DbRepository`.
See [Configuration](configuration.md) for setup.

Add, read, and remove files through `Storage`:

```php
$file = $storage->add(
    uploadedFile: $uploadedFile,
    groupName: 'products',
    storeName: 'upload',
    description: 'Product image',
);

$same = $storage->findById($file->getId());
$url = $same === null ? null : $storage->getUrl($same);

if($same !== null) {
    $storage->remove($same);
}
```

For direct file-object methods, register the storage provider first:

```php
StorageProvider::set($storage);

$url = $file->getUrl();
$content = $file->getContent();
$resource = $file->getResource();
```

See reference examples:

- [Public store + DB repository](../../../examples/01-public-store.php)
- [Private store + DB repository](../../../examples/02-private-store.php)
- [Public and private stores](../../../examples/03-public-and-private.php)
