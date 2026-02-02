(function ($) {
    'use strict';

    $(function () {

        const $modal = $('#appointix-modal');
        const $addBtn = $('#appointix-add-service-btn');
        const $closeBtn = $('.appointix-modal-close');
        const $form = $('#appointix-add-service-form');
        const $list = $('#appointix-services-list');

        // Initialize Nice Select
        if ($.fn.niceSelect) {
            $('select.appointix-booking-status').niceSelect();
        }

        // Toast Notification
        function showToast(message) {
            // Create toast if not exists
            if ($('#appointix-toast').length === 0) {
                $('body').append('<div id="appointix-toast"></div>');
                $('#appointix-toast').css({
                    'position': 'fixed',
                    'bottom': '20px',
                    'right': '20px',
                    'background': '#32373c',
                    'color': '#fff',
                    'padding': '12px 24px',
                    'border-radius': '4px',
                    'z-index': '99999',
                    'display': 'none',
                    'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
                    'font-size': '14px',
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '10px'
                });
            }

            const $toast = $('#appointix-toast');
            $toast.html('<span class="dashicons dashicons-yes" style="color:#4ade80;"></span> ' + message);
            $toast.fadeIn(300).delay(2000).fadeOut(300);
        }

        // Tab Switching in Bookings Page
        $(document).on('click', '.aptx-tab-item', function (e) {
            e.preventDefault();
            const status = $(this).data('status');

            // UI Update
            $('.aptx-tab-item').removeClass('active');
            $(this).addClass('active');

            // Fetch Bookings
            $('#appointix-bookings-list').css('opacity', '0.5');
            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_get_bookings',
                    status: status,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $('#appointix-bookings-list').html(response.data.html).css('opacity', '1');
                        // Re-init nice select
                        if ($.fn.niceSelect) {
                            $('select.appointix-booking-status').niceSelect();
                        }
                    }
                }
            });
        });

        // Update Booking Status via AJAX
        $(document).on('change', '.appointix-booking-status', function () {
            const id = $(this).data('id');
            const status = $(this).val();

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_update_booking_status',
                    id: id,
                    status: status,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        showToast(response.data.message);
                        updateStats(response.data.stats);
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });

        // Helper to update stats
        function updateStats(stats) {
            if (!stats) return;
            // Bookings Page
            $('.aptx-stat-card.total .aptx-stat-value').text(stats.total);
            $('.aptx-stat-card.pending .aptx-stat-value').text(stats.pending);
            $('.aptx-stat-card.confirmed .aptx-stat-value').text(stats.confirmed);
            $('.aptx-stat-card.completed .aptx-stat-value').text(stats.completed);
            $('.aptx-stat-card.cancelled .aptx-stat-value').text(stats.cancelled);
            $('.aptx-stat-card.trash .aptx-stat-value').text(stats.trash);

            // Dashboard Page
            if ($('.appointix-stat-card').length) {
                $('.appointix-stat-card .stat-value').eq(1).text(stats.total);
                $('.appointix-stat-card .stat-value').eq(2).text(stats.pending);
            }

            // Update Tab Counts
            $('.aptx-tab-item[data-status="active"] .count').text(stats.total);
            $('.aptx-tab-item[data-status="pending"] .count').text(stats.pending);
            $('.aptx-tab-item[data-status="confirmed"] .count').text(stats.confirmed);
            $('.aptx-tab-item[data-status="completed"] .count').text(stats.completed);
            $('.aptx-tab-item[data-status="cancelled"] .count').text(stats.cancelled);
            $('.aptx-tab-item[data-status="trash"] .count').text(stats.trash);
        }

        // Delete Popover Logic
        $(document).on('click', '.appointix-delete-booking, .appointix-permanent-delete-booking', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');
            const isPermanent = $btn.hasClass('appointix-permanent-delete-booking');

            // Remove any existing popovers
            $('.appointix-popover-confirm').remove();

            // Create Popover
            const $popover = $(`
                <div class="appointix-popover-confirm" style="position: absolute; background: #fff; padding: 10px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; z-index: 100; display: flex; gap: 8px; align-items: center; min-width: 180px;">
                    <span style="font-size: 13px; color: #1e293b; font-weight: 500;">Are you sure?</span>
                    <button class="button button-small appointix-popover-cancel">No</button>
                    <button class="button button-small button-link-delete appointix-popover-yes" style="color: #dc2626;">Yes</button>
                </div>
            `);

            $('body').append($popover);

            // Position Popover
            const offset = $btn.offset();
            $popover.css({
                top: offset.top - $popover.outerHeight() - 10,
                left: offset.left
            });

            // Handle Interactions
            $popover.find('.appointix-popover-cancel').on('click', function () {
                $popover.remove();
            });

            $popover.find('.appointix-popover-yes').on('click', function () {
                const action = isPermanent ? 'appointix_permanent_delete_booking' : 'appointix_delete_booking';

                $.ajax({
                    url: appointix_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: action,
                        id: id,
                        nonce: appointix_admin.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            showToast(response.data.message);
                            $btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
                            updateStats(response.data.stats);
                        } else {
                            alert(response.data.message);
                        }
                        $popover.remove();
                    }
                });
            });

            // Close on click outside
            $(document).on('click.appointixPopover', function (e) {
                if (!$(e.target).closest('.appointix-popover-confirm').length && !$(e.target).closest('.appointix-delete-booking').length && !$(e.target).closest('.appointix-permanent-delete-booking').length) {
                    $('.appointix-popover-confirm').remove();
                    $(document).off('click.appointixPopover');
                }
            });
        });


        // Restore Booking
        $(document).on('click', '.appointix-restore-booking', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_restore_booking',
                    id: id,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        showToast(response.data.message);
                        $btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
                        updateStats(response.data.stats);
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });

        // Save Settings via AJAX
        $('#appointix-settings-form').on('submit', function (e) {
            e.preventDefault();
            const data = $(this).serialize();

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: data + '&action=appointix_save_settings&nonce=' + appointix_admin.nonce,
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });

        // Administrative Seasonal Pricing Logic
        function refreshSeasonalPrices(postId) {
            const $list = $('#seasonal-rates-list');
            $list.html('<p style="font-size:12px; color:#999;">Loading rates...</p>');

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_get_seasonal_prices',
                    post_id: postId,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    if (response.success && response.data.length > 0) {
                        let html = '<table class="wp-list-table widefat fixed striped" style="font-size:11px;">';
                        html += '<thead><tr><th>Start</th><th>End</th><th>Price</th><th>Action</th></tr></thead><tbody>';
                        response.data.forEach(function (rate) {
                            html += `<tr>
                                <td>${rate.start_date}</td>
                                <td>${rate.end_date}</td>
                                <td>$${rate.price}</td>
                                <td><a href="#" class="delete-seasonal-rate" data-id="${rate.id}" style="color:#ef4444;">Delete</a></td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        $list.html(html);
                    } else {
                        $list.html('<p style="font-size:12px; color:#92400e; font-style:italic;">No seasonal rates set for this unit yet.</p>');
                    }
                }
            });
        }

        $(document).on('click', '#add-seasonal-price-btn', function () {
            const post_id = $('#appointix_post_id').val();
            if (!post_id) {
                alert('Please save the apartment first before adding seasonal prices.');
                return;
            }

            const start = $('#seasonal_start').val();
            const end = $('#seasonal_end').val();
            const price = $('#seasonal_price').val();

            if (!start || !end || !price) {
                alert('Please fill in all seasonal price fields.');
                return;
            }

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_add_seasonal_price',
                    post_id: post_id,
                    start: start,
                    end: end,
                    price: price,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        refreshSeasonalPrices(post_id);
                        $('#seasonal_start, #seasonal_end, #seasonal_price').val('');
                    } else {
                        alert('Failed to add rate.');
                    }
                }
            });
        });

        // Admin Tab Switching
        $(document).on('click', '.appointix-tab-btn', function () {
            $('.appointix-tab-btn').removeClass('active');
            $(this).addClass('active');

            const tab = $(this).data('tab');
            $('.appointix-tab-content').hide();
            $('#' + tab).show();
        });

        // Copy iCal URL
        $(document).on('click', '.appointix-copy-ical', function (e) {
            e.preventDefault();
            const url = $(this).data('url');
            const $btn = $(this);
            const originalText = $btn.text();

            navigator.clipboard.writeText(url).then(function () {
                $btn.text('Copied!');
                $btn.css('color', '#10b981');
                setTimeout(function () {
                    $btn.text(originalText);
                    $btn.css('color', '#4f46e5');
                }, 2000);
            }).catch(function (err) {
                console.error('Could not copy text: ', err);
                alert('Press Ctrl+C to copy: ' + url);
            });
        });

        // Manual iCal Sync
        $(document).on('click', '.appointix-manual-sync', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const $btn = $(this);
            const originalText = $btn.text();

            $btn.text('Syncing...');

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_manual_ical_sync',
                    id: id,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    alert(response.data.message);
                    $btn.text(originalText);
                },
                error: function () {
                    alert('An error occurred during sync.');
                    $btn.text(originalText);
                }
            });
        });


        // Key Features Defaults Logic
        const featureDefaults = {
            'sea_view': "50 m²\nTwo bedrooms\nSea & mountain views\nBalcony & terrace\nFully equipped kitchen\nAir conditioning\nFree WiFi\nWashing machine\nNon-smoking\nFamily-friendly",
            'mountain_view': "50 m²\nTwo bedrooms\nTwo bathrooms\nMountain & partial sea views\nBalcony & terrace\nFully equipped kitchen\nAir conditioning\nFree WiFi\nWashing machine\nNon-smoking"
        };

        function populateFeatureDefaults(force = false) {
            const $textarea = $('#appointix_key_features');
            const type = $('#appointix_apartment_type').val();

            if (force || !$textarea.val().trim()) {
                const defaults = featureDefaults[type] || featureDefaults['mountain_view'];
                $textarea.val(defaults);
            }
        }

        $(document).on('change', '#appointix_apartment_type', function () {
            populateFeatureDefaults(false); // Only if empty
        });

        $(document).on('click', '#appointix-load-features-default', function (e) {
            e.preventDefault();
            if (confirm('This will replace your current features with defaults for this apartment type. Proceed?')) {
                populateFeatureDefaults(true);
            }
        });

        $(document).on('click', '.delete-seasonal-rate', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this rate?')) return;
            const $btn = $(this);
            const id = $btn.data('id');
            const post_id = $('#appointix_post_id').val();

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_delete_seasonal_price',
                    id: id,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        refreshSeasonalPrices(post_id);
                    } else {
                        alert('Failed to delete rate.');
                    }
                }
            });
        });

        // Initialize seasonal prices if we are on an apartment page
        const initial_post_id = $('#appointix_post_id').val();
        if (initial_post_id) {
            refreshSeasonalPrices(initial_post_id);
        }

        // Pricing Mode Toggle
        function togglePricingMode() {
            const mode = $('#appointix_pricing_mode').val();
            if (mode === 'static') {
                $('#static-pricing-row').show();
                $('#seasonal-pricing-container').hide();
            } else {
                $('#static-pricing-row').hide();
                $('#seasonal-pricing-container').show();
            }
        }

        $(document).on('change', '#appointix_pricing_mode', togglePricingMode);
        togglePricingMode();

        // Make refreshSeasonalPrices globally accessible for the inline script
        window.refreshSeasonalPrices = refreshSeasonalPrices;

    });

})(jQuery);
