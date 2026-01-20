<?php
/**
 * Template for displaying single apartment with booking form.
 * Premium layout with gallery, features, and booking sidebar.
 */

// Check if Elementor is in edit mode or preview mode
$is_elementor_mode = false;
if (class_exists('\Elementor\Plugin')) {
    if (
        \Elementor\Plugin::$instance->preview->is_preview_mode() ||
        \Elementor\Plugin::$instance->editor->is_edit_mode()
    ) {
        $is_elementor_mode = true;
    }
}

// Check if content was built with Elementor
$is_built_with_elementor = false;
if (class_exists('\Elementor\Plugin')) {
    $post_id = get_the_ID();
    $document = \Elementor\Plugin::$instance->documents->get($post_id);
    if ($document && $document->is_built_with_elementor()) {
        $is_built_with_elementor = true;
    }
    // Also check for standard Elementor meta just in case
    if (!$is_built_with_elementor && get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder') {
        $is_built_with_elementor = true;
    }
}

// If Elementor is in EDIT or PREVIEW mode, show ONLY content for the builder to work correctly
if ($is_elementor_mode) {
    get_header();
    while (have_posts()) {
        the_post();
        the_content();
    }
    get_footer();
    return;
}

get_header();
the_post(); // Initialize global post data for the template
$apartment_id = get_the_ID();
$apartment = Appointix_Apartments_Model::get_apartment($apartment_id);

if (!$apartment) {
    echo '<div class="appointix-error-page"><div class="error-content"><h1>' . __('Apartment Not Found', 'appointix') . '</h1><p>' . __('The apartment you are looking for does not exist.', 'appointix') . '</p><a href="' . esc_url(home_url('/')) . '" class="btn-back">' . __('Back to Home', 'appointix') . '</a></div></div>';
    get_footer();
    return;
}

$currency = get_option('appointix_currency', '$');

// Apartment type labels
$type_labels = array(
    'sea_view' => __('Sea View', 'appointix'),
    'mountain_view' => __('Mountain View', 'appointix'),
    'city_view' => __('City View', 'appointix'),
    'garden_view' => __('Garden View', 'appointix'),
    'pool_view' => __('Pool View', 'appointix'),
    'standard' => __('Standard', 'appointix')
);


$type_label = isset($type_labels[$apartment->apartment_type])
    ? $type_labels[$apartment->apartment_type]
    : ucfirst(str_replace('_', ' ', $apartment->apartment_type));

if (!empty($apartment->amenities)) {
    $amenities = array_map('trim', explode(',', $apartment->amenities));
}

// Get Dynamic Rating Data
$rating_score = get_post_meta($apartment_id, '_appointix_rating_score', true) ?: '0.0';
$rating_count = get_post_meta($apartment_id, '_appointix_rating_count', true) ?: '0';

// Gallery images
$gallery_images = array();
if (!empty($apartment->gallery)) {
    $gallery_ids = explode(',', $apartment->gallery);
    foreach ($gallery_ids as $img_id) {
        $img_url = wp_get_attachment_image_url(intval($img_id), 'large');
        $thumb_url = wp_get_attachment_image_url(intval($img_id), 'medium');
        if ($img_url) {
            $gallery_images[] = array('full' => $img_url, 'thumb' => $thumb_url);
        }
    }
}


?>

<style>
    /* Single Apartment Styles */
    .apt-single {
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        min-height: 100vh;
        padding-top: 0;
    }

    .apt-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 30px 60px;
    }

    /* Hero Gallery */
    .apt-gallery {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 10px;
        border-radius: 24px;
        overflow: hidden;
        height: 500px;
        margin-bottom: 40px;
    }

    .apt-gallery-main {
        position: relative;
        cursor: pointer;
        overflow: hidden;
    }

    .apt-gallery-main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .apt-gallery-main:hover img {
        transform: scale(1.05);
    }

    .apt-gallery-side {
        display: grid;
        grid-template-rows: 1fr 1fr;
        gap: 10px;
    }

    .apt-gallery-thumb {
        position: relative;
        cursor: pointer;
        overflow: hidden;
    }

    .apt-gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .apt-gallery-thumb:hover img {
        transform: scale(1.1);
    }

    .apt-gallery-more {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
        transition: background 0.3s;
    }

    .apt-gallery-more:hover {
        background: rgba(0, 0, 0, 0.7);
    }

    /* Content Grid */
    .apt-content-grid {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 50px;
        align-items: start;
    }

    /* Left Content */
    .apt-info {
        background: #fff;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .apt-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #e2e8f0;
    }

    .apt-header-left {
        flex: 1;
    }

    .apt-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #6366f1 0%, #0ea5e9 100%);
        color: #fff;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
    }

    .apt-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 12px;
        line-height: 1.2;
    }

    .apt-location {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 1rem;
    }

    .apt-location svg {
        color: #6366f1;
    }

    .apt-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: #fef3c7;
        border-radius: 12px;
    }

    .apt-rating svg {
        color: #f59e0b;
    }

    .apt-rating-score {
        font-weight: 700;
        color: #1e293b;
    }

    .apt-rating-count {
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Features Grid */
    .apt-features {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 35px;
    }

    .apt-feature {
        text-align: center;
        padding: 24px 16px;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        transition: all 0.3s;
    }

    .apt-feature:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.1);
    }

    .apt-feature-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
    }

    .apt-feature-icon svg {
        color: #fff;
    }

    .apt-feature-value {
        font-size: 21px;
        font-weight: 700;
        color: #1e293b;
        display: block;
    }

    .apt-feature-label {
        font-size: 0.85rem;
        color: #64748b;
    }

    /* Description */
    .apt-section {
        margin-bottom: 35px;
    }

    .apt-section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .apt-section-title svg {
        color: #6366f1;
    }

    .apt-description {
        color: #475569;
        line-height: 1.8;
        font-size: 1rem;
    }

    /* Amenities */
    .apt-amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .apt-amenity {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 500;
        color: #334155;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .apt-amenity:hover {
        background: #fff;
        border-color: #6366f1;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.1);
        color: #4f46e5;
    }

    .apt-amenity svg {
        color: #10b981;
        flex-shrink: 0;
        width: 20px;
        height: 20px;
    }

    /* Right Sidebar - Booking Card */
    .apt-booking-sidebar {}

    .apt-booking-card {
        background: #fff;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 10px 40px rgba(99, 102, 241, 0.12);
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .apt-price-header {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .apt-price-amount {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .apt-price-period {
        font-size: 1rem;
        color: #64748b;
    }

    /* Booking Form */
    .apt-booking-form .form-group {
        margin-bottom: 18px;
    }

    .apt-booking-form label {
        display: block;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .apt-booking-form input,
    .apt-booking-form select {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-family: inherit;
        font-size: 1rem;
        transition: all 0.3s;
        background: #fff;
    }

    .apt-booking-form input:focus,
    .apt-booking-form select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    /* Price Summary */
    .apt-booking-summary {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .apt-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        color: #64748b;
        font-size: 0.95rem;
    }

    .apt-summary-row.total {
        border-top: 2px solid #e2e8f0;
        margin-top: 12px;
        padding-top: 16px;
        font-weight: 700;
        font-size: 1.15rem;
        color: #1e293b;
    }

    .apt-summary-row.total span:last-child {
        color: #6366f1;
        font-size: 1.3rem;
    }

    /* Book Button */
    .apt-book-btn {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #fff;
        font-family: inherit;
        font-size: 1.1rem;
        font-weight: 700;
        border: none;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .apt-book-btn:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4);
    }

    .apt-book-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Messages */
    .apt-booking-message {
        padding: 16px 20px;
        border-radius: 14px;
        margin-top: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
    }

    .apt-booking-message.success {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .apt-booking-message.error {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* Guarantee */
    .apt-guarantee {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .apt-guarantee p {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #64748b;
        font-size: 0.9rem;
        margin: 0;
    }

    .apt-guarantee svg {
        color: #10b981;
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .apt-content-grid {
            grid-template-columns: 1fr;
        }

        .apt-booking-sidebar {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .apt-gallery {
            grid-template-columns: 1fr;
            height: auto;
        }

        .apt-gallery-main {
            height: 300px;
        }

        .apt-gallery-side {
            display: none;
        }

        .apt-features {
            grid-template-columns: repeat(2, 1fr);
        }

        .apt-amenities-grid {
            grid-template-columns: 1fr 1fr;
        }

        .apt-header {
            flex-direction: column;
            gap: 20px;
        }

        .apt-title {
            font-size: 1.8rem;
        }
    }
</style>

<div class="apt-single">

    <!-- Hero Slider Gallery -->
    <div class="apt-slider-container <?php echo empty($gallery_images) ? 'apt-no-gallery' : ''; ?>">
        <div class="apt-slider-wrapper">
            <?php if (!empty($gallery_images)): ?>
                <?php foreach ($gallery_images as $index => $image): ?>
                    <div class="apt-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo esc_url($image['full']); ?>" alt="">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="apt-slide no-image-slide">
                    <div class="no-image-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                            <circle cx="9" cy="9" r="2" />
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                        </svg>
                        <p><?php _e('No images available', 'appointix'); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (count($gallery_images) > 1): ?>
            <div class="apt-slider-dots">
                <?php for ($i = 0; $i < count($gallery_images); $i++): ?>
                    <div class="apt-slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="apt-container">

        <!-- Content Grid -->
        <div class="apt-content-grid">

            <!-- Left: Info -->
            <div class="apt-info">

                <div class="apt-header">
                    <div class="apt-header-left">
                        <span class="apt-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <?php echo esc_html($type_label); ?>
                        </span>
                        <h1 class="apt-title"><?php echo esc_html($apartment->name); ?></h1>
                        <?php if ($apartment->location): ?>
                            <p class="apt-location">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?php echo esc_html($apartment->location); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                </div>

                <!-- Features -->
                <div class="apt-features">
                    <div class="apt-feature">
                        <div class="apt-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 4v16" />
                                <path d="M2 8h18a2 2 0 0 1 2 2v10" />
                                <path d="M2 17h20" />
                                <path d="M6 8v9" />
                            </svg>
                        </div>
                        <span class="apt-feature-value"><?php echo esc_html($apartment->bedrooms ?: '—'); ?></span>
                        <span class="apt-feature-label"><?php _e('Bedrooms', 'appointix'); ?></span>
                    </div>
                    <div class="apt-feature">
                        <div class="apt-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5" />
                                <line x1="10" x2="8" y1="5" y2="7" />
                                <line x1="2" x2="22" y1="12" y2="12" />
                                <line x1="7" x2="7" y1="19" y2="21" />
                                <line x1="17" x2="17" y1="19" y2="21" />
                            </svg>
                        </div>
                        <span class="apt-feature-value"><?php echo esc_html($apartment->bathrooms ?: '—'); ?></span>
                        <span class="apt-feature-label"><?php _e('Bathrooms', 'appointix'); ?></span>
                    </div>
                    <div class="apt-feature">
                        <div class="apt-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <span class="apt-feature-value"><?php echo esc_html($apartment->max_guests ?: '—'); ?></span>
                        <span class="apt-feature-label"><?php _e('Max Guests', 'appointix'); ?></span>
                    </div>
                    <div class="apt-feature">
                        <div class="apt-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="18" x="3" y="3" rx="2" />
                                <path d="M3 9h18" />
                                <path d="M9 21V9" />
                            </svg>
                        </div>
						<span
							class="apt-feature-value"><?php echo esc_html( $type_label ); ?></span>
						<span class="apt-feature-label"><?php _e( 'View Type', 'appointix' ); ?></span>
					</div>
				</div>

                <!-- Description -->
                <div class="apt-section">
                    <h3 class="apt-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <?php _e('About This Property', 'appointix'); ?>
                    </h3>
                    <div class="apt-description">
                        <?php the_content(); ?>
                    </div>
                </div>
                
                <!-- Key Features (based on type) -->
                <div class="apt-section">
                    <h3 class="apt-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <?php _e('Key Features', 'appointix'); ?>
                    </h3>
                    <ul class="apt-key-features">
                        <?php 
						if ( $apartment->apartment_type === 'sea_view' ) {
							$features = array(
								__( '50 m²', 'appointix' ),
								__( 'Two bedrooms', 'appointix' ),
								__( 'Sea & mountain views', 'appointix' ),
								__( 'Balcony & terrace', 'appointix' ),
								__( 'Fully equipped kitchen', 'appointix' ),
								__( 'Air conditioning', 'appointix' ),
								__( 'Free WiFi', 'appointix' ),
								__( 'Washing machine', 'appointix' ),
								__( 'Non-smoking', 'appointix' ),
								__( 'Family-friendly', 'appointix' )
							);
						} else {
							// Mountain View / Default
							$features = array(
								__( '50 m²', 'appointix' ),
								__( 'Two bedrooms', 'appointix' ),
								__( 'Two bathrooms', 'appointix' ),
								__( 'Mountain & partial sea views', 'appointix' ),
								__( 'Balcony & terrace', 'appointix' ),
								__( 'Fully equipped kitchen', 'appointix' ),
								__( 'Air conditioning', 'appointix' ),
								__( 'Free WiFi', 'appointix' ),
								__( 'Washing machine', 'appointix' ),
								__( 'Non-smoking', 'appointix' )
							);
						}
                        
                        foreach ( $features as $feature ) : ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <?php echo esc_html( $feature ); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <style>
                    .apt-key-features {
                        list-style: none;
                        padding: 0;
                        margin: 0;
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 10px;
                    }
                    .apt-key-features li {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        color: #475569;
                        font-size: 1rem;
                    }
                    .apt-key-features li svg {
                        color: #10b981;
                        flex-shrink: 0;
                    }
                </style>

                <!-- Amenities -->
                <?php if (!empty($amenities)): ?>
                    <div class="apt-section">
                        <h3 class="apt-section-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <polyline points="9 11 12 14 22 4" />
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                            </svg>
                            <?php _e('What This Place Offers', 'appointix'); ?>
                        </h3>
                        <div class="apt-amenities-grid">
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="apt-amenity">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <?php echo esc_html($amenity); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
						
						<br><br>

						<!-- Elfsight Booking.com Reviews | Untitled Booking.com Reviews -->
<script src="https://elfsightcdn.com/platform.js" async></script>
<div class="elfsight-app-b0776f2c-ec72-469a-8c95-31ce7316eb02" data-elfsight-app-lazy></div>
						
						
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Booking Card -->
            <div class="apt-booking-sidebar">
                <div class="apt-booking-card">
                    <div class="apt-price-header">
                        <span
                            class="apt-price-amount"><?php echo esc_html($currency . number_format($apartment->price_per_night, 0)); ?></span>
                        <span class="apt-price-period">/ <?php _e('night', 'appointix'); ?></span>
                    </div>

                    <div class="apt-trust-signal" style="margin-bottom: 20px; text-align: center;">
                         <span style="display: inline-flex; align-items: center; gap: 6px; background: #e6f7ff; color: #003580; padding: 8px 12px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; border: 1px solid #bae7ff;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <?php _e( 'Verified by Booking.com', 'appointix' ); ?>
                        </span>
                    </div>

                    <form id="apt-booking-form" class="apt-booking-form"
                        data-apartment-id="<?php echo esc_attr($apartment->id); ?>"
                        data-price="<?php echo esc_attr($apartment->price_per_night); ?>">
                        <div class="form-group">
                            <label><?php _e('Check-in / Check-out', 'appointix'); ?></label>
                            <input type="text" id="apt-date-picker"
                                placeholder="<?php _e('Select dates', 'appointix'); ?>" readonly required>
                            <input type="hidden" id="apt-check-in" name="check_in">
                            <input type="hidden" id="apt-check-out" name="check_out">
                        </div>

                        <div class="apt-booking-summary" id="apt-summary" style="display: none;">
                            <div class="apt-summary-row">
                                <span><?php echo esc_html($currency); ?><span
                                        id="apt-ppn"><?php echo number_format($apartment->price_per_night, 0); ?></span>
                                    × <span id="apt-nights">0</span> <?php _e('nights', 'appointix'); ?></span>
                                <span><?php echo esc_html($currency); ?><span id="apt-subtotal">0</span></span>
                            </div>
                            <div class="apt-summary-row total">
                                <span><?php _e('Total', 'appointix'); ?></span>
                                <span><?php echo esc_html($currency); ?><span id="apt-total">0</span></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php _e('Your Name', 'appointix'); ?></label>
                            <input type="text" id="apt-name" name="name" required
                                placeholder="<?php _e('Full name', 'appointix'); ?>">
                        </div>

                        <div class="form-group">
                            <label><?php _e('Email', 'appointix'); ?></label>
                            <input type="email" id="apt-email" name="email" required
                                placeholder="<?php _e('your@email.com', 'appointix'); ?>">
                        </div>

                        <div class="form-group">
                            <label><?php _e('Phone', 'appointix'); ?></label>
                            <input type="tel" id="apt-phone" name="phone"
                                placeholder="<?php _e('+1 234 567 8900', 'appointix'); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><?php _e('Adults', 'appointix'); ?></label>
                                <select id="apt-adults" name="adults">
                                    <?php for ($i = 1; $i <= min($apartment->max_guests ?: 10, 10); $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><?php _e('Children', 'appointix'); ?></label>
                                <select id="apt-children" name="children">
                                    <?php for ($i = 0; $i <= 5; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="apartment_id" value="<?php echo esc_attr($apartment->id); ?>">
                        <input type="hidden" name="total_price" id="apt-total-hidden" value="0">
                        <input type="hidden" name="action" value="appointix_submit_booking">
                        <input type="hidden" name="nonce"
                            value="<?php echo wp_create_nonce('appointix_public_nonce'); ?>">

                        <button type="submit" class="apt-book-btn" id="apt-submit" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            <?php _e('Send Booking Request', 'appointix'); ?>
                        </button>
                    </form>

                    <div class="apt-booking-message" id="apt-message" style="display: none;"></div>

                    <div class="apt-guarantee">
                        <p>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <polyline points="9 12 12 15 16 10" />
                            </svg>
                            <?php _e("You won't be charged yet", 'appointix'); ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Booking Form Logic
        (function () {
            var form = document.getElementById('apt-booking-form');
            var dateInput = document.getElementById('apt-date-picker');
            var checkInInput = document.getElementById('apt-check-in');
            var checkOutInput = document.getElementById('apt-check-out');
            var summaryDiv = document.getElementById('apt-summary');
            var submitBtn = document.getElementById('apt-submit');
            var messageDiv = document.getElementById('apt-message');

            if (!form || !dateInput) return;

            var pricePerNight = parseFloat(form.dataset.price) || 0;
            var currency = '<?php echo esc_js($currency); ?>';

            if (typeof flatpickr !== 'undefined') {
                flatpickr(dateInput, {
                    mode: 'range',
                    minDate: 'today',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y',
                    onChange: function (dates) {
                        if (dates.length === 2) {
                            var checkIn = dates[0];
                            var checkOut = dates[1];
                            checkInInput.value = flatpickr.formatDate(checkIn, 'Y-m-d');
                            checkOutInput.value = flatpickr.formatDate(checkOut, 'Y-m-d');

                            var nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                            if (nights > 0) {
                                var total = nights * pricePerNight;
                                document.getElementById('apt-nights').textContent = nights;
                                document.getElementById('apt-subtotal').textContent = total.toLocaleString();
                                document.getElementById('apt-total').textContent = total.toLocaleString();
                                document.getElementById('apt-total-hidden').value = total.toFixed(2);
                                summaryDiv.style.display = 'block';
                                submitBtn.disabled = false;
                            }
                        } else {
                            summaryDiv.style.display = 'none';
                            submitBtn.disabled = true;
                        }
                    }
                });
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(form);
                formData.append('date', checkInInput.value);
                formData.append('end_date', checkOutInput.value);
                formData.append('time', '14:00');
                formData.append('post_id', formData.get('apartment_id'));

                submitBtn.disabled = true;
                var originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = 'Processing...';

                fetch(appointix_public.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        messageDiv.style.display = 'flex';
                        if (data.success) {
                            messageDiv.className = 'apt-booking-message success';
                            messageDiv.innerHTML = data.data.message;
                            form.reset();
                            summaryDiv.style.display = 'none';
                            submitBtn.style.display = 'none';
                        } else {
                            messageDiv.className = 'apt-booking-message error';
                            messageDiv.innerHTML = data.data.message;
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                        }
                    })
                    .catch(function () {
                        messageDiv.style.display = 'flex';
                        messageDiv.className = 'apt-booking-message error';
                        messageDiv.textContent = 'An error occurred. Please try again.';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
            });
        })();

        // 2. Slider Logic
        (function () {
            var container = document.querySelector('.apt-slider-container');
            var wrapper = document.querySelector('.apt-slider-wrapper');
            var dots = document.querySelectorAll('.apt-slider-dot');
            var slides = document.querySelectorAll('.apt-slide');
            if (!wrapper || slides.length <= 1) return;

            var currentIndex = 0;
            var slideInterval;

            function startAutoSlide() {
                stopAutoSlide();
                slideInterval = setInterval(function () {
                    currentIndex = (currentIndex + 1) % slides.length;
                    goToSlide(currentIndex);
                }, 5000);
            }

            function stopAutoSlide() {
                if (slideInterval) clearInterval(slideInterval);
            }

            function goToSlide(index) {
                currentIndex = index;
                slides.forEach(function (s) { s.classList.remove('active'); });
                if (slides[index]) slides[index].classList.add('active');

                dots.forEach(function (d) { d.classList.remove('active'); });
                if (dots[index]) dots[index].classList.add('active');
            }

            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    var index = parseInt(this.getAttribute('data-index'));
                    goToSlide(index);
                    startAutoSlide(); // Reset interval
                });
            });

            container.addEventListener('mouseenter', stopAutoSlide);
            container.addEventListener('mouseleave', startAutoSlide);

            startAutoSlide();
        })();

        // 3. No Gallery Animation
        (function () {
            var emptyGallery = document.querySelector('.apt-slider-container.apt-no-gallery');
            if (emptyGallery) {
                setTimeout(function () {
                    emptyGallery.classList.add('collapsed');
                }, 1000);
            }
        })();

        // 4. Rating Popup Logic
        (function () {
            var openBtn = document.getElementById('open-rating-popup');
            var modal = document.getElementById('apt-rating-modal');
            var closeBtn = document.getElementById('close-rating-modal');
            var stars = document.querySelectorAll('.apt-rating-star');
            var submitBtn = document.getElementById('submit-rating');
            var selectedRating = 0;

            if (!openBtn || !modal) return;

            openBtn.addEventListener('click', function () {
                modal.classList.add('active');
            });

            closeBtn.addEventListener('click', function () {
                modal.classList.remove('active');
            });

            stars.forEach(function (star) {
                star.addEventListener('mouseover', function () {
                    var val = parseInt(this.getAttribute('data-value'));
                    highlightStars(val);
                });
                star.addEventListener('mouseout', function () {
                    highlightStars(selectedRating);
                });
                star.addEventListener('click', function () {
                    selectedRating = parseInt(this.getAttribute('data-value'));
                    highlightStars(selectedRating);
                });
            });

            function highlightStars(val) {
                stars.forEach(function (star) {
                    var starVal = parseInt(star.getAttribute('data-value'));
                    star.classList.toggle('active', starVal <= val);
                });
            }

            submitBtn.addEventListener('click', function () {
            if (selectedRating === 0) {
                alert('Please select a rating');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            var formData = new FormData();
            formData.append('action', 'appointix_submit_rating');
            formData.append('nonce', appointix_public.nonce);
            formData.append('post_id', '<?php echo get_the_ID(); ?>');
            formData.append('rating', selectedRating);

            fetch(appointix_public.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    // Update header values
                    var scoreEl = document.getElementById('header-rating-score');
                    var countEl = document.getElementById('header-rating-count');
                    
                    if (scoreEl) scoreEl.textContent = data.data.new_score;
                    if (countEl) countEl.textContent = data.data.new_count;

                    alert(data.data.message);
                    modal.classList.remove('active');
                } else {
                    alert(data.data.message || 'Error submitting rating.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Rating';
                }
            })
            .catch(function() {
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Rating';
            });
        });
        })();
    });
</script>


<?php get_footer(); ?>