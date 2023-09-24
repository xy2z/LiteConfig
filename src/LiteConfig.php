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
	protected static array $data = [];

	/**
	 * Argument used in file_parse_ini()
	 *
	 * @var boolean
	 */
	public static bool $ini_process_sections = true;

	/**
	 * Argument used in file_parse_ini()
	 *
	 * @var integer
	 */
	public static int $ini_scanner_mode = INI_SCANNER_TYPED;

	/**
	 * If set to false, it will throw an exception when loading unsupported filetypes.
	 */
	public static bool $ignore_unsupported_filestypes = true;

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
	public static function loadFile(string $path, bool $prefix_filename = false, string $custom_prefix = null): void {
		$pathinfo = pathinfo($path);

		// Add prefix
		$prefix = ($prefix_filename) ? $pathinfo['filename'] : null;

		if (!is_null($custom_prefix)) {
			$prefix = (is_null($prefix)) ? $custom_prefix : $custom_prefix . '.' . $prefix;
		}

		// Load file content
		$content = static::getFileContent($path, $pathinfo);
		if (is_array($content)) {
			static::loadArray($content, $prefix);
		}
		// Quietly ignore empty files.
	}

	/**
	 * Get file content as php array
	 *
	 * @param string $path Path to file
	 * @param array $pathinfo pathinfo() array
	 *
	 * @return null|array
	 */
	protected static function getFileContent(string $path, array $pathinfo): null|array {
		switch ($pathinfo['extension']) {
			case 'php':
				$php = require $path;
				if (empty($php) || $php === 1) {
					return null;
				}
				if (!is_array($php)) {
					throw new \Exception('PHP config file must return an array (' . $pathinfo['basename'] . ')');
				}
				return $php;

			case 'ini':
				$arr = parse_ini_file($path, static::$ini_process_sections, static::$ini_scanner_mode);
				if (!is_array($arr)) {
					throw new \Exception('Invalid INI file  ( ' . $pathinfo['basename'] . ')');
				}
				return $arr;

			case 'json':
				$arr = json_decode(file_get_contents($path), true);
				if (empty($arr)) {
					return null;
				}
				if (!is_array($arr)) {
					throw new \Exception('JSON config file must return an array (' . $pathinfo['basename'] . ')');
				}
				return $arr;

			default:
				// Allow custom handler for other filename extensions.
				$custom_handler = call_user_func_array(array(get_called_class(), 'custom_handler'), array(
					'extension' => $pathinfo['extension'],
					'path' => $path,
				));

				// Only return if success, otherwise it will throw exception at end of this method.
				if (($custom_handler !== null) && ($custom_handler !== false)) {
					return $custom_handler;
				}
		}

		if (!static::$ignore_unsupported_filestypes) {
			throw new \Exception('Unsupported filetype: ' . $pathinfo['extension']);
		}

		return [];
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
			static::add($key, $val, $prefix);
		}
	}

	/**
	 * Add data to the config
	 *
	 * @param string $key Key name
	 * @param mixed $value Value
	 */
	protected static function add(string $key, $value, string $prefix = null): void {
		if (is_array($value)) {
			foreach ($value as $k2 => $v2) {
				$key_path = $key . '.' . $k2;
				static::add($key_path, $v2, $prefix);
			}
		}

		// Save.
		if (!is_null($prefix)) {
			static::$data[$prefix][$key] = $value;
			static::$data[$prefix . '.' . $key] = $value;
		} else {
			static::$data[$key] = $value;
		}
	}

	/**
	 * Get by key
	 *
	 * @param string $key Key
	 * @param mixed $default default value
	 *
	 * @return mixed Value
	 */
	public static function get(string $key, $default = null) {
		if (isset(static::$data[$key])) {
			// Relevant with double prefixes (file prefix + custom prefix)
			return static::$data[$key];
		}

		if (strpos($key, '.') === false) {
			return static::$data[$key] ?? $default;
		}

		// Key contains dot, so it's a nested key.
		// Check if the first part exists.
		$parts = explode('.', $key);
		$prefix = array_shift($parts);
		if (!isset(static::$data[$prefix])) {
			return $default; // not found. return default.
		}

		// Check if the rest exists.
		$rest = implode('.', $parts);
		return static::$data[$prefix][$rest] ?? $default;
	}

	/**
	 * Get complete config as array
	 *
	 * @return array
	 */
	public static function all(): array {
		return static::$data;
	}

	/**
	 * Check if key exists
	 *
	 * @param string $key Key name
	 *
	 * @return bool
	 */
	public static function exists(string $key): bool {
		if (isset(static::$data[$key])) {
			// Relevant with double prefixes (file prefix + custom prefix)
			return true;
		}

		if (strpos($key, '.') === false) {
			return isset(static::$data[$key]);
		}

		// Key contains dot, so it's a nested key.
		// Check if the first part exists.
		$parts = explode('.', $key);
		$prefix = array_shift($parts);
		if (!isset(static::$data[$prefix])) {
			return false;
		}

		// Check if the rest exists.
		$rest = implode('.', $parts);
		return isset(static::$data[$prefix][$rest]);
	}

	/**
	 * This is a placeholder for child classes.
	 * To make sure they follow the "interface" of this function (protected, arguments, etc.)
	 *
	 * @param string $extension File extension (eg. "php")
	 * @param string $path Full file path
	 */
	protected static function custom_handler(string $extension, string $path) {
		return false;
	}

	/**
	 * Resets (clears) $data array.
	 * So it can be loaded again without having old config keys.
	 *
	 * @return void
	 */
	public static function resetData() {
		static::$data = [];
	}
}
