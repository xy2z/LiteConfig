# LiteConfig

A lightweight PHP static class with zero dependencies.

Supports multiple configs and multidimensional arrays.

Built-in support for PHP, INI and JSON files.

Supports YAML and anything else you can parse to an array, see below.


## Requirements
- PHP 7.0 +

## Install
```bash
composer require xy2z/lite-config
```

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
echo Config::get('app.name', 'default name'); # Example
echo Config::get('app.type', 'Desktop'); # Desktop
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

#### YAML
To load a single file as YAML see below. If you need to use `loadDir()` with yaml (or other) files, then read about "Custom Handler" below.

```bash
composer require symfony/yaml
```

```php
use xy2z\LiteConfig\LiteConfig as Config;
use Symfony\Component\Yaml\Yaml;

Config::loadArray(Yaml::parseFile(__DIR__ . '/config/file.yml'));

echo Config::get('key');
```

#### Custom Handler
A custom handler can be used for file extensions other than the built in (.php, .json and .ini). This will automatically work when using the `loadFile()` and `loadDir()` functions.

Here's how you use the static `custom_handler()` function if you want YAML support.

```php
use xy2z\LiteConfig\LiteConfig;
use Symfony\Component\Yaml\Yaml;

class CustomLiteConfig extends LiteConfig {

    protected static function custom_handler(string $extension, string $path) {
        if (($extension === 'yml') || ($extension === 'yaml')) {
            return Yaml::parseFile($path);
        }

        // Handle other extensions here...
    }

}

CustomLiteConfig::loadDir(__DIR__ . '/config', true);
var_dump(CustomLiteConfig::all());
```

If you want to modify the existing handling of all files, you can overwrite the complete `getFileContent()` function in your child class, which is used by `loadDir()` and `loadFile()`.


## Public Methods
- `get(string $key, $default = null)` Get value of key.
- `all()` Returns a complete array.
- `exists(string $key)` Does key exist?
- `loadDir(string $path, bool $prefix_filename = false, string $prefix = null)` Loads all files in dir.
- `loadFile(string $path, bool $prefix_filename = false, string $custom_prefix = null)` Load a single file.
- `loadArray(array $array, string $prefix = null)` Loads a php array.
