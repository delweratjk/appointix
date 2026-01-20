<?php
/**
 * Template for displaying apartment details when accessed via query parameter
 */

// Get the apartment ID from the query parameter
$apartment_id = isset($_GET['apartment_id']) ? intval($_GET['apartment_id']) : 0;

if ($apartment_id) {
    $apartment = Appointix_Apartments_Model::get_apartment($apartment_id);

    if ($apartment) {
        $currency = get_option('appointix_currency', '$');
        get_header();
        ?>
        <style>
            .appointix-apartment-details {
                max-width: 1200px;
                margin: 50px auto;
                padding: 0 20px;
                font-family: 'Outfit', sans-serif;
            }

            .appointix-apartment-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }

            .appointix-apartment-header h1 {
                font-size: 2.5rem;
                margin: 0;
                color: #111827;
            }

            .appointix-price-badge {
                background: #4f46e5;
                color: #fff;
                padding: 10px 25px;
                border-radius: 50px;
                font-size: 1.5rem;
                font-weight: 700;
            }
            
            .appointix-apartment-description {
                font-size: 1.1rem;
                line-height: 1.8;
                color: #4b5563;
            }

            .appointix-booking-widget {
                background: #fff;
                border-radius: 24px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
                position: sticky;
                top: 100px;
            }

            .appointix-booking-widget h3 {
                margin-top: 0;
                margin-bottom: 20px;
                font-size: 1.5rem;
                text-align: center;
            }

            @media (max-width: 768px) {
                .appointix-apartment-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="appointix-apartment-details">
            

            <div class="appointix-apartment-grid">
                <div class="appointix-apartment-sidebar">
                    <div class="appointix-booking-widget">
                        <h3>
                            <?php _e('Book This Apartment', 'appointix'); ?>
                        </h3>
                        <?php echo do_shortcode('[appointix_booking apartment_id="' . $apartment->id . '"]'); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        get_header();
        get_footer();
        exit;
    }
}
