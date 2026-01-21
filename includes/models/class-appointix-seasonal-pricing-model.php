<?php

/**
 * Seasonal Pricing model class.
 */
class Appointix_Seasonal_Pricing_Model
{
    /**
     * Get the base price for an apartment on a specific date.
     */
    public static function get_price_for_date($post_id, $date)
    {
        global $wpdb;
        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';

        // 1. Check if there's a seasonal price overriding this date
        $seasonal_price = $wpdb->get_var($wpdb->prepare(
            "SELECT price FROM $table_seasonal 
             WHERE post_id = %d AND %s BETWEEN start_date AND end_date 
             ORDER BY price DESC LIMIT 1",
            $post_id,
            $date
        ));

        if ($seasonal_price !== null) {
            return floatval($seasonal_price);
        }

        // 2. Fallback to base price from apartment meta
        $base_price = get_post_meta($post_id, '_appointix_price_per_night', true);

        return floatval($base_price);
    }

    /**
     * Calculate total price for a date range.
     */
    public static function calculate_total($post_id, $start_date, $end_date)
    {
        $mode = get_post_meta($post_id, '_appointix_pricing_mode', true) ? get_post_meta($post_id, '_appointix_pricing_mode', true) : 'static';

        if ( $mode === 'static' ) {
            $base_price = floatval( get_post_meta($post_id, '_appointix_price_per_night', true) );
            
             // Calculate nights
             if (!$end_date || $end_date === $start_date) {
                 return $base_price;
             }
             $start = new DateTime($start_date);
             $end = new DateTime($end_date);
             $nights = $start->diff($end)->days;
             if ($nights == 0) $nights = 1;
             
             return $base_price * $nights;
        }

        // Dynamic Mode: Must match rules strictly
        if (!$end_date || $end_date === $start_date) {
            $price = self::get_seasonal_price_only($post_id, $start_date);
            return $price !== false ? $price : 0; // 0 effectively means unavailable
        }

        $total = 0;
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $date) {
            $daily_price = self::get_seasonal_price_only($post_id, $date->format('Y-m-d'));
            if ($daily_price === false) {
                return 0; // Invalid range, gap in rules
            }
            $total += $daily_price;
        }

        return $total;
    }

    /**
     * Get STRICT seasonal price. Returns false if no rule exists.
     */
    private static function get_seasonal_price_only($post_id, $date) {
        global $wpdb;
        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';

        $seasonal_price = $wpdb->get_var($wpdb->prepare(
            "SELECT price FROM $table_seasonal 
             WHERE post_id = %d AND %s BETWEEN start_date AND end_date 
             ORDER BY price DESC LIMIT 1",
            $post_id,
            $date
        ));

        if ($seasonal_price !== null) {
            return floatval($seasonal_price);
        }
        return false;
    }
}
