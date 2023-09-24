<?php

// This should be called using "phpunit" command.


namespace xy2z\LiteConfigTests;

use PHPUnit\Framework\TestCase;
use xy2z\LiteConfig\LiteConfig;

class LiteConfigTest extends TestCase {

	private array $original_array = [
		'app' => 'MyApp',
		'url' => 'myapp.com',
		'build' => 137,
	];

	public function setUp(): void {

	}

	public function tearDown(): void {
		LiteConfig::resetData();
		@unlink(__DIR__ . '/config.json');
		@unlink(__DIR__ . '/conf.json');
		@unlink(__DIR__ . '/config.php');
		@unlink(__DIR__ . '/config.ini');
		@unlink(__DIR__ . '/config/app.json');
		@rmdir(__DIR__ . '/config');
	}

	/**
	 * Test get methods
	 */
	public function test_load_array() {
		LiteConfig::loadArray($this->original_array);;
		$this->assertTrue(true);
		$this->check(__FUNCTION__);
	}

	public function test_load_array_multi() {
		LiteConfig::loadArray(['multi' => $this->original_array]);
		$this->assertTrue(true);
		$this->check(__FUNCTION__, 'multi');
	}


	public function test_load_array_prefix() {
		LiteConfig::loadArray($this->original_array, 'conf');
		$this->check(__FUNCTION__, 'conf');
	}

	public function test_load_file_ini() {
		// Load file with file without any prefixes
		// Ini format
		$ini_content = '';
		foreach ($this->original_array as $key => $value) {
			$ini_content .= 'conf[' . $key . ']=' . $value . PHP_EOL;
		}
		file_put_contents(__DIR__ . '/config.ini', $ini_content . PHP_EOL);
		$this->assertFileExists(__DIR__ . '/config.ini');
		LiteConfig::loadFile(__DIR__ . '/config.ini');
		$this->check(__FUNCTION__, 'conf');
	}

	public function test_load_file_ini_multi() {
		// Load file with file without any prefixes
		// Ini format
		$ini_content = '';
		foreach ($this->original_array as $key => $value) {
			$ini_content .= 'multi[' . $key . ']=' . $value . PHP_EOL;
		}
		file_put_contents(__DIR__ . '/config.ini', $ini_content . PHP_EOL);
		$this->assertFileExists(__DIR__ . '/config.ini');
		LiteConfig::loadFile(__DIR__ . '/config.ini');
		$this->check(__FUNCTION__, 'multi');
	}

	public function test_load_file_custom_prefix_php() {
		// Load file with file prefix without file prefix, but with custom prefix
		file_put_contents(__DIR__ . '/config.php', '<?php return ' . var_export($this->original_array, true) . ';');
		$this->assertFileExists(__DIR__ . '/config.php');
		LiteConfig::loadFile(__DIR__ . '/config.php', false, 'conf');
		$this->check(__FUNCTION__, 'conf');
	}

	public function test_load_file_with_file_prefix_json() {
		// Load file with file prefix, but no custom prefix
		file_put_contents(__DIR__ . '/conf.json', json_encode($this->original_array));
		$this->assertFileExists(__DIR__ . '/conf.json');
		LiteConfig::loadFile(__DIR__ . '/conf.json', true);
		$this->check(__FUNCTION__, 'conf');
	}

	public function test_load_file_with_file_prefix_and_custom_prefix() {
		// Load file with both file prefix and custom prefix.
		file_put_contents(__DIR__ . '/config.json', json_encode($this->original_array));
		$this->assertFileExists(__DIR__ . '/config.json');
		LiteConfig::loadFile(__DIR__ . '/config.json', true, 'double');
		$this->check(__FUNCTION__, 'double.config');
	}

	public function test_load_dir_no_prefix() {
		mkdir(__DIR__ . '/config');
		file_put_contents(__DIR__ . '/config/app.json', json_encode($this->original_array));

		$this->assertDirectoryExists(__DIR__ . '/config');
		$this->assertFileExists(__DIR__ . '/config/app.json');

		// LiteConfig tests
		LiteConfig::loadDir(__DIR__ . '/config');
		$this->check(__FUNCTION__);

		// Cleanup
		unlink(__DIR__ . '/config/app.json');
		rmdir(__DIR__ . '/config');
	}

	public function test_load_dir_with_file_prefix() {
		mkdir(__DIR__ . '/config');
		file_put_contents(__DIR__ . '/config/app.json', json_encode($this->original_array));

		$this->assertDirectoryExists(__DIR__ . '/config');
		$this->assertFileExists(__DIR__ . '/config/app.json');

		// LiteConfig tests
		LiteConfig::loadDir(__DIR__ . '/config', true);
		$this->check(__FUNCTION__, 'app');

		// Cleanup
		unlink(__DIR__ . '/config/app.json');
		rmdir(__DIR__ . '/config');
	}

	public function test_load_dir_with_custom_prefix() {
		mkdir(__DIR__ . '/config');
		file_put_contents(__DIR__ . '/config/app.json', json_encode($this->original_array));

		$this->assertDirectoryExists(__DIR__ . '/config');
		$this->assertFileExists(__DIR__ . '/config/app.json');

		// LiteConfig tests
		LiteConfig::loadDir(__DIR__ . '/config', false, 'custom');
		$this->check(__FUNCTION__, 'custom');

		// Cleanup
		unlink(__DIR__ . '/config/app.json');
		rmdir(__DIR__ . '/config');
	}

	public function test_load_dir_with_file_prefix_and_custom_prefix() {
		mkdir(__DIR__ . '/config');
		file_put_contents(__DIR__ . '/config/app.json', json_encode($this->original_array));

		$this->assertDirectoryExists(__DIR__ . '/config');
		$this->assertFileExists(__DIR__ . '/config/app.json');

		// LiteConfig tests
		LiteConfig::loadDir(__DIR__ . '/config', true, 'custom');
		$this->check(__FUNCTION__, 'custom.app');

		// Cleanup
		unlink(__DIR__ . '/config/app.json');
		rmdir(__DIR__ . '/config');
	}

	public function test_array_indexes() {
		// Test `.0`, `.1`, etc. indexes
		LiteConfig::loadArray([
			'numbers' => [100, 200, 300],
		], 'conf');
		$this->assertSame(LiteConfig::get('conf.numbers'), [100, 200, 300]);
		$this->assertCount(4, LiteConfig::get('conf'));
		$this->assertSame(LiteConfig::get('conf.numbers.0'), 100);
		$this->assertNotSame(LiteConfig::get('conf.numbers.0'), '100');
		$this->assertSame(LiteConfig::get('conf.numbers.1'), 200);
		$this->assertSame(LiteConfig::get('conf.numbers.2'), 300);
		$this->assertNull(LiteConfig::get('conf.numbers.3'));

		$this->assertTrue(LiteConfig::exists('conf'));
		$this->assertTrue(LiteConfig::exists('conf.numbers'));
		$this->assertTrue(LiteConfig::exists('conf.numbers.0'));
		$this->assertTrue(LiteConfig::exists('conf.numbers.1'));
		$this->assertTrue(LiteConfig::exists('conf.numbers.2'));
		$this->assertFalse(LiteConfig::exists('conf.numbers.3'));
	}

	public function test_reset_data() {
		LiteConfig::loadArray(['numbers' => [100, 200, 300],]);
		$this->assertNotEmpty(LiteConfig::all());
		LiteConfig::resetData();
		$this->assertEmpty(LiteConfig::all());
	}

	private function check(string $name, ?string $prefix = null) {
		$prefix_dot = ''; // default.

		if ($prefix) {
			$prefix_dot = $prefix_dot = $prefix . '.';
			$this->assertSame(LiteConfig::get($prefix), $this->original_array, $name . ' get("' . $prefix . '")');
			$this->assertCount(3, LiteConfig::get($prefix));
			$this->assertEquals(LiteConfig::all(), [
				$prefix => $this->original_array,
				$prefix_dot . 'app' => 'MyApp',
				$prefix_dot . 'url' => 'myapp.com',
				$prefix_dot . 'build' => 137,
			], $name . ' all()');
		} else {
			// No prefix
			$this->assertEquals(LiteConfig::all(), $this->original_array);
			$this->assertCount(3, LiteConfig::all());
		}

		$this->assertSame(LiteConfig::get($prefix_dot . 'app'), 'MyApp');
		$this->assertSame(LiteConfig::get($prefix_dot . 'url'), 'myapp.com');
		$this->assertSame(LiteConfig::get($prefix_dot . 'build'), 137);
		$this->assertNotSame(LiteConfig::get($prefix_dot . 'build'), '137');

		$this->assertNull(LiteConfig::get($prefix_dot . 'undefined'));
		$this->assertNull(LiteConfig::get($prefix_dot . 'undefined.foo'));
		$this->assertNull(LiteConfig::get($prefix_dot . 'undefined.foo.bar'));
		$this->assertSame(LiteConfig::get($prefix_dot . 'undefined_default', ['default' => 'value']), ['default' => 'value']);
		$this->assertSame(LiteConfig::get($prefix_dot . 'undefined_default.foo', 'default-value'), 'default-value');
		$this->assertSame(LiteConfig::get($prefix_dot . 'undefined_default.foo.bar', 'default-value'), 'default-value');

		$this->assertNull(LiteConfig::get('invalid'));
		$this->assertNull(LiteConfig::get('invalid.foo'));
		$this->assertNull(LiteConfig::get('invalid.foo.bar'));
		$this->assertSame(LiteConfig::get('invalid', 'default-value'), 'default-value');
		$this->assertSame(LiteConfig::get('invalid.foo', 'default-value'), 'default-value');
		$this->assertSame(LiteConfig::get('invalid.foo.bar', 'default-value'), 'default-value');


		// Exists
		if ($prefix) {
			$this->assertTrue(LiteConfig::exists($prefix));
		}

		$this->assertTrue(LiteConfig::exists($prefix_dot . 'app'));
		$this->assertTrue(LiteConfig::exists($prefix_dot . 'url'));

		$this->assertFalse(LiteConfig::exists($prefix_dot . 'undefined'));
		$this->assertFalse(LiteConfig::exists($prefix_dot . 'undefined.foo'));
		$this->assertFalse(LiteConfig::exists($prefix_dot . 'undefined.foo.bar'));
		$this->assertFalse(LiteConfig::exists($prefix_dot . 'undefined_default'));
		$this->assertFalse(LiteConfig::exists($prefix_dot . 'undefined_default.foo'));
		$this->assertFalse(LiteConfig::exists($prefix_dot . 'undefined_default.foo.bar'));

		$this->assertFalse(LiteConfig::exists('invalid'));
		$this->assertFalse(LiteConfig::exists('invalid.foo'));
		$this->assertFalse(LiteConfig::exists('invalid.foo.bar'));
		$this->assertFalse(LiteConfig::exists('invalid'));
		$this->assertFalse(LiteConfig::exists('invalid.foo'));
		$this->assertFalse(LiteConfig::exists('invalid.foo.bar'));
	}

}
