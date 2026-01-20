(function ($) {
    'use strict';

    $(function () {

        const $modal = $('#appointix-modal');
        const $addBtn = $('#appointix-add-service-btn');
        const $closeBtn = $('.appointix-modal-close');
        const $form = $('#appointix-add-service-form');
        const $list = $('#appointix-services-list');

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
                        // Optional: Show toast notification
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });

        // Delete Booking via AJAX
        $(document).on('click', '.appointix-delete-booking', function () {
            if (!confirm(appointix_admin.confirm_delete)) return;

            const id = $(this).data('id');
            const $row = $(this).closest('tr');

            $.ajax({
                url: appointix_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_delete_booking',
                    id: id,
                    nonce: appointix_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $row.fadeOut(300, function () {
                            $(this).remove();
                        });
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
                        html += '<thead><tr><th>Start</th><th>End</th><th>Price</th></tr></thead><tbody>';
                        response.data.forEach(function (rate) {
                            html += `<tr><td>${rate.start_date}</td><td>${rate.end_date}</td><td>$${rate.price}</td></tr>`;
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
            const post_id = $('#post_id').val();
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


    });

})(jQuery);
