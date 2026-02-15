<?php

/**
 * The Availability model class.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/includes/models
 */
class Appointix_Availability_Model {

    /**
     * Get all related apartment IDs (Polylang translations + Pricing Groups).
     *
     * @param int $post_id The apartment ID.
     * @return array Array of all related post IDs.
     */
    public static function get_grouped_apartment_ids( $post_id ) {
        $post_ids = array( $post_id );

        // 1. Get IDs via Polylang translations
        if ( function_exists( 'pll_get_post_translations' ) ) {
            $translations = pll_get_post_translations( $post_id );
            if ( ! empty( $translations ) ) {
                $post_ids = array_merge( $post_ids, array_values( $translations ) );
            }
        }

        // 2. Get IDs via Pricing Synchronization Groups (from Theme Options)
        $options = get_option( 'appointix_theme_options', array() );
        $groups = isset( $options['pricing_groups'] ) ? $options['pricing_groups'] : array();
        
        if ( ! empty( $groups ) && is_array( $groups ) ) {
            foreach ( $groups as $group ) {
                $primary_id = isset( $group['primary_id'] ) ? intval( $group['primary_id'] ) : 0;
                $linked_ids = isset( $group['linked_ids'] ) ? $group['linked_ids'] : array();
                
                // Handle comma-separated string format
                if ( is_string( $linked_ids ) ) {
                    $linked_ids = array_map( 'trim', explode( ',', $linked_ids ) );
                }
                $linked_ids = array_map( 'intval', $linked_ids );
                
                // Check if current post_id (or any translation) is in this group
                $group_apartments = array_merge( array( $primary_id ), $linked_ids );
                $group_apartments = array_filter( $group_apartments );
                
                $intersection = array_intersect( $post_ids, $group_apartments );
                if ( ! empty( $intersection ) ) {
                    // Merge all apartments from this group
                    $post_ids = array_merge( $post_ids, $group_apartments );
                }
            }
        }

        return array_unique( array_filter( $post_ids ) );
    }

    /**
     * Check if an apartment is available on a specific date range.
     *
     * @since    1.0.0
     * @param    int     $post_id     The apartment ID
     * @param    string  $date        The start/booking date (Y-m-d)
     * @param    string  $time        The booking time (H:i) - optional
     * @param    string  $end_date    The end date for range bookings (Y-m-d) - optional
     * @return   bool                 True if available, false if not
     */
    public static function is_available( $post_id, $date, $time = null, $end_date = null ) {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        $check_start = $date;
        $check_end = $end_date ? $end_date : $date;

        // Get all related IDs (Polylang + Pricing Groups)
        $post_ids = self::get_grouped_apartment_ids( $post_id );
        $placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

        // Check for overlapping bookings
        // FIX: Exclude 'trash' status to prevent deleted bookings from blocking dates
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_bookings 
             WHERE post_id IN ($placeholders) 
             AND status NOT IN ('cancelled', 'rejected', 'trash')
             AND booking_date < %s 
             AND (COALESCE(end_date, booking_date) > %s)",
            array_merge( $post_ids, array( $check_end, $check_start ) )
        ) );

        if ( intval( $count ) > 0 ) {
            return false;
        }

        // Check Dynamic Pricing Rules Coverage
        $pricing_mode = get_post_meta( $post_id, '_appointix_pricing_mode', true );
        if ( $pricing_mode === 'dynamic' ) {
            // FIX: calculate_total now falls back to base price instead of returning 0
            $price_check = Appointix_Seasonal_Pricing_Model::calculate_total( $post_id, $date, $end_date );
            if ( $price_check <= 0 ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all booked dates for an apartment.
     *
     * @param    int     $post_id  The apartment ID
     * @return   array             Array of booked date strings
     */
    public static function get_booked_dates( $post_id ) {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        // Get all related IDs (Polylang + Pricing Groups)
        $post_ids = self::get_grouped_apartment_ids( $post_id );
        $placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT booking_date, end_date FROM $table_bookings 
             WHERE post_id IN ($placeholders) 
             AND status NOT IN ('cancelled', 'rejected', 'trash')",
            $post_ids
        ) );

        $dates = array();
        foreach ( $results as $row ) {
            $start = new DateTime( $row->booking_date );
            $end = $row->end_date ? new DateTime( $row->end_date ) : $start;

            while ( $start <= $end ) {
                $dates[] = $start->format( 'Y-m-d' );
                $start->modify( '+1 day' );
            }
        }

        return array_unique( $dates );
    }
}
