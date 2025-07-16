// User Dashboard JavaScript
let userBookingsTable;
let stationsData = [];
let selectedStation = null;
let startTimePicker, endTimePicker;

// Initialize when page loads
$(document).ready(function() {
    initializeTabs();
    initializeDataTables();
    loadUserStats();
    loadStations();
    loadUserBookings();
    initializeBookingForm();
    initializeTimePickers();
    initializeDatePicker();
});

// Tab functionality
function initializeTabs() {
    $('.tab-button').click(function() {
        const tabName = $(this).data('tab');
        
        // Update active tab
        $('.tab-button').removeClass('active border-purple-500 text-purple-600')
                        .addClass('border-transparent text-gray-500');
        $(this).addClass('active border-purple-500 text-purple-600')
               .removeClass('border-transparent text-gray-500');
        
        // Show/hide content
        $('.tab-content').addClass('hidden');
        $(`#${tabName}`).removeClass('hidden');
    });
}

// Initialize DataTables
function initializeDataTables() {
    userBookingsTable = $('#userBookingsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[2, 'desc']], // Sort by date descending
        language: {
            search: "🔍 Search Gaming Sessions:",
            lengthMenu: "Show _MENU_ sessions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ gaming sessions",
            infoEmpty: "No gaming sessions found",
            infoFiltered: "(filtered from _MAX_ total sessions)",
            paginate: {
                first: "⏮️ First",
                last: "⏭️ Last", 
                next: "▶️ Next",
                previous: "◀️ Previous"
            },
            emptyTable: "🎮 No gaming sessions booked yet! Ready to start your arena journey?",
            zeroRecords: "🔍 No matching gaming sessions found. Try adjusting your search!",
            loadingRecords: "⚡ Loading your gaming history...",
            processing: "🎮 Processing..."
        },
        dom: '<"flex flex-col md:flex-row md:justify-between md:items-center mb-6"<"mb-4 md:mb-0"l><"mb-4 md:mb-0"f>>rtip',
        columns: [
            { data: 'booking_reference', title: '<i class="fas fa-hashtag mr-2"></i>Reference' },
            { 
                data: null,
                title: '<i class="fas fa-gamepad mr-2"></i>Station',
                render: function(data, type, row) {
                    return `<span class="font-semibold text-purple-300">${row.station_name}</span><br><small class="text-gray-400">${row.station_type}</small>`;
                }
            },
            { 
                data: 'booking_date', 
                title: '<i class="fas fa-calendar mr-2"></i>Date',
                render: function(data) {
                    return `<span class="text-cyan-300">${new Date(data).toLocaleDateString()}</span>`;
                }
            },
            { 
                data: null,
                title: '<i class="fas fa-clock mr-2"></i>Time',
                render: function(data, type, row) {
                    return `<span class="text-green-300">${row.start_time}</span><br><small class="text-gray-400">to ${row.end_time}</small>`;
                }
            },
            { 
                data: 'total_hours',
                title: '<i class="fas fa-hourglass mr-2"></i>Duration',
                render: function(data) {
                    return `<span class="text-yellow-300 font-semibold">${data}h</span>`;
                }
            },
            { 
                data: 'total_amount',
                title: '<i class="fas fa-coins mr-2"></i>Amount',
                render: function(data) {
                    return `<span class="text-green-400 font-bold">LKR ${parseFloat(data).toFixed(2)}</span>`;
                }
            },
            { 
                data: 'status',
                title: '<i class="fas fa-flag mr-2"></i>Status',
                render: function(data) {
                    const statusStyles = {
                        'pending': 'status-badge status-pending',
                        'confirmed': 'status-badge status-confirmed', 
                        'completed': 'status-badge status-completed',
                        'cancelled': 'status-badge status-cancelled'
                    };
                    return `<span class="${statusStyles[data] || statusStyles.pending}">${data.toUpperCase()}</span>`;
                }
            },
            { 
                data: 'created_at',
                title: '<i class="fas fa-calendar-plus mr-2"></i>Booked On',
                render: function(data) {
                    return `<span class="text-purple-300">${new Date(data).toLocaleDateString()}</span>`;
                }
            }
        ],
        drawCallback: function() {
            // Add custom styling after each draw
            $('#userBookingsTable_wrapper .dataTables_info').addClass('text-gaming-light');
            $('#userBookingsTable_wrapper .dataTables_paginate .paginate_button').addClass('gaming-paginate-btn');
        }
    });
}

// Load user statistics
async function loadUserStats() {
    try {
        const response = await fetch('../../backend/api/bookings.php?user_bookings=1');
        const data = await response.json();
        
        if (data.success) {
            const bookings = data.data;
            $('#totalBookings').text(bookings.length);
            
            const completedBookings = bookings.filter(b => b.status === 'completed');
            const totalHours = completedBookings.reduce((sum, b) => sum + parseFloat(b.total_hours), 0);
            $('#totalHours').text(totalHours.toFixed(1));
            
            const totalSpent = completedBookings.reduce((sum, b) => sum + parseFloat(b.total_amount), 0);
            $('#totalAmount').text(totalSpent.toFixed(2));
        }
    } catch (error) {
        console.error('Error loading user stats:', error);
    }
}

// Load available stations
async function loadStations() {
    try {
        const response = await fetch('../../backend/api/stations.php');
        const data = await response.json();
        
        if (data.success) {
            stationsData = data.data.filter(station => station.status === 'active');
            populateStationSelect();
        } else {
            showAlert('Error loading stations: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading stations', 'error');
        console.error('Error:', error);
    }
}

// Populate station select dropdown
function populateStationSelect() {
    const select = $('#stationSelect');
    select.empty().append('<option value="">Select a station...</option>');
    
    stationsData.forEach(station => {
        const $option = $('<option>', {
            value: station.id,
            text: `${station.station_name} (${station.station_type}) - LKR ${station.hourly_rate}/hr`
        });
        
        // Safely store the station data using jQuery's data method
        $option.data('station', station);
        select.append($option);
    });
}

// Load user bookings
async function loadUserBookings() {
    try {
        const response = await fetch('../../backend/api/bookings.php?user_bookings=1');
        const data = await response.json();
        
        if (data.success) {
            userBookingsTable.clear().rows.add(data.data).draw();
        } else {
            showAlert('Error loading bookings: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading bookings', 'error');
        console.error('Error:', error);
    }
}

// Initialize time pickers with business hours (9 AM - 8 PM)
function initializeTimePickers() {
    // Start time picker configuration
    startTimePicker = flatpickr("#startTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 30,
        minTime: "09:00",
        maxTime: "19:30", // Last start time to allow 30min minimum session ending by 8 PM
        defaultHour: 9,
        defaultMinute: 0,
        onChange: function(selectedDates, dateStr, instance) {
            // Update end time picker minimum based on start time
            if (dateStr) {
                const startTime = selectedDates[0];
                const minEndTime = new Date(startTime.getTime() + 30 * 60000); // Add 30 minutes
                const hours = minEndTime.getHours().toString().padStart(2, '0');
                const minutes = minEndTime.getMinutes().toString().padStart(2, '0');
                
                endTimePicker.set('minTime', `${hours}:${minutes}`);
                endTimePicker.set('maxTime', "20:00"); // Business hours end at 8 PM
                
                // Clear end time if it's now invalid
                const currentEndTime = endTimePicker.selectedDates[0];
                if (currentEndTime && currentEndTime <= startTime) {
                    endTimePicker.clear();
                }
                
                updateBookingSummary();
            }
        }
    });

    // End time picker configuration
    endTimePicker = flatpickr("#endTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 30,
        minTime: "09:30",
        maxTime: "20:00", // Business hours end at 8 PM
        defaultHour: 10,
        defaultMinute: 0,
        onChange: function(selectedDates, dateStr, instance) {
            updateBookingSummary();
        }
    });
}

// Initialize date picker with 5-day limit
function initializeDatePicker() {
    // Calculate date range (today to next 5 days)
    const today = new Date();
    const maxDate = new Date();
    maxDate.setDate(today.getDate() + 5);
    
    flatpickr("#bookingDate", {
        minDate: "today",
        maxDate: maxDate,
        dateFormat: "Y-m-d",
        defaultDate: today,
        onChange: function(selectedDates, dateStr, instance) {
            checkAvailability();
            updateBookingSummary();
        }
    });
}

// Initialize booking form
function initializeBookingForm() {
    // Station selection change
    $('#stationSelect').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            try {
                const stationData = selectedOption.data('station');
                // Handle both string and object cases
                selectedStation = typeof stationData === 'string' ? JSON.parse(stationData) : stationData;
                showStationInfo();
                checkAvailability();
            } catch (error) {
                console.error('Error parsing station data:', error);
                showAlert('Error loading station information', 'error');
                selectedStation = null;
                hideStationInfo();
            }
        } else {
            selectedStation = null;
            hideStationInfo();
        }
        updateBookingSummary();
    });

    // Date change
    $('#bookingDate').change(function() {
        checkAvailability();
        updateBookingSummary();
    });

    // Time changes
    $('#startTime, #endTime').change(function() {
        updateBookingSummary();
    });

    // Form submission
    $('#bookingForm').submit(function(e) {
        e.preventDefault();
        showPaymentModal();
    });
}

// Show station information
function showStationInfo() {
    if (!selectedStation) return;
    
    $('#stationTypeInfo').text(selectedStation.station_type);
    $('#stationRateInfo').text('LKR ' + parseFloat(selectedStation.hourly_rate).toFixed(2));
    $('#stationDescInfo').text(selectedStation.description || 'No description available');
    $('#stationInfo').removeClass('hidden');
}

// Hide station information
function hideStationInfo() {
    $('#stationInfo').addClass('hidden');
    $('#bookingSummary').addClass('hidden');
}

// Check availability for selected station and date
async function checkAvailability() {
    if (!selectedStation || !$('#bookingDate').val()) {
        return;
    }
    
    try {
        const response = await fetch(`../../backend/api/availability.php?station_id=${selectedStation.id}&date=${$('#bookingDate').val()}`);
        const data = await response.json();
        
        if (data.success) {
            // Store unavailable slots for validation
            window.unavailableSlots = data.unavailable_slots || [];
            showAvailabilityInfo(data.unavailable_slots);
        } else {
            console.error('Error checking availability:', data.message);
        }
    } catch (error) {
        console.error('Error checking availability:', error);
    }
}

// Show availability information to user
function showAvailabilityInfo(unavailableSlots) {
    // Always remove existing availability info first
    $('#stationInfo .availability-info').remove();
    
    if (unavailableSlots && unavailableSlots.length > 0) {
        let availabilityHtml = '<div class="availability-info mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">';
        availabilityHtml += '<h4 class="font-semibold text-yellow-800 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Unavailable Times Today:</h4>';
        availabilityHtml += '<ul class="text-sm text-yellow-700">';
        
        unavailableSlots.forEach(slot => {
            const startTime = new Date(`2000-01-01 ${slot.start_time}`).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            const endTime = new Date(`2000-01-01 ${slot.end_time}`).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            availabilityHtml += `<li><i class="fas fa-clock mr-1"></i>${startTime} - ${endTime} (${slot.reason})</li>`;
        });
        
        availabilityHtml += '</ul></div>';
        
        // Add new availability info with proper class
        $('#stationInfo').append(availabilityHtml);
    }
}

// Check if selected time conflicts with unavailable slots
function hasTimeConflict(startTime, endTime, unavailableSlots) {
    if (!unavailableSlots || unavailableSlots.length === 0) {
        return false;
    }
    
    const selectedStart = new Date(`2000-01-01 ${startTime}`);
    const selectedEnd = new Date(`2000-01-01 ${endTime}`);
    
    return unavailableSlots.some(slot => {
        const slotStart = new Date(`2000-01-01 ${slot.start_time}`);
        const slotEnd = new Date(`2000-01-01 ${slot.end_time}`);
        
        // Check for overlap: selected start < slot end AND selected end > slot start
        return selectedStart < slotEnd && selectedEnd > slotStart;
    });
}

// Update booking summary
function updateBookingSummary() {
    const startTimeValue = startTimePicker.selectedDates[0];
    const endTimeValue = endTimePicker.selectedDates[0];
    
    if (!selectedStation || !startTimeValue || !endTimeValue) {
        $('#bookingSummary').addClass('hidden');
        return;
    }
    
    if (endTimeValue <= startTimeValue) {
        $('#bookingSummary').addClass('hidden');
        return;
    }
    
    const durationMs = endTimeValue - startTimeValue;
    const durationHours = durationMs / (1000 * 60 * 60);
    const totalAmount = durationHours * parseFloat(selectedStation.hourly_rate);
    
    $('#summaryDuration').text(`${durationHours.toFixed(1)} hours`);
    $('#summaryAmount').text('LKR ' + totalAmount.toFixed(2));
    $('#bookingSummary').removeClass('hidden');
}

// Submit booking
async function submitBooking() {
    // Get time picker values
    const startTimeValue = startTimePicker.selectedDates[0];
    const endTimeValue = endTimePicker.selectedDates[0];
    
    // Format times for API (HH:MM:SS format)
    const startTime = startTimeValue ? 
        startTimeValue.getHours().toString().padStart(2, '0') + ':' + 
        startTimeValue.getMinutes().toString().padStart(2, '0') + ':00' : '';
    const endTime = endTimeValue ? 
        endTimeValue.getHours().toString().padStart(2, '0') + ':' + 
        endTimeValue.getMinutes().toString().padStart(2, '0') + ':00' : '';

    const formData = {
        station_id: $('#stationSelect').val(),
        booking_date: $('#bookingDate').val(),
        start_time: startTime,
        end_time: endTime,
        notes: $('#notes').val()
    };
    
    // Validation
    if (!formData.station_id || !formData.booking_date || !formData.start_time || !formData.end_time) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }
    
    if (endTimeValue <= startTimeValue) {
        showAlert('End time must be after start time', 'error');
        return;
    }
    
    // Check for time conflicts with unavailable slots
    if (window.unavailableSlots && hasTimeConflict(startTime, endTime, window.unavailableSlots)) {
        showAlert('Selected time conflicts with unavailable slots. Please choose a different time.', 'error');
        return;
    }
    
    // Business hours validation (9 AM to 8 PM)
    const startHour = startTimeValue.getHours();
    const endHour = endTimeValue.getHours();
    const endMinute = endTimeValue.getMinutes();
    
    if (startHour < 9 || endHour > 20 || (endHour === 20 && endMinute > 0)) {
        showAlert('Booking times must be between 9:00 AM and 8:00 PM', 'error');
        return;
    }
    
    try {
        const response = await fetch('../../backend/api/bookings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        // First check if the response is OK
        if (!response.ok) {
            const errorData = await response.json();
            console.error('Server error:', errorData);
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(`Booking created successfully! Reference: ${result.reference}`, 'success');
            $('#bookingForm')[0].reset();
            hideStationInfo();
            loadUserBookings();
            loadUserStats();
            
            // Reset time pickers
            startTimePicker.clear();
            endTimePicker.clear();
            
            // Switch to bookings tab
            $('.tab-button[data-tab="bookings"]').click();
        } else {
            const errorMessage = result.message || 'Unknown error occurred while creating booking';
            console.error('Booking error:', result);
            showAlert(errorMessage, 'error');
        }
    } catch (error) {
        const errorMessage = error.message || 'An unexpected error occurred while creating the booking';
        console.error('Booking creation error:', error);
        showAlert(errorMessage, 'error');
    }
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertColors = {
        'success': 'bg-green-100 border-green-400 text-green-700',
        'error': 'bg-red-100 border-red-400 text-red-700',
        'info': 'bg-blue-100 border-blue-400 text-blue-700',
        'warning': 'bg-yellow-100 border-yellow-400 text-yellow-700'
    };
    
    const alertHtml = `
        <div class="fixed top-4 right-4 z-50 ${alertColors[type]} border px-4 py-3 rounded max-w-sm" style="z-index: 9999;">
            <div class="flex justify-between items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        $('.fixed.top-4.right-4').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// Payment Modal Functions
function showPaymentModal() {
    // Validate form first
    const startTimeValue = startTimePicker.selectedDates[0];
    const endTimeValue = endTimePicker.selectedDates[0];
    
    if (!$('#stationSelect').val() || !$('#bookingDate').val() || !startTimeValue || !endTimeValue) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }
    
    if (endTimeValue <= startTimeValue) {
        showAlert('End time must be after start time', 'error');
        return;
    }
    
    // Check for time conflicts with unavailable slots
    const startTime = startTimeValue.getHours().toString().padStart(2, '0') + ':' + 
                     startTimeValue.getMinutes().toString().padStart(2, '0') + ':00';
    const endTime = endTimeValue.getHours().toString().padStart(2, '0') + ':' + 
                   endTimeValue.getMinutes().toString().padStart(2, '0') + ':00';
    
    if (window.unavailableSlots && hasTimeConflict(startTime, endTime, window.unavailableSlots)) {
        showAlert('Selected time conflicts with unavailable slots. Please choose a different time.', 'error');
        return;
    }
    
    // Business hours validation (9 AM to 8 PM)
    const startHour = startTimeValue.getHours();
    const endHour = endTimeValue.getHours();
    const endMinute = endTimeValue.getMinutes();
    
    if (startHour < 9 || endHour > 20 || (endHour === 20 && endMinute > 0)) {
        showAlert('Booking times must be between 9:00 AM and 8:00 PM', 'error');
        return;
    }
    
    // Populate payment modal with booking details
    const selectedStationText = $('#stationSelect option:selected').text();
    const bookingDate = new Date($('#bookingDate').val()).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const startTimeText = startTimeValue.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    const endTimeText = endTimeValue.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    // Calculate duration and amount
    const durationMs = endTimeValue - startTimeValue;
    const durationHours = durationMs / (1000 * 60 * 60);
    const hourlyRate = selectedStation ? parseFloat(selectedStation.hourly_rate) : 0;
    const totalAmount = durationHours * hourlyRate;
    
    // Update modal content
    $('#paymentStation').text(selectedStationText);
    $('#paymentDate').text(bookingDate);
    $('#paymentTime').text(`${startTimeText} - ${endTimeText}`);
    $('#paymentDuration').text(`${durationHours.toFixed(1)} hours`);
    $('#paymentAmount').text(`LKR ${totalAmount.toFixed(2)}`);
    
    // Show modal
    $('#paymentModal').removeClass('hidden');
    
    // Focus on card number field
    setTimeout(() => {
        $('#cardNumber').focus();
    }, 300);
}

// Close payment modal
function closePaymentModal() {
    $('#paymentModal').addClass('hidden');
    // Clear form
    $('#paymentForm')[0].reset();
}

// Format card number input
function formatCardNumber(input) {
    // Remove all non-digit characters
    let value = input.replace(/\D/g, '');
    
    // Add spaces every 4 digits
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    
    return value;
}

// Format expiry date input
function formatExpiryDate(input) {
    // Remove all non-digit characters
    let value = input.replace(/\D/g, '');
    
    // Add slash after 2 digits
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    
    return value;
}

// Initialize payment modal event handlers
$(document).ready(function() {
    // Close modal handlers
    $('#closePaymentModal, #cancelPayment').click(function() {
        closePaymentModal();
    });
    
    // Click outside modal to close
    $('#paymentModal').click(function(e) {
        if (e.target === this) {
            closePaymentModal();
        }
    });
    
    // Format card number input
    $('#cardNumber').on('input', function() {
        const formatted = formatCardNumber($(this).val());
        $(this).val(formatted);
    });
    
    // Format expiry date input
    $('#expiryDate').on('input', function() {
        const formatted = formatExpiryDate($(this).val());
        $(this).val(formatted);
    });
    
    // Only allow numbers for CVV
    $('#cvv').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, ''));
    });
    
    // Payment form submission
    $('#paymentForm').submit(function(e) {
        e.preventDefault();
        processPayment();
    });
});

// Process payment and submit booking
async function processPayment() {
    // Validate payment form
    const cardNumber = $('#cardNumber').val().replace(/\s/g, '');
    const cardHolder = $('#cardHolder').val().trim();
    const expiryDate = $('#expiryDate').val();
    const cvv = $('#cvv').val();
    
    if (!cardNumber || cardNumber.length < 13) {
        showAlert('Please enter a valid card number', 'error');
        return;
    }
    
    if (!cardHolder) {
        showAlert('Please enter the cardholder name', 'error');
        return;
    }
    
    if (!expiryDate || expiryDate.length < 5) {
        showAlert('Please enter a valid expiry date', 'error');
        return;
    }
    
    if (!cvv || cvv.length < 3) {
        showAlert('Please enter a valid CVV', 'error');
        return;
    }
    
    // Show processing state
    $('#paymentButtonText').text('Processing...');
    $('#processPayment').prop('disabled', true);
    
    // Simulate payment processing delay
    setTimeout(async () => {
        try {
            // Submit the actual booking
            await submitBooking();
            
            // Close payment modal
            closePaymentModal();
            
        } catch (error) {
            console.error('Payment processing error:', error);
            showAlert('Payment processing failed. Please try again.', 'error');
        } finally {
            // Reset button state
            $('#paymentButtonText').text('Process Payment');
            $('#processPayment').prop('disabled', false);
        }
    }, 2000); // 2 second delay to simulate payment processing
}
