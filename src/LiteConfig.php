<?php

namespace xy2z\LiteConfig;

/**
 * Lite Config
 *
 */
abstract class LiteConfig {

	/**
	 * Config data
	 *
	 * @var array
	 */
	protected static $data = [];

	/**
	 * Argument used in file_parse_ini()
	 *
	 * @var boolean
	 */
	public static $ini_process_sections = true;

	/**
	 * Argument used in file_parse_ini()
	 *
	 * @var integer
	 */
	public static $ini_scanner_mode = INI_SCANNER_TYPED;

	/**
	 * Loads all config files in a directory.
	 *
	 * @param string $path Path to dir
	 * @param boolean $prefix_filename Prefix the key with the filename
	 * @param string $custom_prefix Prefix the key
	 *
	 * @return void
	 */
	public static function loadDir(string $path, $prefix_filename = false, $custom_prefix = null) {
		$glob = glob($path . '/*.*');

		foreach ($glob as $filepath) {
			static::loadFile($filepath, $prefix_filename, $custom_prefix);
		}
	}

	/**
	 * Load config file
	 *
	 * @param string $path Path to file
	 * @param boolean $prefix_filename Prefix the key with the filename
	 * @param string $custom_prefix Prefix the key
	 *
	 */
	public static function loadFile(string $path, bool $prefix_filename = false, string $custom_prefix = null) {
		$pathinfo = pathinfo($path);

		// Add prefix
		$prefix = ($prefix_filename) ? $pathinfo['filename'] : null;

		if (!is_null($custom_prefix)) {
			$prefix = $custom_prefix . '.' . $prefix;
		}

		// Load file content
		$content = static::getFileContent($path, $pathinfo);
		static::loadArray($content, $prefix);
	}

	/**
	 * Get file content as php array
	 *
	 * @param string $path Path to file
	 * @param array $pathinfo pathinfo() array
	 *
	 * @return array
	 */
	protected static function getFileContent(string $path, array $pathinfo) {
		switch ($pathinfo['extension']) {
			case 'php':
				return require($path);

			case 'ini':
				return parse_ini_file($path, static::$ini_process_sections, static::$ini_scanner_mode);

			case 'json':
				return json_decode(file_get_contents($path), true);
		}

		throw new \Exception('Unsupported filetype: ' . $pathinfo['extension']);
	}


	/**
	 * Load an array into the config
	 *
	 * @param array $data Data to load.
	 * @param string $prefix Prefix the keys (optional)
	 *
	 * @return void
	 */
	public static function loadArray(array $data, string $prefix = null) {
		foreach ($data as $key => $val) {
			if (!is_null($prefix)) {
				$key = $prefix . '.' . $key;
			}

			static::add($key, $val);
		}
	}

	/**
	 * Add data to the config
	 *
	 * @param string $key Key name
	 * @param mixed $value Value
	 */
	protected static function add(string $key, $value) {
		if (is_array($value)) {
			foreach ($value as $k2 => $v2) {
				$key_path = $key . '.' . $k2;
				static::add($key_path, $v2);
			}
		}

		// Set.
		static::$data[$key] = $value;
	}

	/**
	 * Get by key
	 *
	 * @param string $key Key
	 *
	 * @return mixed Value
	 */
	public static function get(string $key, $default) {
		return static::$data[$key] ?? $default ?? null;
	}

	/**
	 * Get complete config as array
	 *
	 * @return array
	 */
	public static function all() : array {
		return static::$data;
	}

	/**
	 * Check if key exists
	 *
	 * @param string $key Key name
	 *
	 * @return bool
	 */
	public static function exists(string $key) : bool {
		return isset(self::$data[$key]);
	}
}
