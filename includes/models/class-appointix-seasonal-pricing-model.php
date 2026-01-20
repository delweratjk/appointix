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
        if (!$end_date || $end_date === $start_date) {
            return self::get_price_for_date($post_id, $start_date);
        }

        $total = 0;
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end); // Non-inclusive of end date

        foreach ($period as $date) {
            $total += self::get_price_for_date($post_id, $date->format('Y-m-d'));
        }

        return $total;
    }
}
