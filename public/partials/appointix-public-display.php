<?php
/**
 * Public-facing view for the booking form.
 * Works with both apartments (new CPT) and legacy services.
 */

$currency = get_option( 'appointix_currency', '$' );

// Check if we're using apartments
$using_apartments = isset( $apartments ) && ! empty( $apartments );

// Apartment type labels
$type_labels = array(
	'sea_view'      => __( 'Sea View', 'appointix' ),
	'mountain_view' => __( 'Mountain View', 'appointix' ),
	'city_view'     => __( 'City View', 'appointix' ),
	'garden_view'   => __( 'Garden View', 'appointix' ),
	'pool_view'     => __( 'Pool View', 'appointix' ),
	'standard'      => __( 'Standard', 'appointix' ),
);
?>

<?php
// Check for search params
$search_check_in  = isset( $_GET['check_in'] ) ? sanitize_text_field( $_GET['check_in'] ) : '';
$search_check_out = isset( $_GET['check_out'] ) ? sanitize_text_field( $_GET['check_out'] ) : '';
$search_adults    = isset( $_GET['adults'] ) ? intval( $_GET['adults'] ) : 2;
$search_children  = isset( $_GET['children'] ) ? intval( $_GET['children'] ) : 0;

$has_search = ! empty( $search_check_in ) && ! empty( $search_check_out );
?>

<div class="container appointix-booking-wrapper">
    
    <?php if ( ! $has_search ) : ?>
        <!-- STATE 1: SEARCH FORM -->
		<div class="appointix-search-box">
			<div class="appointix-booking-header">
				<h2><?php echo ! empty( $booking_form_heading ) ? esc_html( $booking_form_heading ) : __( 'Book Your Stay', 'appointix' ); ?></h2>
				<p><?php echo ! empty( $booking_form_subheading ) ? esc_html( $booking_form_subheading ) : __( 'Select your dates to check availability.', 'appointix' ); ?></p>
			</div>
            
            <form id="appointix-availability-form" class="appointix-form-grid">
				<?php if ( 'yes' === $show_apartment_dropdown ) : ?>
				<div class="appointix-form-group">
					<label><?php echo ! empty( $apartment_label ) ? esc_html( $apartment_label ) : __( 'Select Apartment', 'appointix' ); ?></label>
					<div class="appointix-select-wrapper">
						<select name="apartment_id" id="search_apartment_id" class="appointix-input">
							<option value=""><?php _e( 'Choose your apartment...', 'appointix' ); ?></option>
							<?php foreach ( $apartments as $apt ) : 
								$type_label = isset( $type_labels[$apt->apartment_type] ) ? $type_labels[$apt->apartment_type] : ucfirst(str_replace('_', ' ', $apt->apartment_type));
							?>
								<option value="<?php echo esc_attr( $apt->id ); ?>" <?php selected( $preselected_apartment, $apt->id ); ?>>
									<?php echo esc_html( $apt->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<?php endif; ?>
				
				<div class="appointix-form-group">
					<label><?php echo ! empty( $booking_form_check_in ) ? esc_html( $booking_form_check_in ) : __( 'Arrival / Departure', 'appointix' ); ?></label>
					<input type="text" id="booking_date_range" class="appointix-input" placeholder="<?php echo ! empty( $booking_form_select_dates ) ? esc_attr( $booking_form_select_dates ) : __( 'Select dates', 'appointix' ); ?>" readonly required>
					<input type="hidden" id="booking_date" name="check_in">
					<input type="hidden" id="booking_end_date" name="check_out">
				</div>
				
				<div class="appointix-form-group">
					<label><?php echo ! empty( $booking_form_adults ) ? esc_html( $booking_form_adults ) : __( 'Adults', 'appointix' ); ?></label>
					<div class="appointix-select-wrapper">
						<select name="adults" id="search_adults" class="appointix-input">
							<?php for ( $i = 1; $i <= 10; $i++ ) : ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>

				<div class="appointix-form-group">
					<label><?php echo ! empty( $booking_form_children ) ? esc_html( $booking_form_children ) : __( 'Children', 'appointix' ); ?></label>
					<div class="appointix-select-wrapper">
						<select name="children" id="search_children" class="appointix-input">
							<?php for ( $i = 0; $i <= 5; $i++ ) : ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>

				<div class="appointix-form-group submit-group">
					<button type="submit" class="appointix-btn-search"><?php echo ! empty( $booking_form_check_availability ) ? esc_html( $booking_form_check_availability ) : __( 'Check Availability', 'appointix' ); ?></button>
				</div>
            </form>
        </div>


    <?php else : ?>
        <!-- STATE 2: RESULTS LIST -->
        <div class="appointix-results-list">
            <?php if ( ! $selected_apartment ) : ?>
            <div class="appointix-results-header">
                <h2><?php _e('Search Results', 'appointix'); ?></h2>
                <div class="search-meta">
                    <a href="<?php echo home_url('/'); ?>" class="back-link">← <?php _e('Different Dates', 'appointix'); ?></a>
                    <span><?php echo esc_html("$search_check_in - $search_check_out"); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( $selected_apartment ) : ?>
                <?php if ( $is_selected_available ) : ?>
                    <!-- STATE: Selected Apartment Available (INTERACTIVE CARD VIEW) -->
                    <div class="appointix-booking-block" id="booking-block-<?php echo esc_attr($selected_apartment->id); ?>">
                        <div class="apt-card-horizontal">
                            <div class="apt-card-img">
                                <?php $thumb = $selected_apartment->thumbnail ? $selected_apartment->thumbnail : plugin_dir_url( __DIR__ . '/../../' ) . 'assets/images/placeholder.jpg'; ?>
                                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($selected_apartment->name); ?>">
                                <div class="apt-availability-badge">
                                    <i class="fa fa-check-circle"></i> <?php _e('Date is Available', 'appointix'); ?>
                                </div>
                            </div>
                            <div class="apt-card-body">
                                <div class="apt-card-header">
                                    <div class="apt-info-meta">
                                        <span class="apt-tag"><?php echo esc_html(ucfirst($selected_apartment->apartment_type)); ?></span>
                                        <h2 class="apt-name"><?php echo esc_html($selected_apartment->name); ?></h2>
                                    </div>
                                    <div class="apt-price-tag">
                                        <!-- Price shown in summary below -->
                                    </div>
                                </div>
                                <div class="apt-card-desc">
                                    <?php echo wp_trim_words($selected_apartment->description, 30); ?>
                                </div>
                                <div class="apt-card-footer-actions">
                                    <a class="apt-btn-secondary toggle-details" href="<?php echo get_permalink($selected_apartment->id); ?>">
                                        <i class="fa fa-info-circle"></i> <?php _e('View Details', 'appointix'); ?>
                                    </a>
                                    <button class="apt-btn-primary open-booking-modal" 
                                            data-apt-id="<?php echo esc_attr($selected_apartment->id); ?>"
                                            data-apt-name="<?php echo esc_attr($selected_apartment->name); ?>"
                                            data-price="<?php echo esc_attr($selected_apartment->price_per_night); ?>">
                                        <i class="fa fa-calendar-check"></i> <?php _e('Book Now', 'appointix'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- HIDDEN DETAILS SECTION (TOGGLEABLE) -->
                        <div class="apt-details-extra" id="apt-details-<?php echo esc_attr($selected_apartment->id); ?>" style="display:none;">
                            <div class="apt-details-inner">
                                <div class="apt-features-row">
                                    <div class="apt-feat"><strong><?php _e('Bedrooms:', 'appointix'); ?></strong> <?php echo esc_html($selected_apartment->bedrooms ?: '—'); ?></div>
                                    <div class="apt-feat"><strong><?php _e('Bathrooms:', 'appointix'); ?></strong> <?php echo esc_html($selected_apartment->bathrooms ?: '—'); ?></div>
                                    <div class="apt-feat"><strong><?php _e('Max Guests:', 'appointix'); ?></strong> <?php echo esc_html($selected_apartment->max_guests ?: '—'); ?></div>
                                </div>
                                <div class="apt-long-desc">
                                    <h3><?php _e('Description', 'appointix'); ?></h3>
                                    <?php echo wpautop($selected_apartment->description); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOOKING MODAL (POPUP) -->
                    <div class="apt-modal" id="booking-modal-<?php echo esc_attr($selected_apartment->id); ?>">
                        <div class="apt-modal-overlay"></div>
                        <div class="apt-modal-content">
                            <button class="apt-modal-close">&times;</button>
                            <div class="apt-modal-header">
                                <h2><?php _e('Complete Your Booking', 'appointix'); ?></h2>
                                <p><?php _e('Secure your stay at', 'appointix'); ?> <strong><?php echo esc_html($selected_apartment->name); ?></strong></p>
                            </div>
                            <div class="apt-modal-body">
                                <div class="booking-summary-box">
                                    <div class="summary-item">
                                        <label><?php _e('Stay Dates', 'appointix'); ?></label>
                                        <span><?php echo esc_html($search_check_in); ?> – <?php echo esc_html($search_check_out); ?></span>
                                    </div>
                                    <?php 
                                        $check_in_dt = new DateTime($search_check_in);
                                        $check_out_dt = new DateTime($search_check_out);
                                        $nights = $check_in_dt->diff($check_out_dt)->days;
                                        $total_price = Appointix_Seasonal_Pricing_Model::calculate_total($selected_apartment->id, $search_check_in, $search_check_out);
                                    ?>
                                    <div class="summary-item">
                                        <label><?php _e('Length of Stay', 'appointix'); ?></label>
                                        <span><?php echo $nights; ?> <?php _e('Nights', 'appointix'); ?></span>
                                    </div>
                                    <div class="summary-total">
                                        <label><?php _e('Total Amount', 'appointix'); ?></label>
                                        <span class="total-val"><?php echo esc_html($currency . number_format($total_price, 0)); ?></span>
                                    </div>
                                </div>

                                <form class="apt-booking-final-form">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <input type="text" name="guest_name" placeholder="<?php _e( 'Your Full Name', 'appointix' ); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="email" name="guest_email" placeholder="<?php _e( 'Email Address', 'appointix' ); ?>" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="apt-btn-confirm">
                                        <?php _e( 'Confirm', 'appointix' ); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <!-- STATE: Selected Apartment NOT Available (NOTICE BLOCK) -->
                    <div class="appointix-selected-unavailable">
                        <div class="unavailable-notice" style="margin-top: 0;">
                            <div class="notice-icon">!</div>
                            <div class="notice-text">
                                <h3><?php echo sprintf( __('"%s" is not available for these dates.', 'appointix'), esc_html($selected_apartment->name) ); ?></h3>
                                <p><?php _e('Please try different dates or choose from our top recommendations below.', 'appointix'); ?></p>
                            </div>
                        </div>

                        <?php if ( ! empty($apartments) ) : ?>
                            <div class="suggestions-section" style="margin-top: 60px;">
                                <h3 class="suggestions-heading"><?php _e('Recommended for You:', 'appointix'); ?></h3>
                                <div class="appointix-results-grid">
                                    <?php foreach ( $apartments as $apartment ) : 
                                        if ($apartment->id === $selected_apartment->id) continue;
                                        $thumb = $apartment->thumbnail_medium ? $apartment->thumbnail_medium : plugin_dir_url( __DIR__ . '/../../' ) . 'assets/images/placeholder.jpg';
                                        
                                        $booking_link = add_query_arg(array(
                                            'check_in'  => $search_check_in,
                                            'check_out' => $search_check_out,
                                        ), get_permalink($apartment->id));
                                    ?>
                                    <div class="appointix-result-card">
                                        <div class="apt-card-image">
                                            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($apartment->name); ?>">
                                        </div>
                                        <div class="apt-card-content">
                                            <span class="apt-type-badge"><?php echo ucfirst(str_replace('_', ' ', $apartment->apartment_type)); ?></span>
                                            <h3><a href="<?php echo esc_url($booking_link); ?>"><?php echo esc_html($apartment->name); ?></a></h3>
                                            <div class="apt-card-footer">
                                                <div class="apt-price">
                                                    <!-- Select dates for price -->
                                                </div>
                                                <a href="<?php echo esc_url($booking_link); ?>" class="appointix-btn-book"><?php _e('Book Now', 'appointix'); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <!-- STATE: General Search Results -->
                <?php if ( ! empty($apartments) ) : ?>
                    <div class="appointix-results-grid">
                        <?php foreach ( $apartments as $apartment ) : 
                             $thumb = $apartment->thumbnail_medium ? $apartment->thumbnail_medium : plugin_dir_url( __DIR__ . '/../../' ) . 'assets/images/placeholder.jpg';
                             $link = get_permalink($apartment->id); 
                        ?>
                        <div class="appointix-result-card">
                            <div class="apt-card-image">
                                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($apartment->name); ?>">
                            </div>
                            <div class="apt-card-content">
                                <span class="apt-type-badge"><?php echo ucfirst(str_replace('_', ' ', $apartment->apartment_type)); ?></span>
                                <h3><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($apartment->name); ?></a></h3>
                                <div class="apt-short-info">
                                    <?php echo wp_trim_words($apartment->description, 15); ?>
                                </div>
                                <div class="apt-card-footer">
                                    <div class="apt-price">
                                        <!-- Select dates for price -->
                                    </div>
                                    <?php 
                                    $booking_link = add_query_arg(array(
                                        'check_in'  => $search_check_in,
                                        'check_out' => $search_check_out,
                                    ), $link);
                                    ?>
                                    <a href="<?php echo esc_url($booking_link); ?>" class="appointix-btn-book"><?php _e('Book Now', 'appointix'); ?></a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="appointix-no-results">
                        <h3><?php _e('No apartments available.', 'appointix'); ?></h3>
                        <p><?php _e('Try changing your dates.', 'appointix'); ?></p>
                        <a href="<?php echo home_url('/'); ?>" class="appointix-btn-search" style="display:inline-block; width:auto; margin-top:20px;"><?php _e('Searching Again', 'appointix'); ?></a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Shared */
.appointix-booking-wrapper {
    background: transparent;
    font-family: 'Outfit', sans-serif; /* Ensuring modern font usage if loaded */
}

/* Modern Search Box */
.appointix-search-box {
    padding: 20px 20px 0;
    border-radius: 24px;
    max-width: 800px;
    margin: 40px auto;
    border: 1px solid rgba(0,0,0,0.05);
    background: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.appointix-booking-header h2 {
    font-size: 2.2rem;
    color: #1a1a1a;
    margin-bottom: 12px;
    text-align: center;
    font-weight: 800;
    letter-spacing: -0.5px;
}
.appointix-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    align-items: end;
}
.appointix-form-group {
    position: relative;
}
.appointix-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.appointix-input {
    width: 100%;
    padding: 0px 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    font-size: 1rem;
    color: #333;
    transition: all 0.2s ease;
    font-weight: 500;
    appearance: none;
    -webkit-appearance: none;
}
input.appointix-input {
    padding: 16px 20px;
}
.appointix-select-wrapper {
    position: relative;
}
.appointix-select-wrapper::after {
    content: '';
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 12px;
    height: 12px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23cea959' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    pointer-events: none;
}
.appointix-input:focus {
    background: #fff;
    border-color: #cea959;
    box-shadow: 0 0 0 4px rgba(206, 169, 89, 0.1);
    outline: none;
}
.submit-group {
    grid-column: 1 / -1;
    margin-bottom: 15px;
}
.appointix-btn-search {
    background: #1a1a1a;
    color: #fff;
    padding: 18px 40px;
    border: none;
    border-radius: 14px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    letter-spacing: 0.5px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.appointix-btn-search:hover {
    background: #cea959;
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(99, 102, 241, 0.25);
}

/* INTERACTIVE BOOKING BLOCK */
.appointix-booking-block {
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    overflow: hidden;
    margin-bottom: 40px;
    border: 1px solid #f1f5f9;
    max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
}
.apt-card-horizontal {
    display: grid;
    grid-template-columns: 400px 1fr;
    min-height: 280px;
}
.apt-card-img {
    position: relative;
    overflow: hidden;
}
.apt-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}
.apt-card-horizontal:hover .apt-card-img img {
    transform: scale(1.05);
}
.apt-availability-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #10b981;
    color: #fff;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.apt-card-body {
    padding: 35px;
    display: flex;
    flex-direction: column;
}
.apt-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}
.apt-tag {
    display: inline-block;
    color: #cea959;
    font-weight: 800;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.apt-name {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.apt-price-tag {
    text-align: right;
}
.apt-price-val {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    color: #cea959;
    line-height: 1;
}
.apt-price-unit {
    font-size: 0.9rem;
    color: #64748b;
    font-weight: 600;
}
.apt-card-desc {
    color: #475569;
    line-height: 1.6;
    font-size: 1rem;
    margin-bottom: 30px;
    flex-grow: 1;
}

.apt-card-footer-actions {
    display: flex;
    gap: 15px;
}
.apt-btn-primary, .apt-btn-secondary {
    padding: 14px 28px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    border: none;
}
.apt-btn-primary {
    background: #0f172a;
    color: #fff;
    flex: 1;
    justify-content: center;
}
.apt-btn-primary:hover {
    background: #cea959;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(206, 169, 89, 0.2);
}
.apt-btn-secondary {
    background: #f1f5f9;
    color: #475569;
}
.apt-btn-secondary:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.apt-btn-secondary.active {
    background: #cea959;
    color: #fff;
}

/* TOGGLEABLE DETAILS */
.apt-details-extra {
    background: #fafbfc;
    border-top: 1px solid #f1f5f9;
}
.apt-details-inner {
    padding: 35px;
}
.apt-features-row {
    display: flex;
    gap: 40px;
    margin-bottom: 30px;
}
.apt-feat {
    font-size: 1rem;
    color: #1e293b;
}
.apt-feat strong { color: #cea959; }
.apt-long-desc h3 {
    font-size: 1.2rem;
    margin-bottom: 15px;
    color: #0f172a;
}
.apt-long-desc p {
    color: #64748b;
    line-height: 1.8;
}

/* MODAL STYLES */
.apt-modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}
.apt-modal-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(8px);
}
.apt-modal-content {
    position: relative;
    background: #fff;
    width: 100%;
    max-width: 650px;
    border-radius: 30px;
    padding: 50px;
    box-shadow: 0 25px 100px rgba(0,0,0,0.5);
    transform: translateY(20px);
    transition: transform 0.4s;
}
.apt-modal.open .apt-modal-content {
    transform: translateY(0);
}
.apt-modal-close {
    position: absolute;
    top: 25px; right: 25px;
    background: #f1f5f9;
    border: none;
    width: 40px; height: 40px;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
    transition: all 0.2s;
}
.apt-modal-close:hover {
    background: #ef4444;
    color: #fff;
}
.apt-modal-header {
    margin-bottom: 35px;
}
.apt-modal-header h2 {
    font-size: 2.2rem;
    margin-bottom: 8px;
    color: #0f172a;
}
.apt-modal-header p {
    color: #64748b;
    font-size: 1.1rem;
}

.booking-summary-box {
    background: #f8fafc;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 35px;
    border: 1px solid #e2e8f0;
}
.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px dashed #cbd5e1;
}
.summary-item label { color: #64748b; font-weight: 600; }
.summary-item span { color: #0f172a; font-weight: 700; }
.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
}
.summary-total label { font-size: 1.1rem; color: #0f172a; font-weight: 800; }
.total-val { font-size: 2rem; color: #cea959; font-weight: 800; }

.apt-booking-final-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.apt-booking-final-form input, .apt-booking-final-form textarea {
    width: 100%;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    font-size: 1rem;
    background: #fff;
}
.apt-booking-final-form textarea { height: 100px; resize: none; }
.apt-btn-confirm {
    width: 100%;
    padding: 20px;
    background: #cea959;
    color: #fff;
    border: none;
    border-radius: 16px;
    font-size: 1.2rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    margin-top: 15px;
}
.apt-btn-confirm:hover {
    background: #b68d40;
    box-shadow: 0 10px 25px rgba(206, 169, 89, 0.4);
}
.form-note {
    text-align: center;
    font-size: 0.85rem;
    color: #94a3b8;
    margin-top: 15px;
}

body.modal-open {
    overflow: hidden;
}

@media (max-width: 900px) {
    .apt-card-horizontal { grid-template-columns: 1fr; }
    .apt-card-img { height: 250px; }
}
.apt-no-charge-text {
    text-align: center;
    color: #94a3b8;
    font-size: 0.9rem;
    margin-top: 15px;
}

/* RESULTS LIST (General & Unavailable) */
.appointix-results-list {
    padding: 40px 0;
}
.appointix-results-header {
    margin-bottom: 50px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 30px;
}
.appointix-results-header h2 {
    font-size: 2.5rem;
    margin: 0;
    color: #0f172a;
    font-weight: 800;
}

/* SEARCH RESULTS GRID & CARDS */
.appointix-results-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}
.appointix-result-card {
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    border: 1px solid #f1f5f9;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.appointix-result-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}
.apt-card-image {
    width: 100%;
    height: 240px;
}
.apt-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.apt-card-content {
    padding: 25px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.apt-type-badge {
    display: inline-block;
    background: rgba(206, 169, 89, 0.1);
    color: #cea959;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 800;
    margin-bottom: 12px;
    text-transform: uppercase;
    width: fit-content;
}
.apt-card-content h3 {
    margin: 0 0 12px;
    font-size: 1.4rem;
    font-weight: 700;
    text-align: left;
}
.apt-card-content h3 a {
    color: #1e293b;
    text-decoration: none;
}
.apt-short-info {
    color: #64748b;
    margin-bottom: 20px;
    font-size: 0.95rem;
    line-height: 1.6;
}
.apt-card-footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #f1f5f9;
}
.apt-price {
    font-size: 1.1rem;
    color: #64748b;
}
.apt-price strong {
    font-weight: 800;
    font-size: 1.4rem;
    color: #1e293b;
}
.appointix-btn-book {
    background: #0f172a;
    color: #fff;
    padding: 10px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s;
}
.appointix-btn-book:hover {
    background: #cea959;
    transform: translateY(-2px);
}

/* Unavailable Notice */
.unavailable-notice {
    display: flex;
    align-items: center;
    gap: 25px;
    background: #fff;
    border: 2px solid #fee2e2;
    padding: 30px;
    border-radius: 20px;
    margin-bottom: 50px;
}
.notice-icon {
    width: 60px;
    height: 60px;
    background: #ef4444;
    color: #fff;
    font-size: 2rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    flex-shrink: 0;
}
.notice-text h3 {
    margin: 0 0 5px;
    color: #991b1b;
    font-size: 1.4rem;
}
.notice-text p {
    margin: 0;
    color: #4b5563;
    font-size: 1.1rem;
}
.suggestions-heading {
    font-size: 1.6rem;
    margin-bottom: 30px;
    color: #1e293b;
    font-weight: 700;
}

.appointix-no-results {
    text-align: center;
    padding: 80px 40px;
    background: #f8fafc;
    border-radius: 30px;
}

@media (max-width: 1100px) {
    .apt-content-layout { grid-template-columns: 1fr; gap: 30px; }
    .apt-sidebar-action { position: static; }
}

 .unavailable-notice .notice-text h3 {
    text-align: left;
}

@media (min-width: 993px) {
    .unavailable-notice {       
        max-width: 60%;
        margin: auto;
    }
}

@media (max-width: 992px) {
    .premium-card-image { width: 100%; height: 300px; }
}

@media (max-width: 768px) {
    .appointix-form-grid { grid-template-columns: 1fr; gap: 15px; }
    .appointix-results-grid { grid-template-columns: 1fr; }
    .appointix-results-header { flex-direction: column; align-items: flex-start; gap: 20px; }
    .search-meta { width: 100%; justify-content: space-between; }
    .apt-features-grid { grid-template-columns: 1fr 1fr; }
    .apt-key-check-list { grid-template-columns: 1fr; }
    .apt-name { font-size: 1.5rem; }
    .apt-booking-highlight { padding: 25px; }
    .apt-modal-content { padding: 30px; }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Hijack form submit to redirect to Search Results page
    $('#appointix-availability-form').on('submit', function(e) {
        e.preventDefault();
        const checkIn = $('#booking_date').val();
        const checkOut = $('#booking_end_date').val();
        const adults = $('#search_adults').val();
        const children = $('#search_children').val();
        const apartmentId = $('#search_apartment_id').val();

        if(!checkIn || !checkOut) {
            alert('Please select check-in and check-out dates.');
            return;
        }

        // Redirect to search results page
        const baseUrl = '<?php echo home_url('/search-results/'); ?>';
        const url = new URL(baseUrl);
        url.searchParams.set('check_in', checkIn);
        url.searchParams.set('check_out', checkOut);
        url.searchParams.set('adults', adults);
        url.searchParams.set('children', children);
        
        if (apartmentId) {
            url.searchParams.set('apartment_id', apartmentId);
        }
        
        window.location.href = url.toString();
    });

    // Toggle Details
    $(document).on('click', '.toggle-details', function() {
        const target = $(this).data('target');
        $(target).slideToggle();
        $(this).toggleClass('active');
    });

    // Handle Modal
    $(document).on('click', '.open-booking-modal', function() {
        const aptId = $(this).data('apt-id');
        $(`#booking-modal-${aptId}`).fadeIn(300).addClass('open');
        $('body').addClass('modal-open');
    });

    $(document).on('click', '.apt-modal-close, .apt-modal-overlay', function() {
        $('.apt-modal').fadeOut(200).removeClass('open');
        $('body').removeClass('modal-open');
    });

    // Handle Final Booking Submission
    $(document).on('submit', '.apt-booking-final-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('.apt-btn-confirm');
        const $modal = $form.closest('.apt-modal');
        const aptId = $modal.attr('id').replace('booking-modal-', '');

        // Gather data from modal and search params
        const urlParams = new URLSearchParams(window.location.search);
        const checkIn = urlParams.get('check_in');
        const checkOut = urlParams.get('check_out');
        const adults = urlParams.get('adults') || 2;
        const children = urlParams.get('children') || 0;

        const guestName = $form.find('input[name="guest_name"]').val();
        const guestEmail = $form.find('input[name="guest_email"]').val();

        $btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: appointix_public.ajax_url,
            type: 'POST',
            data: {
                action: 'appointix_submit_booking',
                nonce: appointix_public.nonce,
                post_id: parseInt(aptId),
                date: checkIn,
                end_date: checkOut,
                name: guestName,
                email: guestEmail,
                adults: adults,
                children: children,
                time: '14:00' // Default check-in time
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to homepage with success parameter
                    window.location.href = '<?php echo home_url('/'); ?>?booking_success=1';
                } else {
                    alert(response.data.message || 'An error occurred.');
                    $btn.prop('disabled', false).text('Confirm');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text('Confirm');
            }
        });
    });
});
</script>
