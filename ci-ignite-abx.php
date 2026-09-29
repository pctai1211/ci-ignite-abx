<?php
/**
 * Plugin Name: CI Ignite ABX
 * Plugin URI:  https://github.com/ci-ignite-abx
 * Description: Laboratory and PCR panel management for Ignite LIS. Extensible modules for roles, tables, and admin pages.
 * Version:     1.0.0
 * Author:      CI
 * Text Domain: ci-ignite-abx
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('CI_ABX_VERSION', '1.0.1');
define('CI_ABX_FILE', __FILE__);
define('CI_ABX_PATH', plugin_dir_path(__FILE__));
define('CI_ABX_URL', plugin_dir_url(__FILE__));
define('CI_ABX_DB_VERSION', '1.0.0');

require_once CI_ABX_PATH . 'includes/Autoloader.php';

CI\IgniteAbx\Autoloader::register();

register_activation_hook(__FILE__, array('CI\\IgniteAbx\\Activator', 'activate'));
register_deactivation_hook(__FILE__, array('CI\\IgniteAbx\\Deactivator', 'deactivate'));

add_action('plugins_loaded', static function () {
	CI\IgniteAbx\Plugin::instance()->boot();
});
