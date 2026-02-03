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
 * @package    Appointix
 * @subpackage Appointix/includes
 */
class Appointix {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Appointix_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to identify this plugin.
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
	 * Set the plugin name and the plugin version through the constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'APPOINTIX_VERSION' ) ) {
			$this->version = APPOINTIX_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'appointix';

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
	 * - Appointix_Loader. Orchestrates the hooks of the plugin.
	 * - Appointix_i18n. Defines internationalization functionality.
	 * - Appointix_Admin. Defines all hooks for the admin area.
	 * - Appointix_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-appointix-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-appointix-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-appointix-admin.php';

		/**
		 * The class responsible for handling bookings data.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/models/class-appointix-bookings-model.php';

		/**
		 * The class responsible for availability checking.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/models/class-appointix-availability-model.php';

		/**
		 * The class responsible for seasonal pricing.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/models/class-appointix-seasonal-pricing-model.php';

		/**
		 * The class responsible for handling apartments data.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/models/class-appointix-apartments-model.php';

		/**
		 * The class responsible for handling emails.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-appointix-emails.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-appointix-public.php';

		/**
		 * The class responsible for handling iCal sync.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-appointix-ical.php';

		/**
		 * The class responsible for Elementor widgets.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/elementor/class-appointix-elementor-widgets.php';

		$this->loader = new Appointix_Loader();

	}

	/**
	 * Set the locale for this plugin for internationalization.
	 *
	 * Uses the Appointix_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Appointix_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Appointix_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_cpt' );

		// Booking AJAX
		$this->loader->add_action( 'wp_ajax_appointix_update_booking_status', $plugin_admin, 'ajax_update_booking_status' );
		$this->loader->add_action( 'wp_ajax_appointix_delete_booking', $plugin_admin, 'ajax_delete_booking' );
		$this->loader->add_action( 'wp_ajax_appointix_restore_booking', $plugin_admin, 'ajax_restore_booking' ); // Added
		$this->loader->add_action( 'wp_ajax_appointix_permanent_delete_booking', $plugin_admin, 'ajax_permanent_delete_booking' ); // Added
		$this->loader->add_action( 'wp_ajax_appointix_get_bookings', $plugin_admin, 'ajax_get_bookings' );
		$this->loader->add_action( 'wp_ajax_appointix_save_settings', $plugin_admin, 'ajax_save_settings' );
		$this->loader->add_action( 'wp_ajax_appointix_manual_ical_sync', $plugin_admin, 'ajax_manual_ical_sync' );

		$this->loader->add_action( 'wp_ajax_appointix_add_seasonal_price', $plugin_admin, 'ajax_add_seasonal_price' );
		$this->loader->add_action( 'wp_ajax_appointix_get_seasonal_prices', $plugin_admin, 'ajax_get_seasonal_prices' );
		$this->loader->add_action( 'wp_ajax_appointix_delete_seasonal_price', $plugin_admin, 'ajax_delete_seasonal_price' );

		// Enable Elementor for apartments CPT
		$this->loader->add_filter( 'elementor/cpt_support', $this, 'add_elementor_cpt_support' );
	}

	/**
	 * Add Elementor support for apartments CPT.
	 *
	 * @param array $post_types Supported post types
	 * @return array Modified post types
	 */
	public function add_elementor_cpt_support( $post_types ) {
		$post_types[] = 'appointix_apartment';
		return $post_types;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Appointix_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_filter( 'template_include', $plugin_public, 'template_loader' );
		$this->loader->add_filter( 'body_class', $plugin_public, 'add_body_classes' );

		// Public AJAX handlers
		$this->loader->add_action( 'wp_ajax_appointix_submit_booking', $plugin_public, 'ajax_submit_booking' );
		$this->loader->add_action( 'wp_ajax_nopriv_appointix_submit_booking', $plugin_public, 'ajax_submit_booking' );
		$this->loader->add_action( 'wp_ajax_appointix_get_booked_dates', $plugin_public, 'ajax_get_booked_dates' );
		$this->loader->add_action( 'wp_ajax_nopriv_appointix_get_booked_dates', $plugin_public, 'ajax_get_booked_dates' );
		$this->loader->add_action( 'wp_ajax_appointix_get_available_slots', $plugin_public, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_nopriv_appointix_get_available_slots', $plugin_public, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_appointix_calculate_price', $plugin_public, 'ajax_calculate_price' );
		$this->loader->add_action( 'wp_ajax_nopriv_appointix_calculate_price', $plugin_public, 'ajax_calculate_price' );
		$this->loader->add_action( 'wp_ajax_appointix_get_apartment_content', $plugin_public, 'ajax_get_apartment_content' );
		$this->loader->add_action( 'wp_ajax_nopriv_appointix_get_apartment_content', $plugin_public, 'ajax_get_apartment_content' );
		$this->loader->add_action( 'wp_ajax_appointix_submit_rating', $plugin_public, 'ajax_submit_rating' );
		$this->loader->add_action( 'wp_ajax_nopriv_appointix_submit_rating', $plugin_public, 'ajax_submit_rating' );

		// iCal Export Listener
		$this->loader->add_action( 'template_redirect', $plugin_public, 'handle_ical_export' );

		// Apartment Details Listener
		$this->loader->add_action( 'template_redirect', $plugin_public, 'handle_apartment_details' );

		// WhatsApp Button
		$this->loader->add_action( 'wp_footer', $plugin_public, 'render_whatsapp_button' );

		// iCal Sync Cron
		$plugin_ical = new Appointix_iCal();
		$this->loader->add_action( 'appointix_hourly_sync', $plugin_ical, 'sync_all' );
		if ( ! wp_next_scheduled( 'appointix_hourly_sync' ) ) {
			wp_schedule_event( time(), 'hourly', 'appointix_hourly_sync' );
		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		$this->loader->run();

		// Seed content if needed - Hooked to init to avoid fatal errors
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-appointix-seeder.php';
		add_action( 'init', array( 'Appointix_Seeder', 'seed' ) );
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
	 * @return    Appointix_Loader    Orchestrates the hooks of the plugin.
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

}
