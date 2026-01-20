<?php
$currency = get_option('appointix_currency', '$');

if ($bookings): ?>
    <?php foreach ($bookings as $booking): ?>
        <?php
        // Calculate nights if end_date exists
        $nights = 1;
        if (!empty($booking->end_date) && $booking->end_date !== $booking->booking_date) {
            $start = new DateTime($booking->booking_date);
            $end = new DateTime($booking->end_date);
            $nights = $end->diff($start)->days;
        }

        // Get total price
        $total_price = isset($booking->total_price) ? floatval($booking->total_price) : 0;
        ?>
        <tr data-id="<?php echo esc_attr($booking->id); ?>">
            <td>
                <strong>#<?php echo esc_html($booking->id); ?></strong>
            </td>
            <td>
                <div class="aptx-customer-name"><?php echo esc_html($booking->customer_name); ?></div>
                <div class="aptx-customer-email"><?php echo esc_html($booking->customer_email); ?></div>
                <?php if ($booking->customer_phone): ?>
                    <div class="aptx-customer-phone"><?php echo esc_html($booking->customer_phone); ?></div>
                <?php endif; ?>
            </td>
			<td>
				<div class="aptx-apartment-name"><?php echo esc_html( $booking->service_name ?: __( 'Unknown', 'appointix' ) ); ?>
				</div>
				<?php if ( ! empty( $booking->apartment_type ) ) : 
					$type_labels = array(
						'sea_view'      => __( 'Sea View', 'appointix' ),
						'mountain_view' => __( 'Mountain View', 'appointix' ),
						'city_view'     => __( 'City View', 'appointix' ),
						'garden_view'   => __( 'Garden View', 'appointix' ),
						'pool_view'     => __( 'Pool View', 'appointix' ),
						'standard'      => __( 'Standard', 'appointix' ),
					);
					$type_label = isset( $type_labels[ $booking->apartment_type ] ) ? $type_labels[ $booking->apartment_type ] : ucfirst( str_replace( '_', ' ', $booking->apartment_type ) );
				?>
					<span class="aptx-apartment-type"><?php echo esc_html( $type_label ); ?></span>
				<?php elseif ( ! empty( $booking->service_type ) ) : ?>
					<span class="aptx-apartment-type"><?php echo esc_html( ucfirst( $booking->service_type ) ); ?></span>
				<?php endif; ?>
			</td>
            <td>
                <div class="aptx-dates-wrapper">
                    <div class="aptx-date-row">
                        <span class="aptx-date-label"><?php _e('Check-in:', 'appointix'); ?></span>
                        <span
                            class="aptx-date-value"><?php echo esc_html(date('M d, Y', strtotime($booking->booking_date))); ?></span>
                    </div>
                    <?php if (!empty($booking->end_date) && $booking->end_date !== $booking->booking_date): ?>
                        <div class="aptx-date-row">
                            <span class="aptx-date-label"><?php _e('Check-out:', 'appointix'); ?></span>
                            <span
                                class="aptx-date-value"><?php echo esc_html(date('M d, Y', strtotime($booking->end_date))); ?></span>
                        </div>
                        <span class="aptx-nights-badge"><?php echo esc_html($nights); ?>
                            <?php echo $nights > 1 ? __('nights', 'appointix') : __('night', 'appointix'); ?></span>
                    <?php else: ?>
                        <?php if ($booking->booking_time && $booking->booking_time !== '00:00:00'): ?>
                            <div class="aptx-date-row">
                                <span class="aptx-date-label"><?php _e('Time:', 'appointix'); ?></span>
                                <span
                                    class="aptx-date-value"><?php echo esc_html(date('h:i A', strtotime($booking->booking_time))); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </td>
            <td>
                <?php if ($total_price > 0): ?>
                    <div class="aptx-price"><?php echo esc_html($currency . number_format($total_price, 2)); ?></div>
                <?php else: ?>
                    <span style="color: #94a3b8;">—</span>
                <?php endif; ?>
            </td>
            <td>
                <select class="aptx-status-select appointix-booking-status" data-id="<?php echo esc_attr($booking->id); ?>">
                    <option value="pending" <?php selected($booking->status, 'pending'); ?>><?php _e('Pending', 'appointix'); ?>
                    </option>
                    <option value="confirmed" <?php selected($booking->status, 'confirmed'); ?>>
                        <?php _e('Confirmed', 'appointix'); ?></option>
                    <option value="cancelled" <?php selected($booking->status, 'cancelled'); ?>>
                        <?php _e('Cancelled', 'appointix'); ?></option>
                    <option value="completed" <?php selected($booking->status, 'completed'); ?>>
                        <?php _e('Completed', 'appointix'); ?></option>
                </select>
            </td>
            <td>
                <button class="aptx-action-btn aptx-btn-delete appointix-delete-booking"
                    data-id="<?php echo esc_attr($booking->id); ?>">
                    <?php _e('Delete', 'appointix'); ?>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7">
            <div class="aptx-empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <p><?php _e('No bookings found.', 'appointix'); ?></p>
            </div>
        </td>
    </tr>
<?php endif; ?>