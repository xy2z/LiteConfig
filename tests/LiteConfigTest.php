<?php

namespace xy2z\LiteConfigTests;

use PHPUnit\Framework\TestCase;
use xy2z\LiteConfig\LiteConfig;

class LiteConfigTest extends TestCase {

	/**
	 * Test get methods
	 */
	public function testGetters() {
		LiteConfig::loadArray([
			'app' => 'MyApp',
			'url' => 'myapp.com',
		]);


		// All
		$this->assertEquals(LiteConfig::all(), [
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
	}

}
