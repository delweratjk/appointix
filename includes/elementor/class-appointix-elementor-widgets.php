<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Appointix_Elementor_Widgets
 * Handles Elementor widget registration.
 */
class Appointix_Elementor_Widgets {

	private static $instance = null;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'appointix-widgets',
			array(
				'title' => __( 'Appointix Widgets', 'appointix' ),
				'icon'  => 'fa fa-calendar',
			)
		);
	}

	public function register_widgets( $widgets_manager ) {
		require_once plugin_dir_path( __FILE__ ) . 'widgets/class-appointix-hero-widget.php';
		require_once plugin_dir_path( __FILE__ ) . 'widgets/class-appointix-booking-widget.php';
		require_once plugin_dir_path( __FILE__ ) . 'widgets/class-appointix-apartments-grid-widget.php';
		require_once plugin_dir_path( __FILE__ ) . 'widgets/class-appointix-amenities-widget.php';
		require_once plugin_dir_path( __FILE__ ) . 'widgets/class-appointix-faq-widget.php';
		require_once plugin_dir_path( __FILE__ ) . 'widgets/class-appointix-info-widget.php';

		$widgets_manager->register( new \Appointix_Hero_Widget() );
		$widgets_manager->register( new \Appointix_Booking_Widget() );
		$widgets_manager->register( new \Appointix_Apartments_Grid_Widget() );
		$widgets_manager->register( new \Appointix_Amenities_Widget() );
		$widgets_manager->register( new \Appointix_FAQ_Widget() );
		$widgets_manager->register( new \Appointix_Info_Widget() );
	}
}

// Initialize on elementor loaded
add_action( 'elementor/loaded', function() {
	Appointix_Elementor_Widgets::get_instance();
} );
