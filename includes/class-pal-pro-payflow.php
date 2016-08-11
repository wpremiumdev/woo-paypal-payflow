<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Pal_Pro_Payflow
 * @subpackage Pal_Pro_Payflow/includes
 * @author     wpremiumdev <wpremiumdev@gmail.com>
 */
class Pal_Pro_Payflow {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Pal_Pro_Payflow_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'woo-paypal-payflow';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->woo_gateway_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Pal_Pro_Payflow_Loader. Orchestrates the hooks of the plugin.
     * - Pal_Pro_Payflow_i18n. Defines internationalization functionality.
     * - Pal_Pro_Payflow_Admin. Defines all hooks for the admin area.
     * - Pal_Pro_Payflow_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pal-pro-payflow-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pal-pro-payflow-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-premium-pal-pro-payflow-common-function.php';
        if (class_exists('WC_Payment_Gateway')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-premium-pal-pro-payflow-gateway.php';
        }

        $this->loader = new Pal_Pro_Payflow_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Pal_Pro_Payflow_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Pal_Pro_Payflow_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Add Payment Gateways Woocommerce Section
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function woo_gateway_hooks() {
        add_filter('woocommerce_payment_gateways', array($this, 'premium_add_pal_pro_payflow_gateways'), 10, 1);
    }

    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Pal_Pro_Payflow_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
    
    public function premium_add_pal_pro_payflow_gateways($methods) {
        $methods[] = 'Premium_Pal_Pro_PayFlow_Gateway';
        return $methods;
    }

}
