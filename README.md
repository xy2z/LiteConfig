# LiteConfig

A lightweight PHP static class with zero dependencies.

Supports multiple configs and multidimensional arrays.

Built-in support for PHP, INI and JSON files.


## Requirements
- PHP 7.0 +


## Examples

```php
require 'path/to/vendor/autoload.php';
use xy2z\LiteConfig\LiteConfig as Config;
```

#### Array
```php
Config::loadArray([
  'version' => '1.0',
  'app' => [
      'name' => 'Example'
  ]
]);

echo Config::get('version'); # 1.0
echo Config::get('app'); # Array('name' => 'Example')
echo Config::get('app.name'); # Example
```

#### Directory
```php
# config/settings.php
return [
  'app_name' => 'Example',
];

# index.php
Config::loadDir('config/', true);
echo Config::get('settings.app_name'); # key is 'filename.key'
```

#### Single file
```php
# No key prefix
Config::loadFile('config/settings.php');
echo Config::get('key');

# Prefix filename to key
Config::loadFile('config/db.ini', true);
echo Config::get('db.key');
```


## Methods
- `get(string $key)` Get value of key.
- `all()` Returns a complete array.
- `exists(string $key)` Does key exist?
- `loadDir(string $path, bool $prefix_filename = false, string $prefix = null)` Loads all files in dir.
- `loadFile(string $path, bool $prefix_filename = false, string $custom_prefix = null)` Load a single file.
- `loadArray(array $array, string $prefix = null)` Loads a php array.
