<?php
/**
* class Picasso_Ideas_Session
*/
class Picasso_Ideas_Session {
	/**
	 * Check if session exists
	 * 
	 * @param  string $name
	 * @return boolean
	 */
	public static function exists($name) {
		return (isset($_SESSION[$name])) ? true : false;
	}

	/**
	 * Save data in session
	 * 
	 * @param  string $name
	 * @param  mixed $value
	 */
	public static function put($name, $value) {
		return $_SESSION[$name] = $value;
	}

	/**
	 * Get session
	 * 
	 * @param  string $name
	 * @return mixed
	 */
	public static function get($name) {
		return $_SESSION[$name];
	}

	/**
	 * Delete session
	 * 
	 * @param  string $name
	 */
	public static function delete($name) {
		if (self::exists($name)) {
			unset($_SESSION[$name]);
		}
	}

	/**
	 * Flash session
	 * 
	 * @param  string $name
	 * @param  mixed $string
	 */
	public static function flash($name, $string = null) {
		if (self::exists($name)) {
			$session = self::get($name);
			self::delete($name);
			return $session;
		} else {
			self::put($name, $string);
		}
	}
}