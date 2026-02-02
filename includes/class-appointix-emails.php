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
     */
    public static function send_booking_notifications($booking_id)
    {
        $booking = self::get_booking_data($booking_id);
        if (!$booking) return;

        self::send_email('admin_new', $booking);
        self::send_email('customer_pending', $booking);
    }

    /**
     * Send status update email to customer.
     */
    public static function send_status_update_email($booking_id, $status)
    {
        $booking = self::get_booking_data($booking_id);
        if (!$booking) return;

        $key = '';
        switch ($status) {
            case 'confirmed':
            case 'approved': // Handle both terms
                $key = 'customer_approved';
                break;
            case 'rejected':
            case 'cancelled':
                $key = 'customer_rejected';
                break;
        }

        if ($key) {
            self::send_email($key, $booking);
        }
    }

    /**
     * Helper to get populated booking object.
     */
    private static function get_booking_data($booking_id)
    {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'appointix_bookings';
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT b.* FROM $table_bookings b WHERE b.id = %d",
            $booking_id
        ));

        if (!$booking) return false;

        // Get Service Name
        $service_name = '';
        $apartment = get_post($booking->post_id);
        if ($apartment) {
            $service_name = $apartment->post_title;
        }
        $booking->service_name = $service_name ?: __('Service', 'appointix');
        
        return $booking;
    }

    /**
     * Generic send email function using templates.
     */
    private static function send_email($template_key, $booking)
    {
        $options = get_option('appointix_theme_options');
        
        // Normalize keys
        $subject_key = 'email_' . $template_key . '_subject';
        $body_key    = 'email_' . $template_key . '_body';

        // Get Subject & Body
        $subject = isset($options[$subject_key]) ? $options[$subject_key] : '';
        $body    = isset($options[$body_key]) ? $options[$body_key]    : '';

        if (empty($subject) || empty($body)) {
            return; // Don't send if not configured
        }

        // Determine recipient
        $to = '';
        if (strpos($template_key, 'admin') !== false) {
            $to = isset($options['notification_email']) ? $options['notification_email'] : get_option('admin_email');
        } else {
            $to = $booking->customer_email;
        }

        // Replace Placeholders
        $placeholders = array(
            '{booking_id}'     => $booking->id,
            '{customer_name}'  => $booking->customer_name,
            '{customer_email}' => $booking->customer_email,
            '{customer_phone}' => $booking->customer_phone,
            '{apartment_name}' => $booking->service_name,
            '{dates}'          => $booking->booking_date . ($booking->end_date ? ' to ' . $booking->end_date : ''),
            '{total_price}'    => isset($options['currency_symbol']) ? $options['currency_symbol'] . $booking->total_price : '$' . $booking->total_price,
            '{site_name}'      => get_bloginfo('name'),
        );

        foreach ($placeholders as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $body    = str_replace($key, $val, $body);
        }

        // Convert newlines for plain text areas just in case, but wp_editor saves HTML
        $body = wpautop($body);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Attach inline styles for better email looking
        $styled_body = self::wrap_email_template($body);

        wp_mail($to, $subject, $styled_body, $headers);
    }

    /**
     * Simple HTML wrapper for emails.
     */
    private static function wrap_email_template($content)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; background: #f9f9f9; }
                .email-content { background: #fff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
                .email-footer { margin-top: 20px; font-size: 12px; color: #999; text-align: center; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-content">
                    <?php echo $content; ?>
                </div>
                <div class="email-footer">
                    &copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
