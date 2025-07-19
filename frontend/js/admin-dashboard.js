// Admin Dashboard JavaScript
let stationsTable, bookingsTable;
let isEditing = false;

// Initialize when page loads
$(document).ready(function() {
    initializeTabs();
    initializeDataTables();
    loadDashboardStats();
    loadStations();
    loadBookings();
    initializeModals();
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
    stationsTable = $('#stationsTable').DataTable({
        responsive: true,
        pageLength: 10,
        columns: [
            { data: 'id', title: 'ID' },
            { data: 'station_name', title: 'Station Name' },
            { data: 'station_type', title: 'Type' },
            { data: 'description', title: 'Description' },
            { 
                data: 'hourly_rate', 
                title: 'Hourly Rate',
                render: function(data) {
                    return 'LKR ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                data: 'status', 
                title: 'Status',
                render: function(data) {
                    const statusColors = {
                        'active': 'bg-green-100 text-green-800',
                        'maintenance': 'bg-yellow-100 text-yellow-800',
                        'inactive': 'bg-red-100 text-red-800'
                    };
                    return `<span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColors[data] || statusColors.inactive}">${data}</span>`;
                }
            },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button onclick="editStation(${row.id})" class="text-blue-600 hover:text-blue-800 mr-2">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deleteStation(${row.id})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    `;
                }
            }
        ]
    });

    bookingsTable = $('#bookingsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[3, 'desc']], // Sort by date descending
        columns: [
            { data: 'booking_reference', title: 'Reference' },
            { 
                data: null,
                title: 'User',
                render: function(data, type, row) {
                    return `${row.full_name} (${row.username})`;
                }
            },
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
                    return 'LKR ' + parseFloat(data).toFixed(2);
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
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    if (row.status === 'pending') {
                        return `
                            <button onclick="updateBookingStatus(${row.id}, 'confirmed')" class="text-green-600 hover:text-green-800 mr-2 text-xs">
                                <i class="fas fa-check"></i> Confirm
                            </button>
                            <button onclick="updateBookingStatus(${row.id}, 'cancelled')" class="text-red-600 hover:text-red-800 text-xs">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        `;
                    } else if (row.status === 'confirmed') {
                        return `
                            <button onclick="updateBookingStatus(${row.id}, 'completed')" class="text-blue-600 hover:text-blue-800 text-xs">
                                <i class="fas fa-flag-checkered"></i> Complete
                            </button>
                        `;
                    }
                    return '<span class="text-gray-400 text-xs">No actions</span>';
                }
            }
        ]
    });
}

// Load dashboard statistics
async function loadDashboardStats() {
    try {
        // Load stations count
        const stationsResponse = await fetch('../../backend/api/stations.php');
        const stationsData = await stationsResponse.json();
        if (stationsData.success) {
            $('#totalStations').text(stationsData.data.length);
        }

        // Load bookings data for stats
        const bookingsResponse = await fetch('../../backend/api/bookings.php');
        const bookingsData = await bookingsResponse.json();
        if (bookingsData.success) {
            const bookings = bookingsData.data;
            $('#totalBookings').text(bookings.length);
            
            const pendingCount = bookings.filter(b => b.status === 'pending').length;
            $('#pendingBookings').text(pendingCount);
            
            // Calculate today's revenue
            const today = new Date().toISOString().split('T')[0];
            const todayBookings = bookings.filter(b => b.booking_date === today && b.status !== 'cancelled');
            const todayRevenue = todayBookings.reduce((sum, b) => sum + parseFloat(b.total_amount), 0);
            $('#todayRevenue').text('LKR ' + todayRevenue.toFixed(2));
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

// Load stations data
async function loadStations() {
    try {
        const response = await fetch('../../backend/api/stations.php');
        const data = await response.json();
        
        if (data.success) {
            stationsTable.clear().rows.add(data.data).draw();
        } else {
            showAlert('Error loading stations: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading stations', 'error');
        console.error('Error:', error);
    }
}

// Load bookings data
async function loadBookings() {
    try {
        const response = await fetch('../../backend/api/bookings.php');
        const data = await response.json();
        
        if (data.success) {
            bookingsTable.clear().rows.add(data.data).draw();
        } else {
            showAlert('Error loading bookings: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading bookings', 'error');
        console.error('Error:', error);
    }
}

// Initialize modal functionality
function initializeModals() {
    // Add station button
    $('#addStationBtn').click(function() {
        openStationModal();
    });

    // Close modal buttons
    $('#closeModal, #cancelBtn').click(function() {
        closeStationModal();
    });

    // Station form submission
    $('#stationForm').submit(function(e) {
        e.preventDefault();
        saveStation();
    });

    // Close modal when clicking outside
    $('#stationModal').click(function(e) {
        if (e.target === this) {
            closeStationModal();
        }
    });
}

// Open station modal
function openStationModal(stationData = null) {
    isEditing = stationData !== null;
    
    if (isEditing) {
        $('#modalTitle').text('Edit Gaming Station');
        $('#stationId').val(stationData.id);
        $('#stationName').val(stationData.station_name);
        $('#stationType').val(stationData.station_type);
        $('#stationDescription').val(stationData.description);
        $('#hourlyRate').val(stationData.hourly_rate);
        $('#stationStatus').val(stationData.status);
        $('#statusField').removeClass('hidden');
    } else {
        $('#modalTitle').text('Add Gaming Station');
        $('#stationForm')[0].reset();
        $('#stationId').val('');
        $('#statusField').addClass('hidden');
    }
    
    $('#stationModal').removeClass('hidden');
}

// Close station modal
function closeStationModal() {
    $('#stationModal').addClass('hidden');
    $('#stationForm')[0].reset();
    isEditing = false;
}

// Save station (create or update)
async function saveStation() {
    const formData = new FormData($('#stationForm')[0]);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const url = '../../backend/api/stations.php';
        const method = isEditing ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            closeStationModal();
            loadStations();
            loadDashboardStats();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Error saving station', 'error');
        console.error('Error:', error);
    }
}

// Edit station
async function editStation(id) {
    console.log('=== EDIT STATION DEBUG ===');
    console.log('Clicked edit for ID:', id, typeof id);
    
    try {
        const response = await fetch('../../backend/api/stations.php');
        const data = await response.json();
        
        console.log('All stations data:', data.data);
        
        if (data.success) {
            const station = data.data.find(s => s.id == id);
            console.log('Found station object:', station);
            
            if (station) {
                console.log('Station object details:');
                console.log('- ID:', station.id);
                console.log('- Name:', station.station_name);
                console.log('- Type:', station.station_type);
                console.log('- Rate:', station.hourly_rate);
                console.log('- Status:', station.status);
                console.log('- Description:', station.description);
                
                // Check if we're on the stations page or dashboard
                if (typeof openModal !== 'undefined') {
                    // We're on stations.php - use the modal there
                    console.log('Using stations.php modal');
                    isEditing = true;
                    openModal(station);
                } else if (typeof openStationModal !== 'undefined') {
                    // We're on dashboard - use dashboard modal
                    console.log('Using dashboard modal');
                    openStationModal(station);
                } else {
                    console.error('No modal function available');
                    showAlert('Error: Modal function not found', 'error');
                }
                
                // Verify the form was populated
                setTimeout(() => {
                    console.log('After modal open - stationId field value:', $('#stationId').val());
                    console.log('After modal open - isEditing flag:', isEditing);
                    console.log('After modal open - station type field value:', $('#stationType').val());
                }, 100);
            } else {
                console.error('Station not found for ID:', id);
                showAlert('Station not found', 'error');
            }
        } else {
            console.error('Failed to load stations:', data.message);
            showAlert('Error loading station data: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error loading station data:', error);
        showAlert('Error loading station data', 'error');
    }
}

// Delete station
async function deleteStation(id) {
    if (!confirm('Are you sure you want to delete this station? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('../../backend/api/stations.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadStations();
            loadDashboardStats();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Error deleting station', 'error');
        console.error('Error:', error);
    }
}

// Update booking status
async function updateBookingStatus(bookingId, status) {
    const confirmMessages = {
        'confirmed': 'Are you sure you want to confirm this booking?',
        'cancelled': 'Are you sure you want to cancel this booking?',
        'completed': 'Are you sure you want to mark this booking as completed?'
    };
    
    if (!confirm(confirmMessages[status] || 'Are you sure?')) {
        return;
    }
    
    try {
        const response = await fetch('../../backend/api/update_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                booking_id: bookingId, 
                status: status 
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadBookings();
            loadDashboardStats();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Error updating booking status', 'error');
        console.error('Error:', error);
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
        $('.fixed.top-4.right-4').fadeOut(() => {
            $(this).remove();
        });
    }, 5000);
}
