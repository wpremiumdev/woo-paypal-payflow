<?php

/**
 *
 * @link              http://localleadminer.com/
 * @since             1.0.0
 * @package           Pal_Pro_Payflow
 *
 * @wordpress-plugin
 * Plugin Name:       PayPal Pro Payflow for Woo
 * Plugin URI:        http://localleadminer.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            wpremiumdev
 * Author URI:        http://localleadminer.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-paypal-payflow
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pal-pro-payflow-activator.php
 */
function activate_pal_pro_payflow() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pal-pro-payflow-activator.php';
    Pal_Pro_Payflow_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pal-pro-payflow-deactivator.php
 */
function deactivate_pal_pro_payflow() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pal-pro-payflow-deactivator.php';
    Pal_Pro_Payflow_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_pal_pro_payflow');
register_deactivation_hook(__FILE__, 'deactivate_pal_pro_payflow');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pal-pro-payflow.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pal_pro_payflow() {

    $plugin = new Pal_Pro_Payflow();
    $plugin->run();
}

add_action('plugins_loaded', 'load_pal_pro_payflow');

function load_pal_pro_payflow() {
    run_pal_pro_payflow();
}
