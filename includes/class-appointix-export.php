<?php

/**
 * The Export model class.
 */
class Appointix_Export
{

    /**
     * Export bookings to CSV.
     */
    public static function export_bookings_csv()
    {
        if (!current_user_can('manage_options'))
            return;

        $bookings = Appointix_Bookings_Model::get_bookings();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=appointix-bookings-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Customer', 'Email', 'Phone', 'Service', 'Date', 'Time', 'Status', 'Total Price', 'Booking Date'));

        foreach ($bookings as $booking) {
            fputcsv($output, array(
                $booking->id,
                $booking->customer_name,
                $booking->customer_email,
                $booking->customer_phone,
                $booking->service_name,
                $booking->booking_date,
                $booking->booking_time,
                $booking->status,
                $booking->total_price,
                $booking->created_at
            ));
        }

        fclose($output);
        exit;
    }
}
