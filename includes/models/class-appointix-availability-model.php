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

        // Get all translated IDs to check availability across all languages
        $post_ids = array($post_id);

        // 1. Get IDs via Manual Pricing Groups (Theme Options)
        $options = get_option('appointix_theme_options', array());
        $groups = isset($options['pricing_groups']) ? $options['pricing_groups'] : array();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $primary_id = intval($group['primary_id']);
                $linked_ids = isset($group['linked_ids']) ? $group['linked_ids'] : array();
                
                if (is_string($linked_ids)) {
                    $linked_ids = array_map('intval', explode(',', $linked_ids));
                }
                
                if ($post_id == $primary_id || in_array($post_id, $linked_ids)) {
                    $post_ids[] = $primary_id;
                    $post_ids = array_merge($post_ids, $linked_ids);
                    break; 
                }
            }
        }

        // 2. Get IDs via Polylang
        if (function_exists('pll_get_post_translations')) {
            $translations = pll_get_post_translations($post_id);
            if (!empty($translations)) {
                $post_ids = array_merge($post_ids, array_values($translations));
            }
        }

        $post_ids = array_unique(array_filter($post_ids));
        $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));

        // Check for overlapping bookings
        // Overlap occurs when: existing_start < new_end AND existing_end > new_start
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_bookings 
             WHERE post_id IN ($placeholders) 
             AND status NOT IN ('cancelled', 'rejected')
             AND booking_date < %s 
             AND (COALESCE(end_date, booking_date) > %s)",
            array_merge($post_ids, array($check_end, $check_start))
        ));

        if (intval($count) > 0) {
            return false;
        }

        // Check Dynamic Pricing Rules Coverage
        $pricing_mode = get_post_meta($post_id, '_appointix_pricing_mode', true);
        if ($pricing_mode === 'dynamic') {
            // We use calculate_total to validate coverage. It returns 0 if gap exists.
            $price_check = Appointix_Seasonal_Pricing_Model::calculate_total($post_id, $date, $end_date);
            if ($price_check <= 0) {
                return false; // Unavailable due to missing price rules
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
