<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/public
 */
class Appointix_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of the plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register shortcodes for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'appointix_booking', array( $this, 'booking_form_shortcode' ) );
		add_shortcode( 'appointix_apartments', array( $this, 'apartments_list_shortcode' ) );
	}

	/**
	 * Render the booking form shortcode.
	 *
	 * @since    1.0.0
	 */
	public function booking_form_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'apartment_id'                    => 0,
				'apartment_name'                  => '',
				'booking_form_heading'            => '',
				'booking_form_subheading'         => '',
				'booking_form_check_in'           => '',
				'booking_form_check_out'          => '',
				'booking_form_select_dates'       => '',
				'booking_form_adults'             => '',
				'booking_form_children'           => '',
				'booking_form_check_availability' => '',
				'type'                            => '',
				'show_apartment_dropdown'         => 'yes',
				'apartment_label'                 => '',
			),
			$atts,
			'appointix_booking'
		);

		$preselected_apartment           = intval( $atts['apartment_id'] );
		$type_filter                     = sanitize_text_field( $atts['type'] );
		$apartment_name                  = sanitize_text_field( $atts['apartment_name'] );
		$booking_form_heading            = sanitize_text_field( $atts['booking_form_heading'] );
		$booking_form_subheading         = sanitize_text_field( $atts['booking_form_subheading'] );
		$booking_form_check_in           = sanitize_text_field( $atts['booking_form_check_in'] );
		$booking_form_check_out          = sanitize_text_field( $atts['booking_form_check_out'] );
		$booking_form_select_dates       = sanitize_text_field( $atts['booking_form_select_dates'] );
		$booking_form_adults             = sanitize_text_field( $atts['booking_form_adults'] );
		$booking_form_children           = sanitize_text_field( $atts['booking_form_children'] );
		$booking_form_check_availability = sanitize_text_field( $atts['booking_form_check_availability'] );
		$show_apartment_dropdown         = sanitize_text_field( $atts['show_apartment_dropdown'] );
		$apartment_label                 = sanitize_text_field( $atts['apartment_label'] );

		$check_in  = isset( $_GET['check_in'] ) ? sanitize_text_field( $_GET['check_in'] ) : '';
		$check_out = isset( $_GET['check_out'] ) ? sanitize_text_field( $_GET['check_out'] ) : '';

		// Fetch all apartments for availability check
		if ( ! empty( $type_filter ) ) {
			$all_apartments = Appointix_Apartments_Model::get_apartments_by_type( $type_filter );
		} else {
			$all_apartments = Appointix_Apartments_Model::get_apartments();
		}

		$available_apartments   = array();
		$selected_apartment     = null;
		$is_selected_available  = false;
		$requested_apartment_id = isset( $_GET['apartment_id'] ) ? intval( $_GET['apartment_id'] ) : $preselected_apartment;

		foreach ( $all_apartments as $apt ) {
			$is_free = true;
			if ( ! empty( $check_in ) && ! empty( $check_out ) ) {
				$is_free = Appointix_Availability_Model::is_available( $apt->id, $check_in, null, $check_out );
			}

			if ( $is_free ) {
				$available_apartments[] = $apt;
			}

			if ( $requested_apartment_id && $apt->id === $requested_apartment_id ) {
				$selected_apartment    = $apt;
				$is_selected_available = $is_free;
			}
		}

		// Fallback for translated IDs or cross-language links
		if ( $requested_apartment_id && ! $selected_apartment ) {
			$fallack_apt = Appointix_Apartments_Model::get_apartment( $requested_apartment_id );
			if ( $fallack_apt ) {
				$selected_apartment    = $fallack_apt;
				$is_selected_available = true;
				if ( ! empty( $check_in ) && ! empty( $check_out ) ) {
					$is_selected_available = Appointix_Availability_Model::is_available( $fallack_apt->id, $check_in, null, $check_out );
				}
			}
		}

		// Compat for existing partial expectations
		$apartments = $available_apartments;

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/appointix-public-display.php';
		return ob_get_clean();
	}

	public function apartments_list_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'type'  => '',
				'count' => -1,
			),
			$atts,
			'appointix_apartments'
		);

		$type_filter = sanitize_text_field( $atts['type'] );
		$count       = intval( $atts['count'] );

		if ( ! empty( $type_filter ) ) {
			$apartments = Appointix_Apartments_Model::get_apartments_by_type( $type_filter );
		} else {
			$args = array();
			if ( $count > 0 ) {
				$args['posts_per_page'] = $count;
			}
			$apartments = Appointix_Apartments_Model::get_apartments( $args );
		}

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/appointix-apartments-list.php';
		return ob_get_clean();
	}

	public function template_loader( $template ) {
		if ( is_singular( 'appointix_apartment' ) ) {
			$new_template = plugin_dir_path( __FILE__ ) . 'partials/single-appointix_apartment.php';
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		}
		return $template;
	}

	/**
	 * Handle apartment details page when accessed via query parameter
	 */
	public function handle_apartment_details() {
		if ( isset( $_GET['apartment_id'] ) && ! empty( $_GET['apartment_id'] ) ) {
			include plugin_dir_path( __FILE__ ) . 'partials/apartment-details-query.php';
		}
	}

	/**
	 * Add custom body classes for the search page.
	 */
	public function add_body_classes( $classes ) {
		if ( isset( $_GET['check_in'] ) || isset( $_GET['check_out'] ) ) {
			$classes[] = 'apt-body-modal-extend';
		}
		return $classes;
	}

	/**
	 * AJAX handler to get booked dates for a service.
	 */
	public function ajax_get_booked_dates() {
		check_ajax_referer( 'appointix_public_nonce', 'nonce' );

		$post_id = intval( $_POST['post_id'] );

		global $wpdb;
		$table_name = $wpdb->prefix . 'appointix_bookings';

		// Get all dates where this post is booked (including pending)
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT booking_date FROM $table_name WHERE post_id = %d AND status NOT IN ('cancelled', 'rejected')",
				$post_id
			)
		);

		$dates = array();
		foreach ( $results as $row ) {
			$dates[] = $row->booking_date;
		}

		wp_send_json_success( array( 'dates' => $dates ) );
	}

	/**
	 * AJAX handler to get available slots.
	 */
	public function ajax_get_available_slots() {
		check_ajax_referer( 'appointix_public_nonce', 'nonce' );

		$post_id = intval( $_POST['post_id'] );
		$date    = sanitize_text_field( $_POST['date'] );

		$apartment = Appointix_Apartments_Model::get_apartment( $post_id );
		if ( ! $apartment ) {
			wp_send_json_error( array( 'message' => 'Apartment not found.' ) );
		}

		// Logic to generate time slots (e.g., 09:00 to 18:00 every 60 mins)
		$slots    = array();
		$start    = 9; // 9 AM
		$end      = 18;  // 6 PM
		$interval = 60;

		for ( $hour = $start; $hour < $end; $hour++ ) {
			for ( $min = 0; $min < 60; $min += $interval ) {
				$time = sprintf( '%02d:%02d', $hour, $min );
				// Check if this slot is already booked
				if ( Appointix_Availability_Model::is_available( $post_id, $date, $time ) ) {
					$slots[] = array(
						'time'  => $time,
						'label' => date( 'h:i A', strtotime( $time ) ),
					);
				}
			}
		}

		wp_send_json_success( array( 'slots' => $slots ) );
	}

	/**
	 * AJAX handler to calculate total price for a range.
	 */
	public function ajax_calculate_price() {
		check_ajax_referer( 'appointix_public_nonce', 'nonce' );

		$post_id    = intval( $_POST['post_id'] );
		$start_date = sanitize_text_field( $_POST['start_date'] );
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';

		$total = Appointix_Seasonal_Pricing_Model::calculate_total( $post_id, $start_date, $end_date );

		wp_send_json_success( array( 'total' => number_format( $total, 2 ) ) );
	}

	/**
	 * AJAX handler to submit a booking.
	 */
	public function ajax_submit_booking() {
		check_ajax_referer( 'appointix_public_nonce', 'nonce' );

		$data = array(
			'post_id'        => intval( $_POST['post_id'] ),
			'customer_name'  => sanitize_text_field( $_POST['name'] ),
			'customer_email' => sanitize_email( $_POST['email'] ),
			'customer_phone' => sanitize_text_field( $_POST['phone'] ),
			'booking_date'   => sanitize_text_field( $_POST['date'] ),
			'booking_time'   => sanitize_text_field( $_POST['time'] ),
			'end_date'       => isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : null,
			'total_price'    => isset( $_POST['total_price'] ) ? floatval( $_POST['total_price'] ) : 0,
			'status'         => 'pending',
		);

		// Add hotel meta if provided
		$hotel_meta = array();
		if ( isset( $_POST['adults'] ) ) {
			$hotel_meta['adults'] = sanitize_text_field( $_POST['adults'] );
		}
		if ( isset( $_POST['children'] ) ) {
			$hotel_meta['children'] = sanitize_text_field( $_POST['children'] );
		}
		if ( isset( $_POST['room_view'] ) ) {
			$hotel_meta['room_view'] = sanitize_text_field( $_POST['room_view'] );
		}

		if ( ! empty( $hotel_meta ) ) {
			$data['meta_data'] = maybe_serialize( $hotel_meta );
		}

		// Check availability
		if ( ! Appointix_Availability_Model::is_available( $data['post_id'], $data['booking_date'], $data['booking_time'], $data['end_date'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, this slot is no longer available. Please choose another date or time.', 'appointix' ) ) );
		}

		// Simplified booking logic (could be extended to a Bookings Model)
		global $wpdb;
		$table_name = $wpdb->prefix . 'appointix_bookings';
		$result     = $wpdb->insert( $table_name, $data );

		if ( $result ) {
			$booking_id = $wpdb->insert_id;
			Appointix_Emails::send_booking_notifications( $booking_id );

			wp_send_json_success( array( 'message' => __( 'Thank you! Your booking has been received.', 'appointix' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to process booking. Please try again.', 'appointix' ) ) );
		}
	}

	/**
	 * AJAX handler to get apartment content with processed the_content.
	 */
	public function ajax_get_apartment_content() {
		check_ajax_referer( 'appointix_public_nonce', 'nonce' );

		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Post ID missing.' ) );
		}

		$post_id = intval( $_POST['post_id'] );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			wp_send_json_error( array( 'message' => 'Post not found.' ) );
		}

		// Set global post for plugins that rely on it (like Elementor)
		global $post;
		$original_post = $post;
		$post          = get_post( $post_id );
		setup_postdata( $post );

		// Try to get Elementor rendered content if applicable
		$content      = '';
		$is_elementor = false;

		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document && $document->is_built_with_elementor() ) {
				$content      = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $post_id );
				$is_elementor = true;
			}
		}

		// Fallback to standard the_content filter if not Elementor or if Elementor content is empty
		if ( empty( $content ) ) {
			$content = apply_filters( 'the_content', $post->post_content );
		}

		// Check for Elementor CSS with extra safety
		$elementor_css = array();
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			try {
				// Post-specific CSS
				$css_file = new \Elementor\Core\Files\CSS\Post( $post_id );
				if ( $css_file ) {
					$elementor_css[] = $css_file->get_url();
				}

				// Global CSS (Colors, Fonts, etc.) - Check if class exists before instantiating
				if ( class_exists( '\Elementor\Core\Files\CSS\Global_CSS' ) ) {
					$global_css = new \Elementor\Core\Files\CSS\Global_CSS();
					if ( $global_css ) {
						$elementor_css[] = $global_css->get_url();
					}
				}

				// Core Frontend CSS (Layout, Grid, etc.)
				if ( defined( 'ELEMENTOR_ASSETS_URL' ) ) {
					$elementor_css[] = ELEMENTOR_ASSETS_URL . 'css/frontend.min.css';
				}
			} catch ( \Exception $e ) {
				// Silently fail for CSS - missing styles are better than a broken page
				error_log( 'Appointix Elementor CSS error: ' . $e->getMessage() );
			}
		}

		wp_reset_postdata();
		$post = $original_post;

		wp_send_json_success(
			array(
				'content'       => $content,
				'elementor_css' => array_unique( array_filter( $elementor_css ) ),
				'is_elementor'  => $is_elementor,
			)
		);
	}

	/**
	 * AJAX handler to submit a rating for an apartment.
	 */
	public function ajax_submit_rating() {
		check_ajax_referer( 'appointix_public_nonce', 'nonce' );

		$post_id = intval( $_POST['post_id'] );
		$rating  = intval( $_POST['rating'] );

		// Check if already rated (via cookie)
		$cookie_name = 'appointix_rated_' . $post_id;
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			wp_send_json_error( array( 'message' => __( 'You have already rated this property.', 'appointix' ) ) );
		}

		if ( $rating < 1 || $rating > 5 ) {
			wp_send_json_error( array( 'message' => 'Invalid rating.' ) );
		}

		// Get existing rating data
		$current_score = floatval( get_post_meta( $post_id, '_appointix_rating_score', true ) ) ?: 0;
		$current_count = intval( get_post_meta( $post_id, '_appointix_rating_count', true ) ) ?: 0;

		// Calculate new weighted average
		$new_count = $current_count + 1;
		$new_score = ( ( $current_score * $current_count ) + $rating ) / $new_count;

		// Update post meta
		update_post_meta( $post_id, '_appointix_rating_score', round( $new_score, 1 ) );
		update_post_meta( $post_id, '_appointix_rating_count', $new_count );

		// Set cookie to prevent re-rating (30 days)
		setcookie( $cookie_name, '1', time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );

		wp_send_json_success(
			array(
				'message'   => __( 'Thank you for your rating!', 'appointix' ),
				'new_score' => round( $new_score, 1 ),
				'new_count' => $new_count,
			)
		);
	}

	/**
	 * Handle iCal Export requests.
	 */
	public function handle_ical_export() {
		if ( isset( $_GET['appointix_ical_export'] ) && isset( $_GET['post_id'] ) && isset( $_GET['token'] ) ) {
			$post_id = intval( $_GET['post_id'] );
			$token   = sanitize_text_field( $_GET['token'] );
			Appointix_iCal::generate_export( $post_id, $token );
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'appointix-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0' );
		wp_enqueue_style( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13' );
		wp_enqueue_style( 'nice-select', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css', array(), '1.1.0' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/appointix-public.css', array( 'flatpickr', 'nice-select' ), $this->version, 'all' );

		// Force Elementor CSS for single apartments to ensure styling is applied
		if ( is_singular( 'appointix_apartment' ) && class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$post_id  = get_the_ID();
			$css_file = new \Elementor\Core\Files\CSS\Post( $post_id );
			if ( $css_file ) {
				$css_file->enqueue();
			}
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), '4.6.13', true );
		wp_enqueue_script( 'nice-select', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js', array( 'jquery' ), '1.1.0', true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/appointix-public.js', array( 'jquery', 'flatpickr', 'nice-select' ), $this->version, true );

		wp_localize_script(
			$this->plugin_name,
			'appointix_public',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'appointix_public_nonce' ),
			)
		);
	}

	public function render_whatsapp_button() {
		$options = get_option( 'appointix_theme_options' );
		$phone   = isset( $options['whatsapp_number'] ) ? $options['whatsapp_number'] : '';

		if ( empty( $phone ) ) {
			return;
		}

		$url = 'https://wa.me/' . esc_attr( $phone );
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="xio-whatsapp-global" target="_blank" rel="nofollow">
			<i class="fab fa-whatsapp"></i>
		</a>
		<style>
			.xio-whatsapp-global {
				position: fixed !important;
				width: 60px;
				height: 60px;
				bottom: 20px;
				left: 20px;
				background-color: #25D366;
				color: #FFF;
				border-radius: 50px;
				text-align: center;
				font-size: 30px;
				box-shadow: 2px 2px 3px #999;
				z-index: 2147483647;
				display: flex;
				align-items: center;
				justify-content: center;
				text-decoration: none;
				transition: all 0.3s ease;
				animation: xio-pulse-global 2s infinite;
			}
			.xio-whatsapp-global i {
				color: white !important;
				font-family: "Font Awesome 5 Free", "FontAwesome", sans-serif; /* Setup FontAwesome explicitly if needed */
			}
			.xio-whatsapp-global:hover {
				color: #fff;
				transform: scale(1.1);
			}
			@keyframes xio-pulse-global {
				0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
				70% { box-shadow: 0 0 0 10px rgba(37, 211, 102, 0); }
				100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
			}
		</style>
		<?php
	}

}
