<?php
/**
 * Auto-loads the required dependencies for this plugin.
 *
 * @link       https://jcodex.com
 * @since      3.5.0
 *
 * @package    woo-checkout-register-field-editor-premium
 * @subpackage woo-checkout-register-field-editor-premium/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('JWCFE_Autoloader')):

class JWCFE_Autoloader {
	private $include_path = '';

	public function __construct() {
		$this->include_path = untrailingslashit(JWCFE_PATH);

		if(function_exists("__autoload")){
			spl_autoload_register("__autoload");
		}
		spl_autoload_register(array($this, 'autoload'));
	}

	/** Include a class file. */
	private function load_file($path) {
		if ($path && is_readable($path)) {
			require_once($path);
			return true;
		}
		return false;
	}

	/** Class name to file name. */
	private function get_file_name_from_class($class) {
		return 'class-' . str_replace('_', '-', strtolower($class)) . '.php';
	}

	public function autoload($class) {
		$class = strtolower($class);
		$file  = $this->get_file_name_from_class($class);
		$path  = '';

		if (strpos($class, 'jwcfe_admin') === 0) {
			$path = $this->include_path . '/admin/';
		} elseif (strpos($class, 'jwcfe_public') === 0) {
			$path = $this->include_path . '/public/';
		} elseif (strpos($class, 'jwcfe_utils') === 0) {
			$path = $this->include_path . '/includes/utils/';
		} else {
			$path = $this->include_path . '/includes/';
		}

		$file_path = $path . $file;

		if (!$this->load_file($file_path) && strpos($class, 'jwcfe_') === 0) {
			$this->load_file($this->include_path . '/' . $file);
		}
	}
}

endif;

new JWCFE_Autoloader();
