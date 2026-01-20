<?php
/**
 * Template for displaying the apartments catalog list with filters and booking modal.
 */

// Get currency symbol
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

// Get current filter
$current_filter = isset($_GET['apt_type']) ? sanitize_text_field($_GET['apt_type']) : 'all';
?>

<style>
    /* Filter Bar inherits from style.css but with local layout */
    .aptx-filter-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 15px;
        padding: 30px 0;
        margin-bottom: 40px;
        border-bottom: 1px solid #e2e8f0;
    }

    .aptx-filter-label {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.1rem;
    }

    /* Modal Styles */
    .aptx-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(8px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .aptx-modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .aptx-modal {
        background: #fff;
        border-radius: 24px;
        width: 100%;
        max-width: 1200px;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transform: translateY(20px);
        transition: transform 0.3s;
    }

    .aptx-modal-overlay.active .aptx-modal {
        transform: translateY(0);
    }

    .aptx-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 30px;
        border-bottom: 1px solid #e2e8f0;
    }

    .aptx-modal-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .aptx-modal-close {
        width: 40px;
        height: 40px;
        border: none;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .aptx-modal-close:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    /* Modal Tabs */
    .aptx-modal-tabs {
        display: flex;
        gap: 0;
        padding: 0 30px;
        background: #f8fafc;
    }

    .aptx-modal-tab {
        padding: 16px 24px;
        border: none;
        background: transparent;
        color: #64748b;
        font-family: inherit;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        position: relative;
        transition: color 0.2s;
    }

    .aptx-modal-tab:hover {
        color: #4f46e5;
    }

    .aptx-modal-tab.active {
        color: #4f46e5;
    }

    .aptx-modal-tab.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #6366f1, #4f46e5);
        border-radius: 3px 3px 0 0;
    }

    /* Modal Content */
    .aptx-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 30px;
    }

    .aptx-tab-content {
        display: none;
    }

    .aptx-tab-content.active {
        display: block;
    }

    /* Booking Form in Modal */
    .aptx-booking-form .form-group {
        margin-bottom: 20px;
    }

    .aptx-booking-form label {
        display: block;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .aptx-booking-form input,
    .aptx-booking-form select {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 1rem;
        transition: all 0.3s;
        box-sizing: border-box;
    }

    .aptx-booking-form input:focus,
    .aptx-booking-form select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .aptx-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .aptx-price-summary {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 16px;
        padding: 20px;
        margin: 20px 0;
    }

    .aptx-price-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        color: #64748b;
    }

    .aptx-price-row.total {
        border-top: 2px solid #e2e8f0;
        margin-top: 10px;
        padding-top: 15px;
        font-weight: 700;
        color: #1e293b;
        font-size: 1.2rem;
    }

    .aptx-price-row.total span:last-child {
        color: #6366f1;
    }

    .aptx-submit-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        border: none;
        border-radius: 14px;
        font-family: inherit;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .aptx-submit-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
    }

    .aptx-submit-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .aptx-message {
        padding: 16px;
        border-radius: 12px;
        margin-top: 16px;
        text-align: center;
    }

    .aptx-message.success {
        background: #d1fae5;
        color: #065f46;
    }

    .aptx-message.error {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Details Tab */
    .aptx-details-tab-layout {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .aptx-details-top-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .aptx-details-bottom-content {
        border-top: 1px solid #e2e8f0;
        padding-top: 30px;
        margin-top: 10px;
    }

    .aptx-details-grid {
        display: none;
        /* Old class, replaced by layout above */
    }

    .aptx-details-gallery img {
        width: 100%;
        border-radius: 16px;
        height: 250px;
        object-fit: cover;
    }

    .aptx-details-info h3 {
        margin: 0 0 15px;
        font-size: 1.5rem;
        color: #1e293b;
    }

    .aptx-details-location {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        margin-bottom: 20px;
    }

    .aptx-details-location svg {
        color: #6366f1;
    }

    .aptx-details-features {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .aptx-detail-feature {
        text-align: center;
        padding: 16px;
        background: #f8fafc;
        border-radius: 12px;
    }

    .aptx-detail-feature-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }

    .aptx-detail-feature-label {
        font-size: 0.8rem;
        color: #64748b;
    }

    .aptx-details-description {
        color: #475569;
        line-height: 1.7;
    }

    .aptx-amenities-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .aptx-amenity-tag {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #e0e7ff;
        color: #4338ca;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .aptx-filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .aptx-form-row {
            grid-template-columns: 1fr;
        }

        .aptx-details-grid {
            grid-template-columns: 1fr;
        }

        .aptx-details-features {
            grid-template-columns: repeat(3, 1fr);
        }

        .aptx-modal {
            max-height: 95vh;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }
    }
</style>

<div class="appointix-catalog-container">

    <!-- Filter Bar -->
    <div class="aptx-filter-bar">
        <span class="aptx-filter-label"><?php _e('Explore by Apartment Type', 'appointix'); ?></span>
        <div class="aptx-unified-filters">
            <button class="aptx-filter-btn <?php echo $current_filter === 'all' ? 'active' : ''; ?>"
                data-filter="all"><?php _e('All', 'appointix'); ?></button>
            <?php
            // Get unique view types from the apartments in the list
            $available_types = array_unique(array_map(function ($apt) {
                return $apt->apartment_type;
            }, $apartments));

            foreach ($type_labels as $type_key => $type_name):
                if (in_array($type_key, $available_types)): ?>
                    <button class="aptx-filter-btn <?php echo $current_filter === $type_key ? 'active' : ''; ?>"
                        data-filter="<?php echo esc_attr($type_key); ?>"><?php echo esc_html($type_name); ?></button>
                <?php endif;
            endforeach; ?>
        </div>
    </div>

    <!-- Apartments Grid -->
    <div class="appointix-catalog-grid" id="aptx-apartments-grid">
        <?php if (!empty($apartments)): ?>
            <?php foreach ($apartments as $apartment): ?>
                <?php
                $type_label = isset($type_labels[$apartment->apartment_type])
                    ? $type_labels[$apartment->apartment_type]
                    : ucfirst(str_replace('_', ' ', $apartment->apartment_type));

                $amenities = array();
                if (!empty($apartment->amenities)) {
                    $amenities = array_map('trim', explode(',', $apartment->amenities));
                }
                ?>
                <div class="appointix-catalog-card" data-apartment-type="<?php echo esc_attr($apartment->apartment_type); ?>"
                    data-apartment-id="<?php echo esc_attr($apartment->id); ?>"
                    data-apartment-name="<?php echo esc_attr($apartment->name); ?>"
                    data-apartment-price="<?php echo esc_attr($apartment->price_per_night); ?>"
                    data-apartment-location="<?php echo esc_attr($apartment->location); ?>"
                    data-apartment-bedrooms="<?php echo esc_attr($apartment->bedrooms); ?>"
                    data-apartment-bathrooms="<?php echo esc_attr($apartment->bathrooms); ?>"
                    data-apartment-guests="<?php echo esc_attr($apartment->max_guests); ?>"
                    data-apartment-image="<?php echo esc_attr($apartment->thumbnail); ?>"
                    data-apartment-summary="<?php echo esc_attr($apartment->property_summary); ?>"
                    data-apartment-description="<?php echo esc_attr(wp_strip_all_tags($apartment->description)); ?>"
                    data-apartment-amenities="<?php echo esc_attr($apartment->amenities); ?>">
                    <div class="appointix-catalog-image">
                        <?php if ($apartment->thumbnail): ?>
                            <img src="<?php echo esc_url($apartment->thumbnail); ?>"
                                alt="<?php echo esc_attr($apartment->name); ?>">
                        <?php else: ?>
                            <div class="appointix-catalog-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="appointix-catalog-badge"><?php echo esc_html($type_label); ?></div>
                        <div class="appointix-catalog-price">
                            <?php echo esc_html($currency . number_format($apartment->price_per_night, 0)); ?>
                            <span>/<?php _e('night', 'appointix'); ?></span>
                        </div>
                    </div>
                    <div class="appointix-catalog-content">
                        <h3 class="appointix-catalog-name"><?php echo esc_html($apartment->name); ?></h3>
                        <?php if ($apartment->location): ?>
                            <p class="appointix-catalog-location">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?php echo esc_html($apartment->location); ?>
                            </p>
                        <?php endif; ?>
                        <div class="appointix-catalog-features">
                            <?php if ($apartment->bedrooms): ?>
                                <span class="appointix-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path d="M2 4v16" />
                                        <path d="M2 8h18a2 2 0 0 1 2 2v10" />
                                        <path d="M2 17h20" />
                                        <path d="M6 8v9" />
                                    </svg>
                                    <?php echo esc_html($apartment->bedrooms); ?>             <?php _e('Beds', 'appointix'); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($apartment->bathrooms): ?>
                                <span class="appointix-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5" />
                                        <line x1="2" x2="22" y1="12" y2="12" />
                                    </svg>
                                    <?php echo esc_html($apartment->bathrooms); ?>             <?php _e('Baths', 'appointix'); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($apartment->max_guests): ?>
                                <span class="appointix-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                    </svg>
                                    <?php echo esc_html($apartment->max_guests); ?>             <?php _e('Guests', 'appointix'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="appointix-catalog-footer">
                            <a href="<?php echo esc_url($apartment->permalink); ?>" class="appointix-catalog-btn-secondary">
                                <?php _e('View Details', 'appointix'); ?>
                            </a>
                            <button type="button" class="appointix-catalog-btn-primary aptx-open-modal">
                                <?php _e('Book Now', 'appointix'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="appointix-no-results">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5">
                    <path d="m21 21-4.35-4.35" />
                    <circle cx="11" cy="11" r="8" />
                </svg>
                <p><?php _e('No apartments found.', 'appointix'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Booking Modal -->
<div class="aptx-modal-overlay" id="aptx-booking-modal">
    <div class="aptx-modal">
        <div class="aptx-modal-header">
            <h2 class="aptx-modal-title" id="aptx-modal-apartment-name"></h2>
            <button type="button" class="aptx-modal-close" id="aptx-modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <div class="aptx-modal-tabs">
            <button type="button" class="aptx-modal-tab active"
                data-tab="booking"><?php _e('Book Now', 'appointix'); ?></button>
            <button type="button" class="aptx-modal-tab"
                data-tab="details"><?php _e('Apartment Details', 'appointix'); ?></button>
        </div>

        <div class="aptx-modal-body">
            <!-- Booking Tab -->
            <div class="aptx-tab-content active" id="aptx-tab-booking">
                <form id="aptx-modal-booking-form" class="aptx-booking-form">
                    <div class="form-group">
                        <label><?php _e('Check-in / Check-out', 'appointix'); ?></label>
                        <input type="text" id="aptx-modal-dates" placeholder="<?php _e('Select dates', 'appointix'); ?>"
                            readonly required>
                        <input type="hidden" id="aptx-modal-check-in" name="check_in">
                        <input type="hidden" id="aptx-modal-check-out" name="check_out">
                    </div>

                    <div class="aptx-price-summary" id="aptx-modal-price-summary" style="display: none;">
                        <div class="aptx-price-row">
                            <span><?php echo esc_html($currency); ?><span id="aptx-modal-ppn">0</span> × <span
                                    id="aptx-modal-nights">0</span> <?php _e('nights', 'appointix'); ?></span>
                            <span><?php echo esc_html($currency); ?><span id="aptx-modal-subtotal">0</span></span>
                        </div>
                        <div class="aptx-price-row total">
                            <span><?php _e('Total', 'appointix'); ?></span>
                            <span><?php echo esc_html($currency); ?><span id="aptx-modal-total">0</span></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php _e('Full Name', 'appointix'); ?></label>
                        <input type="text" id="aptx-modal-name" name="name" required
                            placeholder="<?php _e('Your name', 'appointix'); ?>">
                    </div>

                    <div class="aptx-form-row">
                        <div class="form-group">
                            <label><?php _e('Email', 'appointix'); ?></label>
                            <input type="email" id="aptx-modal-email" name="email" required
                                placeholder="<?php _e('you@email.com', 'appointix'); ?>">
                        </div>
                        <div class="form-group">
                            <label><?php _e('Phone', 'appointix'); ?></label>
                            <input type="tel" id="aptx-modal-phone" name="phone"
                                placeholder="<?php _e('+1 234 567 890', 'appointix'); ?>">
                        </div>
                    </div>

                    <div class="aptx-form-row">
                        <div class="form-group">
                            <label><?php _e('Adults', 'appointix'); ?></label>
                            <select id="aptx-modal-adults" name="adults">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php _e('Children', 'appointix'); ?></label>
                            <select id="aptx-modal-children" name="children">
                                <?php for ($i = 0; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" id="aptx-modal-apartment-id" name="apartment_id" value="">
                    <input type="hidden" id="aptx-modal-total-price" name="total_price" value="0">

                    <button type="submit" class="aptx-submit-btn" id="aptx-modal-submit" disabled>
                        <?php _e('Confirm Booking', 'appointix'); ?>
                    </button>

                    <div class="aptx-message" id="aptx-modal-message" style="display: none;"></div>
                </form>
            </div>

            <!-- Details Tab -->
            <div class="aptx-tab-content" id="aptx-tab-details">
                <div class="aptx-details-tab-layout">
                    <div class="aptx-details-top-split">
                        <div class="aptx-details-gallery">
                            <img id="aptx-details-image" src="" alt="">
                        </div>
                        <div class="aptx-details-info">
                            <h3 id="aptx-details-name" style="margin-top:0;"></h3>
                            <p class="aptx-details-location">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <span id="aptx-details-location"></span>
                            </p>
                            <div class="aptx-details-features">
                                <div class="aptx-detail-feature">
                                    <div class="aptx-detail-feature-value" id="aptx-details-beds">—</div>
                                    <div class="aptx-detail-feature-label"><?php _e('Bedrooms', 'appointix'); ?></div>
                                </div>
                                <div class="aptx-detail-feature">
                                    <div class="aptx-detail-feature-value" id="aptx-details-baths">—</div>
                                    <div class="aptx-detail-feature-label"><?php _e('Bathrooms', 'appointix'); ?></div>
                                </div>
                                <div class="aptx-detail-feature">
                                    <div class="aptx-detail-feature-value" id="aptx-details-guests">—</div>
                                    <div class="aptx-detail-feature-label"><?php _e('Guests', 'appointix'); ?></div>
                                </div>
                            </div>
                            <div class="aptx-about-section" id="aptx-about-section" style="display:none;">
                                <h4 style="margin: 0 0 10px; font-size: 1.1rem; color: #1e293b;">
                                    <?php _e('Property Summary', 'appointix'); ?>
                                </h4>
                                <p class="aptx-details-summary" id="aptx-details-summary"
                                    style="color: #475569; line-height: 1.7; margin: 0;"></p>
                            </div>
                            <div class="aptx-amenities-list" id="aptx-details-amenities"></div>
                        </div>
                    </div>

                    <div class="aptx-details-bottom-content">
                        <h4 style="margin: 0 0 20px; font-size: 1.3rem; color: #1e293b;">
                            <?php _e('Full Description', 'appointix'); ?>
                        </h4>
                        <div id="aptx-details-full-content" class="aptx-full-content">
                            <!-- AJAX Content will load here -->
                            <div class="aptx-content-loader" style="text-align: center; padding: 40px;">
                                <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    style="animation: spin 1s linear infinite;">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                </svg>
                                <p><?php _e('Loading content...', 'appointix'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="aptx-currency" value="<?php echo esc_attr($currency); ?>">
    <input type="hidden" id="aptx-ajax-url" value="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>">
    <input type="hidden" id="aptx-nonce" value="<?php echo esc_attr(wp_create_nonce('appointix_public_nonce')); ?>">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('aptx-booking-modal');
            var modalClose = document.getElementById('aptx-modal-close');
            var currentApartment = {};
            var modalDatePicker = null;
            var currency = document.getElementById('aptx-currency').value || '$';
            var ajaxUrl = document.getElementById('aptx-ajax-url').value;
            var nonce = document.getElementById('aptx-nonce').value;

            // Filter functionality
            document.querySelectorAll('.aptx-filter-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var filter = this.dataset.filter;

                    document.querySelectorAll('.aptx-filter-btn').forEach(function (b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');

                    document.querySelectorAll('.appointix-catalog-card').forEach(function (card) {
                        if (filter === 'all' || card.dataset.apartmentType === filter) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Open modal
            document.querySelectorAll('.aptx-open-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = this.closest('.appointix-catalog-card');

                    currentApartment = {
                        id: card.dataset.apartmentId,
                        name: card.dataset.apartmentName,
                        price: parseFloat(card.dataset.apartmentPrice) || 0,
                        location: card.dataset.apartmentLocation,
                        bedrooms: card.dataset.apartmentBedrooms,
                        bathrooms: card.dataset.apartmentBathrooms,
                        guests: card.dataset.apartmentGuests,
                        image: card.dataset.apartmentImage,
                        summary: card.dataset.apartmentSummary,
                        description: card.dataset.apartmentDescription,
                        amenities: card.dataset.apartmentAmenities
                    };

                    // Populate modal
                    document.getElementById('aptx-modal-apartment-name').textContent = currentApartment.name;
                    document.getElementById('aptx-modal-apartment-id').value = currentApartment.id;
                    document.getElementById('aptx-modal-ppn').textContent = currentApartment.price.toLocaleString();

                    // Details tab
                    document.getElementById('aptx-details-name').textContent = currentApartment.name;
                    document.getElementById('aptx-details-location').textContent = currentApartment.location || '—';
                    document.getElementById('aptx-details-beds').textContent = currentApartment.bedrooms || '—';
                    document.getElementById('aptx-details-baths').textContent = currentApartment.bathrooms || '—';
                    document.getElementById('aptx-details-guests').textContent = currentApartment.guests || '—';
                    document.getElementById('aptx-details-image').src = currentApartment.image || '';

                    // About This Property (summary)
                    var aboutSection = document.getElementById('aptx-about-section');
                    var summaryEl = document.getElementById('aptx-details-summary');
                    if (currentApartment.summary) {
                        summaryEl.textContent = currentApartment.summary;
                        aboutSection.style.display = 'block';
                    } else {
                        aboutSection.style.display = 'none';
                    }

                    // Fetch and Render Full Content via AJAX
                    var fullContentEl = document.getElementById('aptx-details-full-content');

                    if (fullContentEl) {
						fullContentEl.innerHTML = '<div class="aptx-content-loader" style="text-align: center; padding: 40px; color: #6366f1;"><svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite; margin-bottom: 10px;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg><p style="margin: 0; font-weight: 500;"><?php echo esc_js( __( 'Loading design...', 'appointix' ) ); ?></p></div>';

                        fetch(ajaxUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'appointix_get_apartment_content',
                                post_id: currentApartment.id,
                                nonce: nonce
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.success && data.data) {
                                    // Inject Elementor CSS if provided
                                    if (data.data.elementor_css && data.data.elementor_css.length > 0) {
                                        data.data.elementor_css.forEach(function (cssUrl) {
                                            if (!document.querySelector('link[href="' + cssUrl + '"]')) {
                                                var link = document.createElement('link');
                                                link.rel = 'stylesheet';
                                                link.href = cssUrl;
                                                document.head.appendChild(link);
                                            }
                                        });
                                    }

                                    fullContentEl.innerHTML = data.data.content || '<p class="error">No content returned.</p>';

                                    // Trigger re-initialization for Elementor and other plugins
                                    setTimeout(function () {
                                        window.dispatchEvent(new Event('resize'));

                                        // Force Elementor to re-init widgets in the new content
                                        if (window.elementorFrontend && window.elementorFrontend.hooks) {
                                            var $content = jQuery(fullContentEl);
                                            elementorFrontend.hooks.doAction('frontend/element_ready/global', $content);
                                        }
                                    }, 200);
                                } else {
                                    var msg = (data && data.data && data.data.message) ? data.data.message : 'Failed to load content.';
                                    fullContentEl.innerHTML = '<p class="error">' + msg + '</p>';
                                }
                            })
                            .catch(error => {
                                console.error('Appointix AJAX error:', error);
                                fullContentEl.innerHTML = '<p class="error">An error occurred while loading content. Please try again.</p>';
                            });
                    }

                    // Amenities
                    var amenitiesEl = document.getElementById('aptx-details-amenities');
                    amenitiesEl.innerHTML = '';
                    if (currentApartment.amenities) {
                        currentApartment.amenities.split(',').forEach(function (amenity) {
                            amenity = amenity.trim();
                            if (amenity) {
                                amenitiesEl.innerHTML += '<span class="aptx-amenity-tag">✓ ' + amenity + '</span>';
                            }
                        });
                    }

                    // Reset form
                    document.getElementById('aptx-modal-booking-form').reset();
                    document.getElementById('aptx-modal-price-summary').style.display = 'none';
                    document.getElementById('aptx-modal-submit').disabled = true;
                    document.getElementById('aptx-modal-message').style.display = 'none';

                    // Init date picker
                    if (typeof flatpickr !== 'undefined') {
                        if (modalDatePicker) {
                            modalDatePicker.destroy();
                        }
                        modalDatePicker = flatpickr('#aptx-modal-dates', {
                            mode: 'range',
                            minDate: 'today',
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'M j, Y',
                            onChange: function (dates) {
                                if (dates.length === 2) {
                                    var checkIn = dates[0];
                                    var checkOut = dates[1];
                                    var nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));

                                    document.getElementById('aptx-modal-check-in').value = flatpickr.formatDate(checkIn, 'Y-m-d');
                                    document.getElementById('aptx-modal-check-out').value = flatpickr.formatDate(checkOut, 'Y-m-d');

                                    if (nights > 0) {
                                        var total = nights * currentApartment.price;
                                        document.getElementById('aptx-modal-nights').textContent = nights;
                                        document.getElementById('aptx-modal-subtotal').textContent = total.toLocaleString();
                                        document.getElementById('aptx-modal-total').textContent = total.toLocaleString();
                                        document.getElementById('aptx-modal-total-price').value = total.toFixed(2);
                                        document.getElementById('aptx-modal-price-summary').style.display = 'block';
                                        document.getElementById('aptx-modal-submit').disabled = false;
                                    }
                                } else {
                                    document.getElementById('aptx-modal-price-summary').style.display = 'none';
                                    document.getElementById('aptx-modal-submit').disabled = true;
                                }
                            }
                        });
                    }

                    // Show modal
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';

                    // Switch to booking tab
                    switchTab('booking');
                });
            });

            // Close modal
            function closeModal() {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }

            modalClose.addEventListener('click', closeModal);
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
            });

            // Tab switching
            function switchTab(tabName) {
                document.querySelectorAll('.aptx-modal-tab').forEach(function (tab) {
                    tab.classList.toggle('active', tab.dataset.tab === tabName);
                });
                document.querySelectorAll('.aptx-tab-content').forEach(function (content) {
                    content.classList.toggle('active', content.id === 'aptx-tab-' + tabName);
                });
            }

            document.querySelectorAll('.aptx-modal-tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    switchTab(this.dataset.tab);
                });
            });

            // Form submission
            document.getElementById('aptx-modal-booking-form').addEventListener('submit', function (e) {
                e.preventDefault();

                var submitBtn = document.getElementById('aptx-modal-submit');
                var messageEl = document.getElementById('aptx-modal-message');

                submitBtn.disabled = true;
                submitBtn.textContent = '<?php _e('Processing...', 'appointix'); ?>';

                var formData = new FormData(this);
                formData.append('action', 'appointix_submit_booking');
                formData.append('nonce', nonce);
                formData.append('post_id', currentApartment.id);
                formData.append('date', document.getElementById('aptx-modal-check-in').value);
                formData.append('end_date', document.getElementById('aptx-modal-check-out').value);
                formData.append('time', '14:00');

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        messageEl.style.display = 'block';
                        if (data.success) {
                            messageEl.className = 'aptx-message success';
                            messageEl.textContent = data.data.message || '<?php _e('Booking confirmed!', 'appointix'); ?>';
                            submitBtn.style.display = 'none';
                        } else {
                            messageEl.className = 'aptx-message error';
                            messageEl.textContent = data.data.message || '<?php _e('An error occurred', 'appointix'); ?>';
                            submitBtn.disabled = false;
                            submitBtn.textContent = '<?php _e('Confirm Booking', 'appointix'); ?>';
                        }
                    })
                    .catch(function () {
                        messageEl.style.display = 'block';
                        messageEl.className = 'aptx-message error';
                        messageEl.textContent = '<?php _e('An error occurred. Please try again.', 'appointix'); ?>';
                        submitBtn.disabled = false;
                        submitBtn.textContent = '<?php _e('Confirm Booking', 'appointix'); ?>';
                    });
            });
        });
    </script>