<?php

/**
 * iCal Synchronization logic.
 */
class Appointix_iCal
{

    /**
     * Generate iCal content for a specific apartment.
     */
    public static function generate_export($post_id, $token)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        // Verify token from post meta
        $saved_token = get_post_meta($post_id, '_appointix_ical_token', true);

        if (!$saved_token || $saved_token !== $token) {
            wp_die('Invalid iCal export link.', 'Appointix Error', array('response' => 403));
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'appointix_apartment') {
            wp_die('Invalid apartment ID.', 'Appointix Error', array('response' => 404));
        }

        // Get confirmed bookings
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_bookings WHERE post_id = %d AND status IN ('confirmed', 'pending')",
            $post_id
        ));

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Appointix//Booking Plugin//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        foreach ($bookings as $booking) {
            $dtstart = date('Ymd', strtotime($booking->booking_date));
            // For multi-day bookings, end_date might be set. If not, default to next day.
            $end_date_raw = $booking->end_date ? $booking->end_date : date('Y-m-d', strtotime($booking->booking_date . ' +1 day'));
            $dtend = date('Ymd', strtotime($end_date_raw));

            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:appointix-{$booking->id}@{$_SERVER['HTTP_HOST']}\r\n";
            $ical .= "DTSTAMP:" . date('Ymd\THis\Z') . "\r\n";
            $ical .= "DTSTART;VALUE=DATE:{$dtstart}\r\n";
            $ical .= "DTEND;VALUE=DATE:{$dtend}\r\n";
            $ical .= "SUMMARY:Booking - {$booking->customer_name}\r\n";
            $ical .= "DESCRIPTION:Apartment: {$post->post_title}. Status: {$booking->status}\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="sync-apartment-' . $post_id . '.ics"');
        echo $ical;
        exit;
    }

    /**
     * Import external calendar events for an apartment.
     */
    public static function import_external_calendar($post_id)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        $ical_airbnb = get_post_meta($post_id, '_appointix_ical_airbnb', true);
        $ical_booking = get_post_meta($post_id, '_appointix_ical_booking', true);

        if (empty($ical_airbnb) && empty($ical_booking)) {
            return false;
        }

        // Clear existing external bookings for this post
        $wpdb->delete($table_bookings, array('post_id' => $post_id, 'customer_name' => 'External sync'));

        $urls = array_filter(array($ical_airbnb, $ical_booking));
        foreach ($urls as $url) {
            self::fetch_and_process_url($post_id, $url);
        }

        return true;
    }

    private static function fetch_and_process_url($post_id, $url)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';

        // Check if $url is valid
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return;
        }

        $response = wp_remote_get($url, array('timeout' => 30));
        if (is_wp_error($response)) {
            return;
        }

        $content = wp_remote_retrieve_body($response);
        if (empty($content)) {
            return;
        }

        // Simplified iCal parsing
        preg_match_all('/BEGIN:VEVENT.*?END:VEVENT/si', $content, $matches);

        foreach ($matches[0] as $event_text) {
            $dtstart = '';
            $dtend = '';

            // Handle DTSTART;VALUE=DATE:20231201 or DTSTART:20231201T120000Z
            if (preg_match('/DTSTART(?:;VALUE=DATE)?:(\d{8})/i', $event_text, $m)) {
                $dtstart = date('Y-m-d', strtotime($m[1]));
            }

            if (preg_match('/DTEND(?:;VALUE=DATE)?:(\d{8})/i', $event_text, $m)) {
                $dtend = date('Y-m-d', strtotime($m[1]));
            }

            if ($dtstart) {
                // If no end date, it's a one-day block
                if (!$dtend) {
                    $dtend = date('Y-m-d', strtotime($dtstart . ' +1 day'));
                }

                // Insert into bookings table as blocked/confirmed
                $wpdb->insert($table_bookings, array(
                    'post_id' => $post_id,
                    'customer_name' => 'External sync',
                    'customer_email' => 'sync@external.com',
                    'booking_date' => $dtstart,
                    'end_date' => $dtend,
                    'status' => 'confirmed',
                    'total_price' => 0,
                    'meta_data' => 'iCal Sync: ' . ($url == get_post_meta($post_id, '_appointix_ical_airbnb', true) ? 'Airbnb' : 'Booking.com')
                ));
            }
        }
    }

    /**
     * Periodic sync for all apartments.
     */
    public static function sync_all()
    {
        $apartments = get_posts(array(
            'post_type' => 'appointix_apartment',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'publish'
        ));

        foreach ($apartments as $post_id) {
            self::import_external_calendar($post_id);
        }
    }
}

