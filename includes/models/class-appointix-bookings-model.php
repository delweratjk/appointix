<?php

/**
 * The Bookings model class.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/includes/models
 */
class Appointix_Bookings_Model
{

    /**
     * Get all bookings from the database.
     * Supports both legacy services and new apartment CPT.
     *
     * @since    1.0.0
     */
    public static function get_bookings()
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        // Get bookings
        $bookings = $wpdb->get_results("
            SELECT b.* 
            FROM $table_bookings b
            ORDER BY b.created_at DESC
        ");

        // Enrich bookings with apartment data
        foreach ($bookings as $booking) {
            $post = get_post($booking->post_id);
            if ($post && $post->post_type === 'appointix_apartment') {
                $booking->service_name = $post->post_title;
                $booking->service_type = 'apartment';

                // Get apartment type meta
                $apartment_type = get_post_meta($post->ID, '_appointix_apartment_type', true);
                if ($apartment_type) {
                    $booking->apartment_type = $apartment_type;
                }
            } else {
                $booking->service_name = $booking->post_id ? sprintf( __( 'Deleted Apartment (ID: %d)', 'appointix' ), $booking->post_id ) : __( 'Unknown Apartment', 'appointix' );
                $booking->service_type = '';
            }
        }

        return $bookings;
    }

    /**
     * Get a single booking by ID.
     *
     * @param int $id The booking ID
     * @return object|null
     */
    public static function get_booking($id)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        $booking = $wpdb->get_row($wpdb->prepare("
            SELECT b.* 
            FROM $table_bookings b
            WHERE b.id = %d
        ", $id));

        if ($booking) {
            // Check if it's an apartment CPT
            $post = get_post($booking->post_id);
            if ($post && $post->post_type === 'appointix_apartment') {
                $booking->service_name = $post->post_title;
                $booking->service_type = 'apartment';
            } else {
                $booking->service_name = __('Unknown', 'appointix');
                $booking->service_type = '';
            }
        }

        return $booking;
    }

    /**
     * Update booking status.
     *
     * @since    1.0.0
     */
    public static function update_status($id, $status)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointix_bookings';

        return $wpdb->update(
            $table_name,
            array('status' => sanitize_text_field($status)),
            array('id' => intval($id))
        );
    }

    /**
     * Get count of bookings.
     */
    public static function count_bookings()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointix_bookings';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    /**
     * Get pending bookings count.
     */
    public static function count_pending_bookings()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointix_bookings';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'pending'");
    }

    /**
     * Delete a booking.
     *
     * @since    1.0.0
     */
    public static function delete_booking($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointix_bookings';
        return $wpdb->delete($table_name, array('id' => intval($id)));
    }
}