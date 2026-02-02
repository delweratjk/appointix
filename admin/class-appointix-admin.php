<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/admin
 */
class Appointix_Admin
{

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
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Check and update database schema if needed.
     */
    private function check_database_updates() {
        global $wpdb;

        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';

        // Manual column rename if old one exists
        $column_exists = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM $table_seasonal LIKE %s", 'service_id' ) );
        if ( ! empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE $table_seasonal CHANGE service_id post_id mediumint(9) NOT NULL" );
        }

        // Create/Update Seasonal Pricing Table
        $sql_seasonal = "CREATE TABLE IF NOT EXISTS $table_seasonal (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9) NOT NULL,
            price decimal(10,2) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            PRIMARY KEY  (id)
        ) " . $wpdb->get_charset_collate() . ";";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_seasonal );
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu()
    {
        // Run DB update check
        $this->check_database_updates();

        add_menu_page(
            'Appointix Booking',
            'Appointix',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            'dashicons-calendar-alt',
            25
        );

        add_submenu_page(
            $this->plugin_name,
            __('Dashboard', 'appointix'),
            __('Dashboard', 'appointix'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Bookings', 'appointix'),
            __('Bookings', 'appointix'),
            'manage_options',
            $this->plugin_name . '-bookings',
            array($this, 'display_bookings_page')
        );

        /* Old Settings Removed - Merged to Theme Options */

        // Submenu for CPT items
        add_submenu_page(
            $this->plugin_name,
            __('Manage Apartments', 'appointix'),
            __('Manage Apartments', 'appointix'),
            'manage_options',
            'edit.php?post_type=appointix_apartment'
        );

    }

    /**
     * Render the dashboard page.
     */
    public function display_plugin_setup_page()
    {
        include_once(plugin_dir_path(__FILE__) . 'partials/appointix-admin-display.php');
    }

    /**
     * Render the bookings page.
     */
    public function display_bookings_page()
    {
        include_once(plugin_dir_path(__FILE__) . 'partials/appointix-admin-bookings-display.php');
    }

    public function register_cpt()
    {
        // Register Apartments CPT
        $labels = array(
            'name' => _x('Apartments', 'post type general name', 'appointix'),
            'singular_name' => _x('Apartment', 'post type singular name', 'appointix'),
            'menu_name' => _x('Apartments', 'admin menu', 'appointix'),
            'name_admin_bar' => _x('Apartment', 'add new on admin bar', 'appointix'),
            'add_new' => _x('Add New', 'apartment', 'appointix'),
            'add_new_item' => __('Add New Apartment', 'appointix'),
            'new_item' => __('New Apartment', 'appointix'),
            'edit_item' => __('Edit Apartment', 'appointix'),
            'view_item' => __('View Apartment', 'appointix'),
            'all_items' => __('All Apartments', 'appointix'),
            'search_items' => __('Search Apartments', 'appointix'),
            'not_found' => __('No apartments found.', 'appointix'),
            'not_found_in_trash' => __('No apartments found in Trash.', 'appointix')
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => 'appointix',
            'show_in_rest' => true, // Enable Gutenberg block editor
            'query_var' => true,
            'rewrite' => array('slug' => 'apartment'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields')
        );

        register_post_type('appointix_apartment', $args);

        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'add_apartment_meta_boxes'));
        add_action('save_post_appointix_apartment', array($this, 'save_apartment_meta'));
    }

    /**
     * Add meta boxes for apartment details.
     */
    public function add_apartment_meta_boxes()
    {
        add_meta_box(
            'appointix_apartment_details',
            __('Apartment Details', 'appointix'),
            array($this, 'render_apartment_meta_box'),
            'appointix_apartment',
            'normal',
            'high'
        );

        add_meta_box(
            'appointix_apartment_gallery',
            __('Apartment Gallery', 'appointix'),
            array($this, 'render_gallery_meta_box'),
            'appointix_apartment',
            'side',
            'default'
        );

        add_meta_box(
            'appointix_apartment_ical',
            __('iCal Sync', 'appointix'),
            array($this, 'render_ical_meta_box'),
            'appointix_apartment',
            'side',
            'default'
        );
    }

    /**
     * Render the apartment details meta box.
     */
    public function render_apartment_meta_box($post)
    {
        wp_nonce_field('appointix_apartment_meta', 'appointix_apartment_nonce');

        $master_id = Appointix_Seasonal_Pricing_Model::get_master_post_id($post->ID);

        $apartment_type = get_post_meta($master_id, '_appointix_apartment_type', true);
        $pricing_mode = get_post_meta($master_id, '_appointix_pricing_mode', true) ? get_post_meta($master_id, '_appointix_pricing_mode', true) : 'static';
        $price_per_night = get_post_meta($master_id, '_appointix_price_per_night', true);
        $bedrooms = get_post_meta($master_id, '_appointix_bedrooms', true);
        $bathrooms = get_post_meta($master_id, '_appointix_bathrooms', true);
        $max_guests = get_post_meta($master_id, '_appointix_max_guests', true);
        $amenities = get_post_meta($post->ID, '_appointix_amenities', true);
        $location = get_post_meta($master_id, '_appointix_location', true);
        $property_summary = get_post_meta($post->ID, '_appointix_property_summary', true);
        $key_features = get_post_meta($post->ID, '_appointix_key_features', true);

        $apartment_types = array(
            'sea_view' => __('Sea View', 'appointix'),
            'mountain_view' => __('Mountain View', 'appointix'),
            'city_view' => __('City View', 'appointix'),
            'garden_view' => __('Garden View', 'appointix'),
            'pool_view' => __('Pool View', 'appointix'),
            'standard' => __('Standard', 'appointix')
        );
        ?>
        <style>
            .appointix-meta-row {
                margin-bottom: 15px;
            }

            .appointix-meta-row label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }

            .appointix-meta-row input[type="text"],
            .appointix-meta-row input[type="number"],
            .appointix-meta-row select,
            .appointix-meta-row textarea {
                width: 100%;
                padding: 8px;
            }

            .appointix-meta-columns {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
            }

            .appointix-meta-col {
                flex: 1;
                min-width: 200px;
            }
        </style>
        <div class="appointix-meta-columns">
            <div class="appointix-meta-col">
                <div class="appointix-meta-row">
                    <label for="appointix_apartment_type"><?php _e('Apartment Type', 'appointix'); ?></label>
                    <select name="appointix_apartment_type" id="appointix_apartment_type">
                        <?php foreach ($apartment_types as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($apartment_type, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="appointix-meta-row">
                    <label for="appointix_pricing_mode"><?php _e('Pricing Mode', 'appointix'); ?></label>
                    <select name="appointix_pricing_mode" id="appointix_pricing_mode">
                        <option value="static" <?php selected($pricing_mode, 'static'); ?>><?php _e('Static Price (Per Night)', 'appointix'); ?></option>
                        <option value="dynamic" <?php selected($pricing_mode, 'dynamic'); ?>><?php _e('Dynamic Calendar Pricing', 'appointix'); ?></option>
                    </select>
                </div>
                <div class="appointix-meta-row" id="static-pricing-row">
                    <label for="appointix_price_per_night"><?php _e('Price Per Night ($)', 'appointix'); ?></label>
                    <input type="number" name="appointix_price_per_night" id="appointix_price_per_night"
                        value="<?php echo esc_attr($price_per_night); ?>" step="0.01" min="0">
                </div>
                <div class="appointix-meta-row">
                    <label for="appointix_location"><?php _e('Location', 'appointix'); ?></label>
                    <input type="text" name="appointix_location" id="appointix_location"
                        value="<?php echo esc_attr($location); ?>"
                        placeholder="<?php _e('e.g., Miami Beach, FL', 'appointix'); ?>">
                </div>
            </div>
            <div class="appointix-meta-col">
                <div class="appointix-meta-row">
                    <label for="appointix_bedrooms"><?php _e('Bedrooms', 'appointix'); ?></label>
                    <input type="number" name="appointix_bedrooms" id="appointix_bedrooms"
                        value="<?php echo esc_attr($bedrooms); ?>" min="0">
                </div>
                <div class="appointix-meta-row">
                    <label for="appointix_bathrooms"><?php _e('Bathrooms', 'appointix'); ?></label>
                    <input type="number" name="appointix_bathrooms" id="appointix_bathrooms"
                        value="<?php echo esc_attr($bathrooms); ?>" min="0">
                </div>
                <div class="appointix-meta-row">
                    <label for="appointix_max_guests"><?php _e('Max Guests', 'appointix'); ?></label>
                    <input type="number" name="appointix_max_guests" id="appointix_max_guests"
                        value="<?php echo esc_attr($max_guests); ?>" min="1">
                </div>
            </div>
        </div>
        <div class="appointix-meta-row">
            <label for="appointix_property_summary"><?php _e('About This Property (Short Description)', 'appointix'); ?></label>
            <textarea name="appointix_property_summary" id="appointix_property_summary" rows="3"
                placeholder="<?php _e('Brief description shown in listings and modals...', 'appointix'); ?>"><?php echo esc_textarea($property_summary); ?></textarea>
        </div>
        <div class="appointix-meta-row">
            <label for="appointix_amenities"><?php _e('Amenities (comma-separated)', 'appointix'); ?></label>
            <textarea name="appointix_amenities" id="appointix_amenities" rows="3"
                placeholder="<?php _e('e.g., WiFi, Air Conditioning, Kitchen, Pool, Parking', 'appointix'); ?>"><?php echo esc_textarea($amenities); ?></textarea>
        </div>
        <div class="appointix-meta-row">
            <label for="appointix_key_features"><?php _e('Key Features (one per line)', 'appointix'); ?></label>
            <div style="display:flex; gap:10px; align-items:flex-start; margin-bottom:5px;">
                <textarea name="appointix_key_features" id="appointix_key_features" rows="6" style="flex:1;"
                    placeholder="<?php _e("e.g.\n50 m²\nTwo bedrooms\nSea View", "appointix"); ?>"><?php echo esc_textarea($key_features); ?></textarea>
                <button type="button" id="appointix-load-features-default" class="button">
                    <?php _e('Load Defaults', 'appointix'); ?>
                </button>
            </div>
            <p class="description"><?php _e('These features appear as a checklist on the single apartment page. If empty, defaults for the selected type will be used.', 'appointix'); ?></p>
        </div>

        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;">

        <h3><?php _e('Dynamic Calendar Pricing', 'appointix'); ?></h3>
        <p class="description"><?php _e('Set special rates for specific date ranges. These will override the base price per night.', 'appointix'); ?></p>
        
        <div id="seasonal-pricing-container" style="margin-top: 15px;">
            <input type="hidden" id="appointix_post_id" value="<?php echo $post->ID; ?>">
            <div class="seasonal-controls" style="display: flex; gap: 10px; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;"><?php _e('Start Date', 'appointix'); ?></label>
                    <input type="text" id="seasonal_start" class="appointix-datepicker" placeholder="YYYY-MM-DD">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;"><?php _e('End Date', 'appointix'); ?></label>
                    <input type="text" id="seasonal_end" class="appointix-datepicker" placeholder="YYYY-MM-DD">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;"><?php _e('Price ($)', 'appointix'); ?></label>
                    <input type="number" id="seasonal_price" step="0.01" min="0" placeholder="0.00">
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="button" id="add-seasonal-price-btn" class="button button-primary" style="height: 40px;"><?php _e('Add Rate', 'appointix'); ?></button>
                </div>
            </div>

            <div id="seasonal-rates-list">
                <!-- Loaded via JS -->
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                if ($('.appointix-datepicker').length > 0) {
                    $('.appointix-datepicker').datepicker({
                        dateFormat: 'yy-mm-dd',
                        minDate: 0
                    });
                }
            });
        </script>
        <?php
    }

    /**
     * Render the gallery meta box.
     */
    public function render_gallery_meta_box($post)
    {
        $gallery = get_post_meta($post->ID, '_appointix_gallery', true);
        $gallery_ids = $gallery ? explode(',', $gallery) : array();
        ?>
        <div id="appointix-gallery-container">
            <?php foreach ($gallery_ids as $id):
                $img_url = wp_get_attachment_image_url(intval($id), 'thumbnail');
                if ($img_url):
                    ?>
                    <div class="appointix-gallery-item" data-id="<?php echo esc_attr($id); ?>">
                        <img src="<?php echo esc_url($img_url); ?>">
                        <span class="remove-gallery-image">&times;</span>
                    </div>
                <?php endif; endforeach; ?>
        </div>
        <input type="hidden" name="appointix_gallery" id="appointix_gallery" value="<?php echo esc_attr($gallery); ?>">
        <div id="appointix_gallery_btn_container">
            <button type="button" class="button" id="appointix-add-gallery-images">
                <i class="dashicons dashicons-images-alt2" style="margin-top:4px;"></i>
                <?php _e('Add Gallery Images', 'appointix'); ?>
            </button>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                var frame;
                $('#appointix-add-gallery-images').on('click', function (e) {
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({
                        title: '<?php _e('Select Gallery Images', 'appointix'); ?>',
                        multiple: true,
                        library: { type: 'image' }
                    });
                    frame.on('select', function () {
                        var selection = frame.state().get('selection');
                        var ids = $('#appointix_gallery').val() ? $('#appointix_gallery').val().split(',') : [];
                        selection.each(function (attachment) {
                            if (ids.indexOf(attachment.id.toString()) === -1) {
                                ids.push(attachment.id);
                                $('#appointix-gallery-container').append(
                                    '<div class="appointix-gallery-item" data-id="' + attachment.id + '">' +
                                    '<img src="' + (attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.url) + '">' +
                                    '<span class="remove-gallery-image">&times;</span></div>'
                                );
                            }
                        });
                        $('#appointix_gallery').val(ids.join(','));
                    });
                    frame.open();
                });
                $(document).on('click', '.remove-gallery-image', function () {
                    var item = $(this).closest('.appointix-gallery-item');
                    var id = item.data('id');
                    var ids = $('#appointix_gallery').val().split(',').filter(function (i) { return i != id; });
                    $('#appointix_gallery').val(ids.join(','));
                    item.remove();
                });

                // Make gallery sortable if possible
                if ($.fn.sortable) {
                    $('#appointix-gallery-container').sortable({
                        update: function () {
                            var ids = [];
                            $('.appointix-gallery-item').each(function () {
                                ids.push($(this).data('id'));
                            });
                            $('#appointix_gallery').val(ids.join(','));
                        }
                    });
                }
            });
        </script>
        <?php
    }

    /**
     * Render the iCal sync meta box.
     */
    public function render_ical_meta_box($post)
    {
        $ical_airbnb = get_post_meta($post->ID, '_appointix_ical_airbnb', true);
        $ical_booking = get_post_meta($post->ID, '_appointix_ical_booking', true);
        $ical_token = get_post_meta($post->ID, '_appointix_ical_token', true);

        if (empty($ical_token) && $post->post_status === 'publish') {
            $ical_token = wp_generate_password(24, false);
            update_post_meta($post->ID, '_appointix_ical_token', $ical_token);
        }

        $export_url = $ical_token ? add_query_arg(array(
            'appointix_ical_export' => 1,
            'service_id' => $post->ID,
            'token' => $ical_token
        ), home_url('/')) : '';
        ?>
        <div class="appointix-meta-row">
            <label for="appointix_ical_airbnb"><?php _e('Airbnb iCal URL', 'appointix'); ?></label>
            <input type="url" name="appointix_ical_airbnb" id="appointix_ical_airbnb"
                value="<?php echo esc_url($ical_airbnb); ?>" style="width:100%;">
        </div>
        <div class="appointix-meta-row" style="margin-top:10px;">
            <label for="appointix_ical_booking"><?php _e('Booking.com iCal URL', 'appointix'); ?></label>
            <input type="url" name="appointix_ical_booking" id="appointix_ical_booking"
                value="<?php echo esc_url($ical_booking); ?>" style="width:100%;">
        </div>
        <?php if ($export_url): ?>
            <div style="margin-top:15px;padding-top:10px;border-top:1px solid #ddd;">
                <label><?php _e('Export URL (share with other platforms)', 'appointix'); ?></label>
                <input type="text" readonly value="<?php echo esc_url($export_url); ?>" style="width:100%;font-size:11px;"
                    onclick="this.select()">
            </div>
        <?php endif; ?>
    <?php
    }

    /**
     * Save apartment meta data.
     */
    public function save_apartment_meta($post_id)
    {
        if (
            !isset($_POST['appointix_apartment_nonce']) ||
            !wp_verify_nonce($_POST['appointix_apartment_nonce'], 'appointix_apartment_meta')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            'appointix_apartment_type' => '_appointix_apartment_type',
            'appointix_pricing_mode' => '_appointix_pricing_mode',
            'appointix_price_per_night' => '_appointix_price_per_night',
            'appointix_bedrooms' => '_appointix_bedrooms',
            'appointix_bathrooms' => '_appointix_bathrooms',
            'appointix_max_guests' => '_appointix_max_guests',
            'appointix_property_summary' => '_appointix_property_summary',
            'appointix_amenities' => '_appointix_amenities',
            'appointix_location' => '_appointix_location',
            'appointix_gallery' => '_appointix_gallery',
            'appointix_key_features' => '_appointix_key_features',
            'appointix_ical_airbnb' => '_appointix_ical_airbnb',
            'appointix_ical_booking' => '_appointix_ical_booking'
        );

        foreach ($fields as $post_key => $meta_key) {
            if (isset($_POST[$post_key])) {
                if (strpos($meta_key, 'ical') !== false) {
                    $value = esc_url_raw($_POST[$post_key]);
                } elseif ($meta_key === '_appointix_price_per_night') {
                    $value = floatval($_POST[$post_key]);
                } elseif (in_array($meta_key, array('_appointix_bedrooms', '_appointix_bathrooms', '_appointix_max_guests'))) {
                    $value = intval($_POST[$post_key]);
                } elseif ( in_array( $meta_key, array( '_appointix_property_summary', '_appointix_amenities', '_appointix_key_features' ) ) ) {
                    $value = sanitize_textarea_field( $_POST[$post_key] );
                } else {
                    $value = sanitize_text_field( $_POST[$post_key] );
                }
                update_post_meta($post_id, $meta_key, $value);

                // Sync base price and pricing mode across translations and manual groups
                if (in_array($meta_key, array('_appointix_price_per_night', '_appointix_pricing_mode'))) {
                    // 1. Sync via Polylang
                    if (function_exists('pll_get_post_translations')) {
                        $translations = pll_get_post_translations($post_id);
                        foreach ($translations as $lang => $translated_id) {
                            if ($translated_id != $post_id) {
                                update_post_meta($translated_id, $meta_key, $value);
                            }
                        }
                    }

                    // 2. Sync via Manual Pricing Groups
                    $options = get_option('appointix_theme_options', array());
                    $groups = isset($options['pricing_groups']) ? $options['pricing_groups'] : array();
                    if (!empty($groups)) {
                        foreach ($groups as $group) {
                            $primary_id = intval($group['primary_id']);
                            $linked_ids = isset($group['linked_ids']) ? $group['linked_ids'] : array();
                            
                            if (is_string($linked_ids)) {
                                $linked_ids = array_map('trim', explode(',', $linked_ids));
                            }
                            
                            if ($post_id == $primary_id || in_array((string)$post_id, $linked_ids) || in_array((int)$post_id, $linked_ids)) {
                                // Sync to primary
                                if ($primary_id != $post_id) {
                                    update_post_meta($primary_id, $meta_key, $value);
                                }
                                // Sync to all linked
                                foreach ($linked_ids as $lid) {
                                    $lid = intval($lid);
                                    if ($lid > 0 && $lid != $post_id) {
                                        update_post_meta($lid, $meta_key, $value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Generate iCal token if not exists
        $existing_token = get_post_meta($post_id, '_appointix_ical_token', true);
        if (empty($existing_token)) {
            update_post_meta($post_id, '_appointix_ical_token', wp_generate_password(24, false));
        }
    }

    /**
     * Render the settings page.
     */
    public function display_settings_page()
    {
        include_once(plugin_dir_path(__FILE__) . 'partials/appointix-admin-settings-display.php');
    }

    /**
     * AJAX handler to update booking status.
     */
    public function ajax_update_booking_status()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'appointix')));
        }

        $id = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        $result = Appointix_Bookings_Model::update_status($id, $status);

        if ($result !== false) {
            // Trigger Email
            Appointix_Emails::send_status_update_email($id, $status);

            // Get updated stats
            $stats = Appointix_Bookings_Model::get_stats();

            wp_send_json_success(array(
                'message' => __('Status updated successfully!', 'appointix'),
                'stats'   => $stats
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to update status', 'appointix')));
        }
    }

    /**
     * AJAX handler to manually trigger iCal sync for a service.
     */
    public function ajax_manual_ical_sync()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'appointix')));
        }

        $id = intval($_POST['id']);
        $result = Appointix_iCal::import_external_calendar($id);

        if ($result) {
            wp_send_json_success(array('message' => __('Calendar synced successfully!', 'appointix')));
        } else {
            wp_send_json_error(array('message' => __('Failed to sync calendar. Check the URL.', 'appointix')));
        }
    }

    /**
     * AJAX handler to delete a booking.
     */
    public function ajax_delete_booking()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'appointix')));
        }

        $id = intval($_POST['id']);
        $result = Appointix_Bookings_Model::delete_booking($id);

        if ($result) {
            // Get updated stats
            $stats = Appointix_Bookings_Model::get_stats();

            wp_send_json_success(array(
                'message' => __('Booking deleted successfully!', 'appointix'),
                'stats'   => $stats
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete booking', 'appointix')));
        }
    }

    /**
     * AJAX handler to get refresh booking list HTML.
     */
    public function ajax_get_bookings()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';
        $filter_args = array();
        if ($status !== 'active' && $status !== 'all') {
            $filter_args['status'] = $status;
        } elseif ($status === 'all') {
            $filter_args['status'] = 'all'; // Pass 'all' to model explicitly
        }

        $bookings = Appointix_Bookings_Model::get_bookings($filter_args);
        
        // Pass filtered status to the view
        $current_tab = $status; 
        
        ob_start();
        include(plugin_dir_path(__FILE__) . 'partials/appointix-admin-bookings-list.php');
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX handler to restore a booking from trash.
     */
    public function ajax_restore_booking()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'appointix')));
        }

        $id = intval($_POST['id']);
        $result = Appointix_Bookings_Model::restore_booking($id);

        if ($result !== false) {
            $stats = Appointix_Bookings_Model::get_stats();
            wp_send_json_success(array(
                'message' => __('Booking restored!', 'appointix'),
                'stats'   => $stats
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to restore booking', 'appointix')));
        }
    }

    /**
     * AJAX handler to permanently delete a booking.
     */
    public function ajax_permanent_delete_booking()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'appointix')));
        }

        $id = intval($_POST['id']);
        $result = Appointix_Bookings_Model::permanent_delete_booking($id);

        if ($result !== false) {
            $stats = Appointix_Bookings_Model::get_stats();
            wp_send_json_success(array(
                'message' => __('Booking permanently deleted', 'appointix'),
                'stats'   => $stats
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete booking', 'appointix')));
        }
    }

    public function ajax_delete_seasonal_price()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'appointix')));
        }

        $id = intval($_POST['id']);
        global $wpdb;
        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';
        $result = $wpdb->delete($table_seasonal, array('id' => $id), array('%d'));

        if ($result !== false) {
            wp_send_json_success(array('message' => __('Rate deleted successfully!', 'appointix')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete rate', 'appointix')));
        }
    }

    /**
     * AJAX handler to save settings.
     */
    public function ajax_save_settings()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'appointix')));
        }

        update_option('appointix_currency', sanitize_text_field($_POST['appointix_currency']));
        update_option('appointix_email_notifications', sanitize_email($_POST['appointix_email_notifications']));

        wp_send_json_success(array('message' => __('Settings saved successfully!', 'appointix')));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/appointix-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('nice-select-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css', array(), '1.1.0');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('nice-select-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js', array('jquery'), '1.1.0', true);
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/appointix-admin.js', array('jquery', 'jquery-ui-datepicker', 'nice-select-js'), $this->version, false);

        wp_localize_script($this->plugin_name, 'appointix_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('appointix_admin_nonce'),
            'confirm_delete' => __('Are you sure you want to delete this?', 'appointix'),
        ));

    }

    public function ajax_add_seasonal_price()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error();

        global $wpdb;
        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';
        $post_id = intval($_POST['post_id']);
        $post_id = Appointix_Seasonal_Pricing_Model::get_master_post_id($post_id);

        $wpdb->insert($table_seasonal, array(
            'post_id' => $post_id,
            'start_date' => sanitize_text_field($_POST['start']),
            'end_date' => sanitize_text_field($_POST['end']),
            'price' => floatval($_POST['price'])
        ));

        wp_send_json_success(array('message' => __('Seasonal price added!', 'appointix')));
    }

    public function ajax_get_seasonal_prices()
    {
        check_ajax_referer('appointix_admin_nonce', 'nonce');
        global $wpdb;
        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';
        $post_id = intval($_POST['post_id']);
        $post_id = Appointix_Seasonal_Pricing_Model::get_master_post_id($post_id);

        $prices = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_seasonal WHERE post_id = %d ORDER BY start_date ASC", $post_id));
        wp_send_json_success($prices);
    }
}
