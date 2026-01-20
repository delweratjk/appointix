<?php

/**
 * The class responsible for handling email notifications.
 *
 * @since      1.0.0
 * @package    Appointix
 * @subpackage Appointix/includes
 */
class Appointix_Emails
{

    /**
     * Send notification emails for a new booking.
     *
     * @since    1.0.0
     */
    public static function send_booking_notifications($booking_id)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';
        $table_services = $wpdb->prefix . 'appointix_services';

        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT b.* FROM $table_bookings b WHERE b.id = %d",
            $booking_id
        ));

        if (!$booking)
            return;

        // Try to get service name from services table
        $service_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_services WHERE id = %d", $booking->service_id));

        // If not found, check if it's an apartment post
        if (!$service_name) {
            $apartment = get_post($booking->service_id);
            if ($apartment && $apartment->post_type === 'appointix_apartment') {
                $service_name = $apartment->post_title;
            }
        }

        $booking->service_name = $service_name ?: __('Other Service', 'appointix');

        self::send_admin_notification($booking);
        self::send_customer_notification($booking);
    }

    /**
     * Send notification to the site administrator.
     */
    private static function send_admin_notification($booking)
    {
        $to = get_option('appointix_email_notifications', get_option('admin_email'));
        $subject = sprintf(__('New Booking Received: #%d', 'appointix'), $booking->id);

        ob_start();
        include(plugin_dir_path(__FILE__) . 'emails/admin-new-booking.php');
        $message = ob_get_clean();

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Send notification to the customer.
     */
    private static function send_customer_notification($booking)
    {
        $to = $booking->customer_email;
        $subject = sprintf(__('Booking Confirmation: #%d', 'appointix'), $booking->id);

        ob_start();
        include(plugin_dir_path(__FILE__) . 'emails/customer-booking-confirmation.php');
        $message = ob_get_clean();

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $message, $headers);
    }
}
