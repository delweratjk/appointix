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
    /**
     * Get all bookings from the database.
     *
     * @param array $args Filter arguments.
     * @since    1.0.0
     */
    public static function get_bookings($args = array())
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        // Default query: exclude trash unless specifically requested
        $where = "WHERE status != 'trash'";
        
        if ( isset( $args['status'] ) ) {
            if ( $args['status'] === 'trash' ) {
                $where = "WHERE status = 'trash'";
            } elseif ( $args['status'] === 'all' ) {
                $where = "WHERE status != 'trash'";
            } else {
                $where = $wpdb->prepare( "WHERE status = %s", $args['status'] );
            }
        }

        // Get bookings
        $bookings = $wpdb->get_results("
            SELECT b.* 
            FROM $table_bookings b
            $where
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
     * Get count of active bookings (not trash).
     */
    public static function count_bookings()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointix_bookings';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status != 'trash'");
    }

    /**
     * Get booking stats by status.
     */
    public static function get_stats() 
    {
        $total     = self::count_bookings();
        $pending   = self::count_bookings_by_status('pending');
        $confirmed = self::count_bookings_by_status('approved') + self::count_bookings_by_status('confirmed');
        $completed = self::count_bookings_by_status('completed');
        $cancelled = self::count_bookings_by_status('cancelled');
        $trash     = self::count_bookings_by_status('trash');
        
        return array(
            'total'     => $total,
            'pending'   => $pending,
            'confirmed' => $confirmed,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'trash'     => $trash
        );
    }

    /**
     * Count bookings by status.
     */
    public static function count_bookings_by_status($status) 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointix_bookings';
        return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE status = %s", $status));
    }

    /**
     * Soft delete a booking (Move to Trash).
     */
    public static function delete_booking($id)
    {
        return self::update_status($id, 'trash');
    }

    /**
     * Restore a booking from Trash.
     */
    public static function restore_booking($id)
    {
        return self::update_status($id, 'pending');
    }

    /**
     * Permanently delete a booking.
     */
    public static function permanent_delete_booking($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointix_bookings';
        return $wpdb->delete($table_name, array('id' => intval($id)));
    }
}