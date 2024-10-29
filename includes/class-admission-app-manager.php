<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.mariotadic.com
 * @since      1.0.0
 *
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/includes
 */

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
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/includes
 * @author     Mario Tadic <tadic.mario@gmail.com>
 */
class Admission_App_Manager
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Admission_App_Manager_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $admission_app_manager The string used to uniquely identify this plugin.
     */
    protected $admission_app_manager;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
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
    public function __construct()
    {

        $this->admission_app_manager = 'admission-app-manager';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Admission_App_Manager_Loader. Orchestrates the hooks of the plugin.
     * - Admission_App_Manager_i18n. Defines internationalization functionality.
     * - Admission_App_Manager_Admin. Defines all hooks for the admin area.
     * - Admission_App_Manager_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        if (file_exists(dirname(__FILE__) . '/vendor/webdevstudios/cmb2/init.php')) {
            require_once dirname(__FILE__) . '/vendor/webdevstudios/cmb2/init.php';
            require_once dirname(__FILE__) . '/helpers/admission-app-manager-cmb-combination.php';
        }

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/vendor/autoload.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-admission-app-manager-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-admission-app-manager-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admission-app-manager-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/helpers/admission-app-manager-helper.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-admission-app-manager-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-admission-app-manager-post-types.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-admission-app-manager-taxonomy.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admission-app-manager-custom-fields.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admission-app-manager-ajax.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admission-app-manager-settings.php';

        $this->loader = new Admission_App_Manager_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Admission_App_Manager_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Admission_App_Manager_i18n();
        $plugin_i18n->set_domain($this->get_admission_app_manager());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Admission_App_Manager_Admin($this->get_admission_app_manager(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        //add admin menus
        $this->loader->add_action('admin_menu', $plugin_admin, 'aam_add_main_menu_item');

        //consultant user additional settings
        $this->loader->add_action('show_user_profile', $plugin_admin, 'aam_consultant_custom_fields');
        $this->loader->add_action('edit_user_profile', $plugin_admin, 'aam_consultant_custom_fields');
        $this->loader->add_action('personal_options_update', $plugin_admin, 'aam_save_consultant_custom_fields');
        $this->loader->add_action('edit_user_profile_update', $plugin_admin, 'aam_save_consultant_custom_fields');

        //client user additional settings
        $this->loader->add_action('edit_user_profile', $plugin_admin, 'aam_client_custom_fields');
        $this->loader->add_action('edit_user_profile', $plugin_admin, 'aam_client_own_custom_fields');
        $this->loader->add_action('show_user_profile', $plugin_admin, 'aam_client_own_custom_fields');
        $this->loader->add_action('edit_user_profile_update', $plugin_admin, 'aam_save_client_custom_fields');
        $this->loader->add_action('edit_user_profile_update', $plugin_admin, 'aam_save_client_own_custom_fields');
        $this->loader->add_action('show_user_profile_update', $plugin_admin, 'aam_save_client_own_custom_fields');

        //school additional settings
        $this->loader->add_action('edit_user_profile', $plugin_admin, 'aam_school_custom_fields');
        $this->loader->add_action('edit_user_profile_update', $plugin_admin, 'aam_save_school_custom_fields');

        //send emails to admin on application save
        $this->loader->add_action('aam_save_deadlines', $plugin_admin, 'aam_send_prompts_to_client');
        $this->loader->add_action('aam_save_deadlines', $plugin_admin, 'aam_send_deadlines_to_client');

        //add hash to filename on upload
        $this->loader->add_filter('sanitize_file_name', $plugin_admin, 'make_filename_hash', 10);

        //redirect router
        $this->loader->add_filter( 'login_redirect', $plugin_admin, 'aam_login_redirect', 10, 3 );

        //remove dashboard widgets
        $this->loader->add_action( 'admin_init', $plugin_admin, 'remove_dashboard_meta' );

        //force single column admin dashboard
        $this->loader->add_filter('screen_layout_columns', $plugin_admin, 'aam_screen_layout_columns');
        $this->loader->add_filter('get_user_option_screen_layout_dashboard', $plugin_admin, 'aam_screen_layout_dashboard');

        $this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'aam_add_dashboard_widgets' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Admission_App_Manager_Public($this->get_admission_app_manager(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_admission_app_manager()
    {
        return $this->admission_app_manager;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Admission_App_Manager_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}
