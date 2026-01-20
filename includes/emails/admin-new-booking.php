<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Outfit', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .header {
            background: #0f172a;
            color: #fff;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .content {
            padding: 40px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin: 30px 0 15px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 5px;
        }

        .details-card {
            background: #f1f5f9;
            padding: 25px;
            border-radius: 12px;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .details-label {
            font-weight: 600;
            color: #64748b;
        }

        .details-value {
            font-weight: 700;
            color: #1e293b;
            text-align: right;
        }

        .footer {
            padding: 30px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            background: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
		<div class="header">
			<h1><?php _e( 'New Booking Received!', 'appointix' ); ?></h1>
		</div>
		<div class="content">
			<p><?php _e( 'Hello Admin,', 'appointix' ); ?></p>
			<p><?php _e( 'A new booking has been placed on your website. Here are the details:', 'appointix' ); ?></p>

			<div class="section-title"><?php _e( 'Customer Information', 'appointix' ); ?></div>
            <div class="details-card">
				<div class="details-row">
					<div class="details-label"><?php _e( 'Name', 'appointix' ); ?></div>
					<div class="details-value"><?php echo esc_html( $booking->customer_name ); ?></div>
				</div>
				<div class="details-row">
					<div class="details-label"><?php _e( 'Email', 'appointix' ); ?></div>
					<div class="details-value"><?php echo esc_html( $booking->customer_email ); ?></div>
				</div>
				<div class="details-row">
					<div class="details-label"><?php _e( 'Phone', 'appointix' ); ?></div>
					<div class="details-value"><?php echo esc_html( $booking->customer_phone ); ?></div>
				</div>
			</div>

			<div class="section-title"><?php _e( 'Booking Details', 'appointix' ); ?></div>
            <div class="details-card">
				<div class="details-row">
					<div class="details-label"><?php _e( 'Booking ID', 'appointix' ); ?></div>
					<div class="details-value">#<?php echo $booking->id; ?></div>
				</div>
				<div class="details-row">
					<div class="details-label"><?php _e( 'Property', 'appointix' ); ?></div>
					<div class="details-value"><?php echo esc_html( $booking->service_name ); ?></div>
				</div>

                <?php if ($booking->end_date): ?>
					<div class="details-row">
						<div class="details-label"><?php _e( 'Check-in', 'appointix' ); ?></div>
						<div class="details-value">
							<?php echo date_i18n( get_option( 'date_format' ), strtotime( $booking->booking_date ) ); ?></div>
					</div>
					<div class="details-row">
						<div class="details-label"><?php _e( 'Check-out', 'appointix' ); ?></div>
						<div class="details-value">
							<?php echo date_i18n( get_option( 'date_format' ), strtotime( $booking->end_date ) ); ?></div>
					</div>
				<?php else : ?>
					<div class="details-row">
						<div class="details-label"><?php _e( 'Date', 'appointix' ); ?></div>
                        <div class="details-value">
                            <?php echo date_i18n(get_option('date_format'), strtotime($booking->booking_date)); ?></div>
                    </div>
					<?php if ( $booking->booking_time ) : ?>
						<div class="details-row">
							<div class="details-label"><?php _e( 'Time', 'appointix' ); ?></div>
							<div class="details-value">
								<?php echo date_i18n( get_option( 'time_format' ), strtotime( $booking->booking_time ) ); ?></div>
						</div>
					<?php endif; ?>
                <?php endif; ?>

				<div class="details-row" style="margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
					<div class="details-label"><?php _e( 'Total Amount', 'appointix' ); ?></div>
					<div class="details-value" style="font-size: 18px; color: #4f46e5;">
						<?php echo get_option( 'appointix_currency', '$' ) . number_format( $booking->total_price, 2 ); ?>
					</div>
				</div>
            </div>

			<div style="text-align: center; margin-top: 30px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=appointix-bookings' ) ); ?>" class="btn"><?php _e( 'Manage Bookings', 'appointix' ); ?></a>
			</div>
        </div>
		<div class="footer">
			&copy; <?php echo date( 'Y' ); ?> <?php echo get_bloginfo( 'name' ); ?>. <?php _e( 'All rights reserved.', 'appointix' ); ?>
		</div>
    </div>
</body>

</html>