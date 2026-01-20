<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://example.com
 * @since             1.0.0
 * @package           Appointix
 *
 * @wordpress-plugin
 * Plugin Name:       Appointix
 * Plugin URI:        https://example.com/appointix
 * Description:       A premium WordPress booking plugin for hotels, appointments, and more.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://yourname.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       appointix
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - http://semver.org/
 */
define( 'APPOINTIX_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-appointix-activator.php
 */
function activate_appointix() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-appointix-activator.php';
	Appointix_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-appointix-deactivator.php
 */
function deactivate_appointix() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-appointix-deactivator.php';
	Appointix_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_appointix' );
register_deactivation_hook( __FILE__, 'deactivate_appointix' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-appointix.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks, then kicking off
 * the plugin from this point in the file will register the hooks with
 * WordPress.
 *
 * @since    1.0.0
 */
function run_appointix() {

	$plugin = new Appointix();
	$plugin->run();

}
run_appointix();
