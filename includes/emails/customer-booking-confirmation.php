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
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .details-card {
            background: #f1f5f9;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }

        .details-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
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

        .total-row {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 18px;
        }

        .total-label {
            font-weight: 700;
        }

        .total-price {
            font-weight: 800;
            color: #4f46e5;
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
			<h1><?php _e( 'Booking Confirmed!', 'appointix' ); ?></h1>
		</div>
		<div class="content">
			<div class="greeting"><?php printf( __( 'Hi %s,', 'appointix' ), esc_html( $booking->customer_name ) ); ?></div>
			<p><?php _e( "Great news! Your booking has been received. We're getting everything ready for your stay.", 'appointix' ); ?></p>

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
                    <?php
                    $check_in = new DateTime($booking->booking_date);
                    $check_out = new DateTime($booking->end_date);
                    $nights = $check_in->diff($check_out)->days;
                    ?>
					<div class="details-row">
						<div class="details-label"><?php echo $nights > 1 ? __( 'Nights', 'appointix' ) : __( 'Night', 'appointix' ); ?></div>
						<div class="details-value"><?php echo $nights; ?></div>
					</div>
				<?php else : ?>
					<div class="details-row">
						<div class="details-label"><?php _e( 'Date', 'appointix' ); ?></div>
						<div class="details-value">
							<?php echo date_i18n( get_option( 'date_format' ), strtotime( $booking->booking_date ) ); ?></div>
					</div>
					<?php if ( $booking->booking_time ) : ?>
						<div class="details-row">
							<div class="details-label"><?php _e( 'Time', 'appointix' ); ?></div>
							<div class="details-value">
								<?php echo date_i18n( get_option( 'time_format' ), strtotime( $booking->booking_time ) ); ?></div>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<div class="total-row">
					<div class="total-label"><?php _e( 'Total Amount', 'appointix' ); ?></div>
					<div class="total-price">
						<?php echo get_option( 'appointix_currency', '$' ) . number_format( $booking->total_price, 2 ); ?>
					</div>
				</div>
			</div>

			<p><?php _e( "If you have any questions or need to make changes to your booking, please don't hesitate to reach out to us.", 'appointix' ); ?></p>

			<div style="text-align: center;">
				<a href="<?php echo esc_url( home_url() ); ?>" class="btn"><?php _e( 'Visit Website', 'appointix' ); ?></a>
			</div>
        </div>
		<div class="footer">
			&copy; <?php echo date( 'Y' ); ?> <?php echo get_bloginfo( 'name' ); ?>. <?php _e( 'All rights reserved.', 'appointix' ); ?><br>
			<?php _e( 'This is an automated message, please do not reply directly to this email.', 'appointix' ); ?>
		</div>
    </div>
</body>

</html>