<?php

// This should be called using "phpunit" command.


namespace xy2z\LiteConfigTests;

use PHPUnit\Framework\TestCase;
use xy2z\LiteConfig\LiteConfig;

class LiteConfigTest extends TestCase {

	/**
	 * Test get methods
	 */
	public function testArray() {
		LiteConfig::loadArray([
			'app' => 'MyApp',
			'url' => 'myapp.com',
		]);


		// All
		$this->assertSame(LiteConfig::all(), [
			'app' => 'MyApp',
			'url' => 'myapp.com',
		]);

		// Get existing key
		$this->assertSame(LiteConfig::get('app'), 'MyApp');

		// Get undefined key
		$this->assertSame(LiteConfig::get('undefined'), null);

		// Get undefined key with default
		$this->assertSame(LiteConfig::get('undefined', 1), 1);

		// Exists
		$this->assertTrue(LiteConfig::exists('app'));

		// Doesn't exist
		$this->assertFalse(LiteConfig::exists('undefined'));

		// =======================================================
		// Last:
		// =======================================================
		// Reset data
		$this->assertNotEmpty(LiteConfig::all());
		LiteConfig::resetData();
		$this->assertEmpty(LiteConfig::all());
	}

	public function testArrayPrefix() {
		$original_array = [
			'app' => 'MyApp',
			'url' => 'myapp.com',
			'build' => 137,
		];

		LiteConfig::loadArray($original_array, 'prefix');

		// Get
		$this->assertSame(LiteConfig::get('prefix'), $original_array);
		$this->assertCount(3, LiteConfig::get('prefix'));
		$this->assertSame(LiteConfig::all(), ['prefix' => $original_array]);

		$this->assertSame(LiteConfig::get('prefix.app'), 'MyApp');
		$this->assertSame(LiteConfig::get('prefix.url'), 'myapp.com');
		$this->assertSame(LiteConfig::get('prefix.build'), 137);
		$this->assertNotSame(LiteConfig::get('prefix.build'), '137');

		$this->assertNull(LiteConfig::get('prefix.undefined'));
		$this->assertNull(LiteConfig::get('prefix.undefined.foo'));
		$this->assertNull(LiteConfig::get('prefix.undefined.foo.bar'));
		$this->assertSame(LiteConfig::get('prefix.undefined_default', ['default' => 'value']), ['default' => 'value']);
		$this->assertSame(LiteConfig::get('prefix.undefined_default.foo', 'default-value'), 'default-value');
		$this->assertSame(LiteConfig::get('prefix.undefined_default.foo.bar', 'default-value'), 'default-value');

		$this->assertNull(LiteConfig::get('invalid'));
		$this->assertNull(LiteConfig::get('invalid.foo'));
		$this->assertNull(LiteConfig::get('invalid.foo.bar'));
		$this->assertSame(LiteConfig::get('invalid', 'default-value'), 'default-value');
		$this->assertSame(LiteConfig::get('invalid.foo', 'default-value'), 'default-value');
		$this->assertSame(LiteConfig::get('invalid.foo.bar', 'default-value'), 'default-value');


		// Exists
		$this->assertTrue(LiteConfig::exists('prefix'));

		$this->assertTrue(LiteConfig::exists('prefix.app'));
		$this->assertTrue(LiteConfig::exists('prefix.url'));

		$this->assertFalse(LiteConfig::exists('prefix.undefined'));
		$this->assertFalse(LiteConfig::exists('prefix.undefined.foo'));
		$this->assertFalse(LiteConfig::exists('prefix.undefined.foo.bar'));
		$this->assertFalse(LiteConfig::exists('prefix.undefined_default'));
		$this->assertFalse(LiteConfig::exists('prefix.undefined_default.foo'));
		$this->assertFalse(LiteConfig::exists('prefix.undefined_default.foo.bar'));

		$this->assertFalse(LiteConfig::exists('invalid'));
		$this->assertFalse(LiteConfig::exists('invalid.foo'));
		$this->assertFalse(LiteConfig::exists('invalid.foo.bar'));
		$this->assertFalse(LiteConfig::exists('invalid'));
		$this->assertFalse(LiteConfig::exists('invalid.foo'));
		$this->assertFalse(LiteConfig::exists('invalid.foo.bar'));

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

		// Reset
		// Reset data
		$this->assertNotEmpty(LiteConfig::all());
		LiteConfig::resetData();
		$this->assertEmpty(LiteConfig::all());
	}

}
