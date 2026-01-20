<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/includes
 */
class Appointix_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Bookings Table
        $table_bookings = $wpdb->prefix . 'appointix_bookings';
        $sql_bookings = "CREATE TABLE $table_bookings (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9) NOT NULL,
            customer_name varchar(100) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_phone varchar(20) DEFAULT NULL,
            booking_date date NOT NULL,
            booking_time time DEFAULT NULL,
            end_date date DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            total_price decimal(10,2) NOT NULL,
            meta_data text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Seasonal Pricing Table
        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';
        $sql_seasonal = "CREATE TABLE $table_seasonal (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9) NOT NULL,
            price decimal(10,2) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_bookings);
        dbDelta($sql_seasonal);
    }

}
