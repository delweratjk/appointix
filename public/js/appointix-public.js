(function ($) {
    'use strict';

    $(function () {
        // Initialize Nice Select
        if ($.fn.niceSelect) {
            $('select').niceSelect();
        }

        var currentStep = 1;
        var bookingData = {
            post_id: 0,
            date: '',
            end_date: '',
            time: '14:00', // Default check-in time
            name: '',
            email: '',
            phone: '',
            adults: 1,
            children: 0,
            total_price: 0,
            price_per_night: 0,
            nights: 0
        };

        var $container = $('.appointix-booking-container');
        var $steps = $('.appointix-booking-step');
        var $stepIndicators = $('.appointix-step');
        var $nextBtn = $('#appointix-next-btn');
        var $prevBtn = $('#appointix-prev-btn');
        var currency = $('#currency-symbol').val() || '$';

        // Initialize date range picker
        var dateRangePicker = null;
        var $dateRangeInput = $('#booking_date_range');

        if ($dateRangeInput.length && typeof flatpickr !== 'undefined') {
            dateRangePicker = flatpickr('#booking_date_range', {
                mode: 'range',
                minDate: 'today',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'M j, Y',
                onChange: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        var checkIn = selectedDates[0];
                        var checkOut = selectedDates[1];

                        bookingData.date = flatpickr.formatDate(checkIn, 'Y-m-d');
                        bookingData.end_date = flatpickr.formatDate(checkOut, 'Y-m-d');

                        $('#booking_date').val(bookingData.date);
                        $('#booking_end_date').val(bookingData.end_date);

                        // Calculate nights and price
                        var diffTime = Math.abs(checkOut - checkIn);
                        var nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                        if (nights > 0) {
                            bookingData.nights = nights;
                            var pricePerNight = parseFloat(bookingData.price_per_night) || 0;
                            var total = nights * pricePerNight;
                            bookingData.total_price = total;

                            $('#booking-total-price').val(total.toFixed(2));

                            // Update price summary
                            $('#price-per-night').text(currency + pricePerNight.toFixed(2));
                            $('#number-of-nights').text(nights);
                            $('#total-price').text(currency + total.toFixed(2));
                            $('#appointix-price-summary').show();
                        }
                    } else {
                        bookingData.date = '';
                        bookingData.end_date = '';
                        bookingData.nights = 0;
                        bookingData.total_price = 0;
                        $('#appointix-price-summary').hide();
                    }
                }
            });
        }

        // Legacy single date picker
        var legacyDatePicker = null;
        var $legacyDateInput = $('#booking_date:not([type="hidden"])');

        if ($legacyDateInput.length && !$dateRangeInput.length && typeof flatpickr !== 'undefined') {
            legacyDatePicker = flatpickr('#booking_date', {
                minDate: 'today',
                dateFormat: 'Y-m-d',
                onChange: function (selectedDates, dateStr) {
                    if (dateStr) {
                        bookingData.date = dateStr;
                    }
                }
            });
        }

        // Check for preselected apartment
        var $preselected = $('.appointix-service-card.selected');
        if ($preselected.length) {
            bookingData.post_id = $preselected.attr('data-id');
            bookingData.price_per_night = parseFloat($preselected.attr('data-price')) || 0;

            // Also check hidden fields
            if (!bookingData.post_id) {
                bookingData.post_id = $('#selected-service-id').val();
            }
            if (!bookingData.price_per_night) {
                bookingData.price_per_night = parseFloat($('#selected-price-per-night').val()) || 0;
            }

            if (bookingData.post_id) {
                fetchBookedDates(bookingData.post_id);
            }

            // If step 1 is hidden, we're on step 2
            if ($('#appointix-step-1').is(':hidden')) {
                currentStep = 2;
                $stepIndicators.removeClass('active completed');
                $stepIndicators.eq(0).addClass('completed');
                $stepIndicators.eq(1).addClass('active');
            }
        }

        // Apartment Selection
        $(document).on('click', '.appointix-service-card', function () {
            $('.appointix-service-card').removeClass('selected active');
            $(this).addClass('selected active');

            bookingData.post_id = $(this).attr('data-id');
            bookingData.price_per_night = parseFloat($(this).attr('data-price')) || 0;

            $('#selected-service-id').val(bookingData.post_id);
            $('#selected-price-per-night').val(bookingData.price_per_night);

            // Reset dates when selecting new apartment
            if (dateRangePicker) {
                dateRangePicker.clear();
            }
            bookingData.date = '';
            bookingData.end_date = '';
            bookingData.nights = 0;
            bookingData.total_price = 0;
            $('#appointix-price-summary').hide();

            fetchBookedDates(bookingData.post_id);
        });

        function fetchBookedDates(postId) {
            $.ajax({
                url: appointix_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_get_booked_dates',
                    post_id: postId,
                    nonce: appointix_public.nonce
                },
                success: function (response) {
                    if (response.success) {
                        var disabledDates = response.data.dates || [];
                        if (dateRangePicker) {
                            dateRangePicker.set('disable', disabledDates);
                        }
                        if (legacyDatePicker) {
                            legacyDatePicker.set('disable', disabledDates);
                        }
                    }
                }
            });
        }

        function fetchAvailableSlots(date) {
            var $slotContainer = $('#appointix-time-slots');
            if (!$slotContainer.length) return;

            $slotContainer.html('<div class="loading" style="text-align: center; padding: 20px; color: #64748b;">Loading available times...</div>');

            $.ajax({
                url: appointix_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_get_available_slots',
                    post_id: bookingData.post_id,
                    date: date,
                    nonce: appointix_public.nonce
                },
                success: function (response) {
                    if (response.success) {
                        var html = '';
                        if (response.data.slots && response.data.slots.length > 0) {
                            response.data.slots.forEach(function (slot) {
                                html += '<div class="appointix-time-slot" data-time="' + slot.time + '">' + slot.label + '</div>';
                            });
                        } else {
                            html = '<p style="text-align: center; color: #64748b;">No times available for this date.</p>';
                        }
                        $slotContainer.html(html);
                    }
                }
            });
        }

        // Time Slot Selection
        $(document).on('click', '.appointix-time-slot', function () {
            $('.appointix-time-slot').removeClass('active');
            $(this).addClass('active');
            bookingData.time = $(this).data('time');
        });

        // Next Button Click
        $nextBtn.on('click', function () {
            if (currentStep === 1) {
                if (!bookingData.post_id) {
                    showAlert('Please select an apartment first.');
                    return;
                }
                goToStep(2);
            } else if (currentStep === 2) {
                if (!bookingData.date) {
                    showAlert('Please select your check-in and check-out dates.');
                    return;
                }
                if (!bookingData.end_date) {
                    showAlert('Please select both check-in and check-out dates.');
                    return;
                }
                goToStep(3);
            } else if (currentStep === 3) {
                bookingData.name = $('#customer_name').val();
                bookingData.email = $('#customer_email').val();
                bookingData.phone = $('#customer_phone').val();

                if (!bookingData.name || !bookingData.email) {
                    showAlert('Please fill in your name and email.');
                    return;
                }

                // Validate email
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(bookingData.email)) {
                    showAlert('Please enter a valid email address.');
                    return;
                }

                bookingData.adults = $('#hotel_adults').val() || 1;
                bookingData.children = $('#hotel_children').val() || 0;

                // Show summary and go to step 4
                showBookingSummary();
                goToStep(4);
            } else if (currentStep === 4) {
                submitBooking();
            }
        });

        // Previous Button Click
        $prevBtn.on('click', function () {
            if (currentStep > 1) {
                // If step 1 is hidden, don't go back to it
                if (currentStep === 2 && $('#appointix-step-1').is(':hidden')) {
                    return;
                }
                goToStep(currentStep - 1);
            }
        });

        function showAlert(message) {
            // Create a nicer alert
            var $alert = $('<div class="appointix-alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: #ef4444; color: #fff; padding: 16px 24px; border-radius: 12px; z-index: 10000; box-shadow: 0 10px 40px rgba(0,0,0,0.2);"></div>');
            $alert.text(message);
            $('body').append($alert);

            setTimeout(function () {
                $alert.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }

        function showBookingSummary() {
            var $summary = $('#summary-content');
            var selectedCard = $('.appointix-service-card.selected');
            var apartmentName = selectedCard.find('.service-name').text() || 'Selected Apartment';

            var html = '<div style="display: grid; gap: 12px;">';
            html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Apartment:</span><strong>' + apartmentName + '</strong></div>';
            html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Check-in:</span><strong>' + formatDate(bookingData.date) + '</strong></div>';

            if (bookingData.end_date) {
                html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Check-out:</span><strong>' + formatDate(bookingData.end_date) + '</strong></div>';
                html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Nights:</span><strong>' + bookingData.nights + '</strong></div>';
            }

            html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Guests:</span><strong>' + bookingData.adults + ' Adults, ' + bookingData.children + ' Children</strong></div>';
            html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Name:</span><strong>' + bookingData.name + '</strong></div>';
            html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Email:</span><strong>' + bookingData.email + '</strong></div>';

            if (bookingData.phone) {
                html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><span style="color: #64748b;">Phone:</span><strong>' + bookingData.phone + '</strong></div>';
            }

            html += '<div style="display: flex; justify-content: space-between; padding: 15px 0; margin-top: 10px; border-top: 2px solid #4f46e5;"><span style="font-size: 1.2rem; font-weight: 600;">Total:</span><strong style="font-size: 1.4rem; color: #4f46e5;">' + currency + parseFloat(bookingData.total_price).toFixed(2) + '</strong></div>';
            html += '</div>';

            $summary.html(html);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            var date = new Date(dateStr);
            var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        function goToStep(step) {
            $steps.hide();
            $('#appointix-step-' + step).fadeIn(300);

            $stepIndicators.removeClass('active completed');
            $stepIndicators.each(function (index) {
                var stepIdx = index + 1;
                if (stepIdx < step) {
                    $(this).addClass('completed');
                } else if (stepIdx === step) {
                    $(this).addClass('active');
                }
            });

            currentStep = step;

            // Show/hide prev button
            if (currentStep > 1 && !$('#appointix-step-1').is(':hidden')) {
                $prevBtn.show();
            } else if (currentStep > 2) {
                $prevBtn.show();
            } else {
                $prevBtn.hide();
            }

            // Update next button text
            if (currentStep === 4) {
                $nextBtn.text('Confirm Booking');
            } else {
                $nextBtn.text('Continue');
            }

            // Scroll to top of container
            $('html, body').animate({
                scrollTop: $container.offset().top - 100
            }, 300);
        }

        function submitBooking() {
            $nextBtn.prop('disabled', true).text('Processing...');
            $prevBtn.hide();

            $.ajax({
                url: appointix_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'appointix_submit_booking',
                    nonce: appointix_public.nonce,
                    post_id: bookingData.post_id,
                    date: bookingData.date,
                    end_date: bookingData.end_date,
                    time: bookingData.time,
                    name: bookingData.name,
                    email: bookingData.email,
                    phone: bookingData.phone,
                    adults: bookingData.adults,
                    children: bookingData.children,
                    total_price: bookingData.total_price
                },
                success: function (response) {
                    if (response.success) {
                        $('#booking-summary').hide();
                        $('#booking-success').show();
                        $nextBtn.hide();
                        $('.appointix-footer').html('<a href="' + window.location.href.split('?')[0] + '" class="appointix-btn-next" style="text-decoration: none;">Make Another Booking</a>');
                    } else {
                        showAlert(response.data.message || 'An error occurred. Please try again.');
                        $nextBtn.prop('disabled', false).text('Confirm Booking');
                        $prevBtn.show();
                    }
                },
                error: function () {
                    showAlert('An error occurred. Please try again.');
                    $nextBtn.prop('disabled', false).text('Confirm Booking');
                    $prevBtn.show();
                }
            });
        }
    });
})(jQuery);
