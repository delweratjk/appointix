<?php

/**
 * The Apartments model class.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/includes/models
 */
class Appointix_Apartments_Model
{

    /**
     * Get all apartments from CPT.
     *
     * @since    1.0.0
     * @return   array   Array of apartment objects
     */
    public static function get_apartments($args = array())
    {
        $default_args = array(
            'post_type' => 'appointix_apartment',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $query_args = wp_parse_args($args, $default_args);
        $posts = get_posts($query_args);

        $apartments = array();
        foreach ($posts as $post) {
            $apartments[] = self::get_apartment_data($post);
        }

        return $apartments;
    }

    /**
     * Get a single apartment by ID.
     *
     * @param    int     $id     The apartment post ID
     * @return   object|null     Apartment object or null
     */
    public static function get_apartment($id)
    {
        $post = get_post(intval($id));

        if (!$post || $post->post_type !== 'appointix_apartment') {
            return null;
        }

        return self::get_apartment_data($post);
    }

    /**
     * Build apartment data object from post.
     *
     * @param    WP_Post $post   The post object
     * @return   object          Apartment data object
     */
    private static function get_apartment_data($post)
    {
        $data = new stdClass();

        $data->id = $post->ID;
        $data->name = $post->post_title;
        $data->description = $post->post_content;
        $data->excerpt = get_the_excerpt($post);
        $data->permalink = get_permalink($post->ID);
        $data->thumbnail = get_the_post_thumbnail_url($post->ID, 'large');
        $data->thumbnail_medium = get_the_post_thumbnail_url($post->ID, 'medium');
        $data->gallery = get_post_meta($post->ID, '_appointix_gallery', true);

        // Resolve master post ID for shared data (pricing, capacity, location)
        $master_id = Appointix_Seasonal_Pricing_Model::get_master_post_id($post->ID);

        // Custom meta fields
        $data->apartment_type = get_post_meta($master_id, '_appointix_apartment_type', true);
        $data->price_per_night = floatval(get_post_meta($master_id, '_appointix_price_per_night', true));
        $data->bedrooms = intval(get_post_meta($master_id, '_appointix_bedrooms', true));
        $data->bathrooms = intval(get_post_meta($master_id, '_appointix_bathrooms', true));
        $data->max_guests = intval(get_post_meta($master_id, '_appointix_max_guests', true));
        $data->property_summary = get_post_meta($post->ID, '_appointix_property_summary', true);
        $data->amenities = get_post_meta($post->ID, '_appointix_amenities', true);
        $data->location = get_post_meta($master_id, '_appointix_location', true);

        // iCal fields
        $data->ical_import_airbnb = get_post_meta($post->ID, '_appointix_ical_airbnb', true);
        $data->ical_import_booking = get_post_meta($post->ID, '_appointix_ical_booking', true);
        $data->ical_token = get_post_meta($post->ID, '_appointix_ical_token', true);

        // Generate token if not exists
        if (empty($data->ical_token)) {
            $token = wp_generate_password(24, false);
            update_post_meta($post->ID, '_appointix_ical_token', $token);
            $data->ical_token = $token;
        }

        return $data;
    }

    /**
     * Get apartments by type.
     *
     * @param    string  $type   The apartment type (sea_view, mountain_view, etc.)
     * @return   array           Array of apartment objects
     */
    public static function get_apartments_by_type($type)
    {
        $args = array(
            'meta_query' => array(
                array(
                    'key' => '_appointix_apartment_type',
                    'value' => sanitize_text_field($type)
                )
            )
        );

        return self::get_apartments($args);
    }

    /**
     * Get latest apartments.
     *
     * @param    int     $count  Number of apartments to get
     * @return   array           Array of apartment objects
     */
    public static function get_latest_apartments($count = 6)
    {
        return self::get_apartments(array('posts_per_page' => intval($count)));
    }

    /**
     * Get apartment count.
     *
     * @return   int     Number of published apartments
     */
    public static function count_apartments()
    {
        $count = wp_count_posts('appointix_apartment');
        return isset($count->publish) ? $count->publish : 0;
    }

    /**
     * Calculate total price for date range.
     *
     * @param    int     $apartment_id   The apartment ID
     * @param    string  $check_in       Check-in date (Y-m-d)
     * @param    string  $check_out      Check-out date (Y-m-d)
     * @return   array                   Price breakdown
     */
    public static function calculate_price($apartment_id, $check_in, $check_out)
    {
        $apartment_id = Appointix_Seasonal_Pricing_Model::get_master_post_id($apartment_id);
        $apartment = self::get_apartment($apartment_id);

        if (!$apartment || empty($check_in) || empty($check_out)) {
            return array(
                'nights' => 0,
                'per_night' => 0,
                'total' => 0
            );
        }

        $start = new DateTime($check_in);
        $end = new DateTime($check_out);
        $interval = $start->diff($end);
        $nights = $interval->days;

        if ($nights < 1) {
            $nights = 1;
        }

        $price_per_night = $apartment->price_per_night;
        $total = $nights * $price_per_night;

        // Check for seasonal pricing
        $seasonal_price = self::get_seasonal_price($apartment_id, $check_in, $check_out);
        if ($seasonal_price > 0) {
            $total = $seasonal_price;
            $price_per_night = $total / $nights;
        }

        return array(
            'nights' => $nights,
            'per_night' => $price_per_night,
            'total' => $total
        );
    }

    /**
     * Get seasonal pricing if applicable.
     *
     * @param    int     $apartment_id   The apartment ID
     * @param    string  $check_in       Check-in date
     * @param    string  $check_out      Check-out date
     * @return   float                   Seasonal total or 0
     */
    private static function get_seasonal_price($apartment_id, $check_in, $check_out)
    {
        $apartment_id = Appointix_Seasonal_Pricing_Model::get_master_post_id($apartment_id);
        global $wpdb;
        $table_seasonal = $wpdb->prefix . 'appointix_seasonal_pricing';

        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_seasonal'");
        if (!$table_exists) {
            return 0;
        }

        $start = new DateTime($check_in);
        $end = new DateTime($check_out);
        $total = 0;

        while ($start < $end) {
            $date = $start->format('Y-m-d');

            // Check for seasonal price on this date
            $seasonal = $wpdb->get_var($wpdb->prepare(
                "SELECT price FROM $table_seasonal 
                 WHERE post_id = %d 
                 AND start_date <= %s 
                 AND end_date >= %s
                 LIMIT 1",
                $apartment_id,
                $date,
                $date
            ));

            if ($seasonal) {
                $total += floatval($seasonal);
            } else {
                // Use regular price
                $apartment = self::get_apartment($apartment_id);
                $total += $apartment->price_per_night;
            }

            $start->modify('+1 day');
        }

        return $total;
    }
}
