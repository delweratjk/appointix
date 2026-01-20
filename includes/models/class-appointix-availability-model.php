<?php

/**
 * The Availability model class.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/includes/models
 */
class Appointix_Availability_Model
{

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
    public static function is_available($post_id, $date, $time = null, $end_date = null)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        // For apartments, check date range overlap
        $check_start = $date;
        $check_end = $end_date ? $end_date : $date;

        // Check for overlapping bookings
        // Overlap occurs when: existing_start < new_end AND existing_end > new_start
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_bookings 
             WHERE post_id = %d 
             AND status NOT IN ('cancelled', 'rejected')
             AND booking_date < %s 
             AND (COALESCE(end_date, booking_date) > %s)",
            $post_id,
            $check_end,
            $check_start
        ));

        return intval($count) === 0;
    }

    /**
     * Get all booked dates for an apartment.
     *
     * @param    int     $post_id  The apartment ID
     * @return   array             Array of booked date strings
     */
    public static function get_booked_dates($post_id)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT booking_date, end_date FROM $table_bookings 
             WHERE post_id = %d 
             AND status NOT IN ('cancelled', 'rejected')",
            $post_id
        ));

        $dates = array();
        foreach ($results as $row) {
            $start = new DateTime($row->booking_date);
            $end = $row->end_date ? new DateTime($row->end_date) : $start;

            // Add all dates in the range
            while ($start <= $end) {
                $dates[] = $start->format('Y-m-d');
                $start->modify('+1 day');
            }
        }

        return array_unique($dates);
    }
}
