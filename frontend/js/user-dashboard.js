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
        columns: [
            { data: 'booking_reference', title: 'Reference' },
            { 
                data: null,
                title: 'Station',
                render: function(data, type, row) {
                    return `${row.station_name} (${row.station_type})`;
                }
            },
            { data: 'booking_date', title: 'Date' },
            { 
                data: null,
                title: 'Time',
                render: function(data, type, row) {
                    return `${row.start_time} - ${row.end_time}`;
                }
            },
            { 
                data: 'total_hours',
                title: 'Duration',
                render: function(data) {
                    return data + ' hours';
                }
            },
            { 
                data: 'total_amount',
                title: 'Amount',
                render: function(data) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            { 
                data: 'status',
                title: 'Status',
                render: function(data) {
                    const statusColors = {
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'confirmed': 'bg-blue-100 text-blue-800',
                        'completed': 'bg-green-100 text-green-800',
                        'cancelled': 'bg-red-100 text-red-800'
                    };
                    return `<span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColors[data] || statusColors.pending}">${data}</span>`;
                }
            },
            { 
                data: 'created_at',
                title: 'Booked On',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            }
        ]
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
            $('#totalSpent').text('$' + totalSpent.toFixed(2));
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
            text: `${station.station_name} (${station.station_type}) - $${station.hourly_rate}/hr`
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
        submitBooking();
    });
}

// Show station information
function showStationInfo() {
    if (!selectedStation) return;
    
    $('#stationTypeInfo').text(selectedStation.station_type);
    $('#stationRateInfo').text('$' + parseFloat(selectedStation.hourly_rate).toFixed(2));
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
    if (unavailableSlots && unavailableSlots.length > 0) {
        let availabilityHtml = '<div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">';
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
        
        // Remove existing availability info and add new one
        $('#stationInfo .availability-info').remove();
        $('#stationInfo').append(availabilityHtml);
        $('.availability-info').addClass('availability-info');
    } else {
        // Remove availability info if no conflicts
        $('#stationInfo .availability-info').remove();
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
    $('#summaryAmount').text('$' + totalAmount.toFixed(2));
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
