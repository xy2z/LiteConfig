<?php

require '../src/LiteConfig.php';

// use xy2z\Liteconfig\Liteconfig as Config;
use xy2z\Liteconfig\LiteConfig as Config;

$start = microtime(true);


# PHP array
// Config::loadArray([
// 	'app_name' => 'Example',
// ]);

# LoadFile
// Config::loadFile('config/conf.php', true);
// Config::loadFile('config/conf.ini', true);
// Config::loadFile('conf.json', true);

# loadDir
// Config::loadDir('config', true);

# JSON
// $json = file_get_contents('conf.json');
// Config::loadArray(json_decode($json, true));

# INI
// Config::loadArray(parse_ini_file('conf.ini', true));


Config::loadArray([
  'version' => 1.5,
  'app' => [
      'name' => 'Example'
  ]
]);

var_dump(Config::get('version')); # 1.0
var_dump(Config::get('app')); # Array('name' => 'Example')
var_dump(Config::get('app.name')); # Example


# RESULT
echo PHP_EOL . '------------' . PHP_EOL;
var_dump(
	Config::get('conf.app_name')
);

echo PHP_EOL;
var_dump(microtime(true) - $start);
