$(document).ready(function() {
    console.log("User Dashboard JavaScript loaded");
    
    // Initialize dashboard
    initializeDashboard();
    
    // Tab switching functionality
    $('.tab-button').on('click', function() {
        const targetTab = $(this).data('tab');
        
        // Update active tab button
        $('.tab-button').removeClass('active tab-active');
        $(this).addClass('active tab-active');
        
        // Switch tab content
        $('.tab-content').addClass('hidden');
        $(`#${targetTab}`).removeClass('hidden');
        
        if (targetTab === 'bookings') {
            loadUserBookings();
        }
    });
    
    // Booking form submission
    $('#bookingForm').on('submit', function(e) {
        e.preventDefault();
        processBooking();
    });
    
    // Clear selection button
    $(document).on('click', '#clearSelection', function() {
        clearAllSelections();
    });
    
    // Payment modal handlers
    $('#closePaymentModal, #cancelPayment').on('click', function() {
        $('#paymentModal').addClass('hidden');
    });
    
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        processPayment();
    });
    
    // Card number formatting
    $('#cardNumber').on('input', function() {
        let value = $(this).val().replace(/\s/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        $(this).val(formattedValue);
    });
    
    // Expiry date formatting
    $('#expiryDate').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        $(this).val(value);
    });
    
    // CVV number only
    $('#cvv').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });
});

function initializeDashboard() {
    console.log("Initializing dashboard...");
    
    // Load stations
    loadStations();
    
    // Initialize date picker
    const today = new Date();
    const maxDate = new Date();
    maxDate.setDate(today.getDate() + 30); // 30 days from today
    
    $('#bookingDate').attr('min', today.toISOString().split('T')[0]);
    $('#bookingDate').attr('max', maxDate.toISOString().split('T')[0]);
    
    // Initialize time pickers
    initializeTimePickers();
    
    // Load user stats
    loadUserStats();
    
    console.log("Dashboard initialized successfully");
}

function loadStations() {
    console.log("Loading stations...");
    
    $.ajax({
        url: '../../backend/api/stations.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("Stations loaded:", response);
            if (response.success && response.data) {
                populateStationCards(response.data);
            } else {
                console.error("Failed to load stations:", response.message);
                showError("Failed to load gaming stations. Please refresh the page.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error loading stations:", error);
            console.error("Status:", status);
            console.error("Response:", xhr.responseText);
            showError("Error connecting to server. Please check your connection.");
        }
    });
}

function populateStationCards(stations) {
    console.log("Populating station cards with data:", stations);
    const container = $('#stationCards');
    
    if (!container.length) {
        console.error("Station cards container not found!");
        return;
    }
    
    container.empty();
    
    if (!stations || stations.length === 0) {
        container.html(`
            <div class="col-span-full text-center py-8">
                <div class="text-gray-400">
                    <i class="fas fa-gamepad text-4xl mb-4"></i>
                    <p>No gaming stations available at the moment.</p>
                </div>
            </div>
        `);
        return;
    }
    
    stations.forEach(station => {
        const isAvailable = station.status === 'active';
        const statusClass = getStatusClass(station.status);
        const statusText = getStatusText(station.status);
        
        const cardHtml = `
            <div class="station-card ${!isAvailable ? 'disabled' : ''}" 
                 data-station-id="${station.id}" 
                 data-station-name="${station.station_name}"
                 data-station-type="${station.station_type}"
                 data-rate="${station.hourly_rate}">
                
                <div class="selected-indicator">
                    <i class="fas fa-check"></i>
                </div>
                
                <div class="station-status ${statusClass}"></div>
                
                <div class="station-card-content">
                    <div class="station-type-badge">
                        ${station.station_type}
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2">
                        ${station.station_name}
                    </h3>
                    
                    <p class="text-gray-300 text-sm mb-3 line-clamp-2">
                        ${station.description || 'Premium gaming experience awaits!'}
                    </p>
                    
                    <div class="flex justify-between items-center">
                        <div class="station-price">
                            LKR ${parseFloat(station.hourly_rate).toFixed(2)}/hr
                        </div>
                        <div class="text-xs text-gray-400">
                            <i class="fas fa-circle mr-1"></i>${statusText}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(cardHtml);
    });
    
    // Add click handlers for station selection
    $('.station-card:not(.disabled)').on('click', function() {
        toggleStationSelection($(this));
    });
    
    console.log(`Added ${stations.length} station cards to the page`);
}

function getStatusClass(status) {
    switch(status) {
        case 'active': return '';
        case 'maintenance': return 'maintenance';
        case 'inactive': return 'inactive';
        default: return 'inactive';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'active': return 'Available';
        case 'maintenance': return 'Maintenance';
        case 'inactive': return 'Offline';
        default: return 'Unknown';
    }
}

function toggleStationSelection(card) {
    const stationId = card.data('station-id');
    const isSelected = card.hasClass('selected');
    
    if (isSelected) {
        // Deselect
        card.removeClass('selected');
        console.log(`Deselected station ${stationId}`);
    } else {
        // Select
        card.addClass('selected');
        console.log(`Selected station ${stationId}`);
    }
    
    updateSelectionSummary();
    updateBookingSummary();
}

function updateSelectionSummary() {
    const selectedCards = $('.station-card.selected');
    const count = selectedCards.length;
    
    if (count > 0) {
        $('#selectionSummary').removeClass('hidden');
        $('#selectedCount').text(count);
        
        const stationsList = selectedCards.map(function() {
            const name = $(this).data('station-name');
            const rate = $(this).data('rate');
            return `<span class="inline-block bg-purple-700/50 px-2 py-1 rounded text-xs mr-2 mb-1">
                        ${name} - LKR ${parseFloat(rate).toFixed(2)}/hr
                    </span>`;
        }).get().join('');
        
        $('#selectedStationsList').html(stationsList);
    } else {
        $('#selectionSummary').addClass('hidden');
    }
}

function clearAllSelections() {
    $('.station-card.selected').removeClass('selected');
    updateSelectionSummary();
    updateBookingSummary();
}

function initializeTimePickers() {
    // Start time picker
    flatpickr("#startTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minTime: "09:00",
        maxTime: "20:00",
        minuteIncrement: 30,
        onChange: function(selectedDates, dateStr) {
            updateEndTimeOptions();
            updateBookingSummary();
        }
    });
    
    // End time picker
    flatpickr("#endTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minTime: "09:30",
        maxTime: "20:00",
        minuteIncrement: 30,
        onChange: function(selectedDates, dateStr) {
            updateBookingSummary();
        }
    });
    
    // Date change handler
    $('#bookingDate').on('change', function() {
        updateBookingSummary();
    });
}

function updateEndTimeOptions() {
    const startTime = $('#startTime').val();
    if (!startTime) return;
    
    const [hours, minutes] = startTime.split(':').map(Number);
    const startMinutes = hours * 60 + minutes + 30; // Add 30 minutes minimum
    
    const endHours = Math.floor(startMinutes / 60);
    const endMins = startMinutes % 60;
    
    const minEndTime = String(endHours).padStart(2, '0') + ':' + String(endMins).padStart(2, '0');
    
    // Update the end time picker
    const endTimePicker = document.querySelector("#endTime")._flatpickr;
    if (endTimePicker) {
        endTimePicker.set('minTime', minEndTime);
        endTimePicker.set('maxTime', '23:00');
    }
}

function updateBookingSummary() {
    const selectedStations = $('.station-card.selected');
    const date = $('#bookingDate').val();
    const startTime = $('#startTime').val();
    const endTime = $('#endTime').val();
    
    if (selectedStations.length > 0 && date && startTime && endTime) {
        const duration = calculateDuration(startTime, endTime);
        const totalAmount = calculateTotalAmount(selectedStations, duration);
        
        if (duration > 0) {
            $('#bookingSummary').removeClass('hidden');
            $('#summaryDuration').text(`${duration} hour${duration !== 1 ? 's' : ''}`);
            $('#summaryAmount').text(`LKR ${totalAmount.toFixed(2)}`);
        } else {
            $('#bookingSummary').addClass('hidden');
        }
    } else {
        $('#bookingSummary').addClass('hidden');
    }
}

function calculateDuration(startTime, endTime) {
    if (!startTime || !endTime) return 0;
    
    const [startHours, startMinutes] = startTime.split(':').map(Number);
    const [endHours, endMinutes] = endTime.split(':').map(Number);
    
    const startTotalMinutes = startHours * 60 + startMinutes;
    const endTotalMinutes = endHours * 60 + endMinutes;
    
    if (endTotalMinutes <= startTotalMinutes) return 0;
    
    return (endTotalMinutes - startTotalMinutes) / 60;
}

function calculateTotalAmount(selectedStations, duration) {
    let total = 0;
    selectedStations.each(function() {
        const rate = parseFloat($(this).data('rate'));
        total += rate * duration;
    });
    return total;
}

function processBooking() {
    const selectedStations = $('.station-card.selected');
    
    if (selectedStations.length === 0) {
        showError("Please select at least one gaming station.");
        return;
    }
    
    const bookingData = {
        booking_date: $('#bookingDate').val(),
        start_time: $('#startTime').val(),
        end_time: $('#endTime').val(),
        notes: $('#notes').val(),
        station_ids: []
    };
    
    // Validate booking data
    if (!bookingData.booking_date || !bookingData.start_time || !bookingData.end_time) {
        showError("Please fill in all required fields.");
        return;
    }
    
    const duration = calculateDuration(bookingData.start_time, bookingData.end_time);
    if (duration <= 0) {
        showError("End time must be after start time.");
        return;
    }
    
    // Collect selected station IDs
    selectedStations.each(function() {
        bookingData.station_ids.push($(this).data('station-id'));
    });
    
    // For payment modal display, we still need station details
    const stationDetails = [];
    selectedStations.each(function() {
        stationDetails.push({
            station_id: $(this).data('station-id'),
            station_name: $(this).data('station-name'),
            rate: $(this).data('rate')
        });
    });
    
    const totalAmount = calculateTotalAmount(selectedStations, duration);
    
    // Show payment modal
    showPaymentModal(bookingData, stationDetails, duration, totalAmount);
}

function showPaymentModal(bookingData, stationDetails, duration, totalAmount) {
    // Populate payment modal with booking details
    const stationNames = stationDetails.map(s => s.station_name).join(', ');
    $('#paymentStation').text(stationNames);
    $('#paymentDate').text(bookingData.booking_date);
    $('#paymentTime').text(`${bookingData.start_time} - ${bookingData.end_time}`);
    $('#paymentDuration').text(`${duration} hour${duration !== 1 ? 's' : ''}`);
    $('#paymentAmount').text(`LKR ${totalAmount.toFixed(2)}`);
    
    // Store booking data for payment processing
    $('#paymentModal').data('bookingData', bookingData);
    $('#paymentModal').removeClass('hidden');
    
    // Clear previous form data
    $('#paymentForm')[0].reset();
}

function processPayment() {
    const bookingData = $('#paymentModal').data('bookingData');
    const paymentData = {
        card_number: $('#cardNumber').val(),
        card_holder: $('#cardHolder').val(),
        expiry_date: $('#expiryDate').val(),
        cvv: $('#cvv').val()
    };
    
    // Validate payment data
    if (!paymentData.card_number || !paymentData.card_holder || 
        !paymentData.expiry_date || !paymentData.cvv) {
        showError("Please fill in all payment details.");
        return;
    }
    
    // Disable payment button and show loading
    const submitBtn = $('#processPayment');
    const originalText = submitBtn.find('#paymentButtonText').text();
    submitBtn.prop('disabled', true);
    submitBtn.find('#paymentButtonText').text('Processing...');
    
    // Submit booking
    $.ajax({
        url: '../../backend/api/bookings.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(bookingData),
        success: function(response) {
            console.log("Booking response:", response);
            
            // Parse response if it's a string
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    console.error("Failed to parse response:", response);
                    showError("Invalid response from server. Please try again.");
                    return;
                }
            }
            
            if (response.success) {
                showSuccess("Booking confirmed! Your gaming session is ready.");
                $('#paymentModal').addClass('hidden');
                
                // Reset form
                $('#bookingForm')[0].reset();
                clearAllSelections();
                $('#bookingSummary').addClass('hidden');
                
                // Refresh bookings tab
                loadUserBookings();
                loadUserStats();
            } else {
                showError(response.message || "Booking failed. Please try again.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Booking error:", error);
            console.error("Response:", xhr.responseText);
            
            // Try to parse error response for specific error message
            try {
                const errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse.message) {
                    showError(errorResponse.message);
                } else {
                    showError("Failed to process booking. Please try again.");
                }
            } catch (e) {
                showError("Failed to process booking. Please try again.");
            }
        },
        complete: function() {
            // Re-enable payment button
            submitBtn.prop('disabled', false);
            submitBtn.find('#paymentButtonText').text(originalText);
        }
    });
}

function loadUserBookings() {
    console.log("Loading user bookings...");
    
    $.ajax({
        url: '../../backend/api/bookings.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("Bookings loaded:", response);
            
            if (response.success && response.data) {
                populateBookingsTable(response.data);
            } else {
                console.error("Failed to load bookings:", response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error loading bookings:", error);
            console.error("Response:", xhr.responseText);
        }
    });
}

function populateBookingsTable(bookings) {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#userBookingsTable')) {
        $('#userBookingsTable').DataTable().destroy();
    }
    
    // Clear existing content
    $('#userBookingsTable tbody').empty();
    
    // Populate with new data
    bookings.forEach(booking => {
        const statusBadge = getStatusBadge(booking.status);
        const formattedDate = new Date(booking.booking_date).toLocaleDateString();
        const createdDate = new Date(booking.created_at).toLocaleDateString();
        const duration = calculateDuration(booking.start_time, booking.end_time);
        
        const row = `
            <tr>
                <td><span class="font-mono text-purple-300">#${booking.id}</span></td>
                <td class="font-medium">${booking.station_name || 'N/A'}</td>
                <td>${formattedDate}</td>
                <td class="font-mono">${booking.start_time} - ${booking.end_time}</td>
                <td>${duration}h</td>
                <td class="font-bold text-green-400">LKR ${parseFloat(booking.total_amount).toFixed(2)}</td>
                <td>${statusBadge}</td>
                <td>${createdDate}</td>
            </tr>
        `;
        
        $('#userBookingsTable tbody').append(row);
    });
    
    // Initialize DataTable with gaming theme
    $('#userBookingsTable').DataTable({
        order: [[0, 'desc']], // Sort by booking ID descending
        pageLength: 10,
        responsive: true,
        language: {
            search: "Search Sessions:",
            lengthMenu: "Show _MENU_ sessions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ gaming sessions",
            infoEmpty: "No gaming sessions found",
            infoFiltered: "(filtered from _MAX_ total sessions)",
            emptyTable: "No gaming sessions booked yet. Start your gaming journey!",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
}

function getStatusBadge(status) {
    const statusClasses = {
        'pending': 'status-badge status-pending',
        'confirmed': 'status-badge status-confirmed',
        'completed': 'status-badge status-completed',
        'cancelled': 'status-badge status-cancelled'
    };
    
    const statusClass = statusClasses[status] || 'status-badge status-pending';
    return `<span class="${statusClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
}

function loadUserStats() {
    console.log("Loading user stats...");
    
    $.ajax({
        url: '../../backend/api/bookings.php?stats=true',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("Stats loaded:", response);
            
            if (response.success && response.stats) {
                updateStatsDisplay(response.stats);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error loading stats:", error);
        }
    });
}

function updateStatsDisplay(stats) {
    $('#totalBookings').text(stats.total_bookings || 0);
    $('#totalHours').text(stats.total_hours || 0);
    $('#totalAmount').text(parseFloat(stats.total_amount || 0).toFixed(2));
}

function showError(message) {
    // Create error toast/notification
    const errorHtml = `
        <div class="fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-lg shadow-lg z-50 error-toast">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="$(this).parent().parent().remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    $('body').append(errorHtml);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        $('.error-toast').fadeOut(() => {
            $('.error-toast').remove();
        });
    }, 5000);
}

function showSuccess(message) {
    // Create success toast/notification
    const successHtml = `
        <div class="fixed top-4 right-4 bg-green-600 text-white px-6 py-4 rounded-lg shadow-lg z-50 success-toast">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="$(this).parent().parent().remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    $('body').append(successHtml);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        $('.success-toast').fadeOut(() => {
            $('.success-toast').remove();
        });
    }, 5000);
}
