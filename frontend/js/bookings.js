// Bookings Management JavaScript
let bookingsTable;
let allBookings = [];
let currentBooking = null;

// Initialize when page loads
$(document).ready(function() {
    initializeDataTable();
    loadBookings();
    loadStations();
    initializeFilters();
    initializeModal();
});

// Initialize DataTable
function initializeDataTable() {
    bookingsTable = $('#bookingsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[3, 'desc']], // Sort by date descending
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
            emptyTable: "🎮 No gaming sessions found in the arena database!",
            zeroRecords: "🔍 No matching gaming sessions found. Adjust your filters!",
            loadingRecords: "⚡ Loading gaming sessions...",
            processing: "🎮 Processing arena data..."
        },
        dom: '<"flex flex-col md:flex-row md:justify-between md:items-center mb-6"<"mb-4 md:mb-0"l><"mb-4 md:mb-0"f>>rtip',
        columns: [
            { data: 'booking_reference', title: '<i class="fas fa-hashtag mr-2"></i>Reference' },
            { 
                data: null,
                title: '<i class="fas fa-user mr-2"></i>User',
                render: function(data, type, row) {
                    return `<div><span class="font-medium text-cyan-300">${row.user_email}</span></div>`;
                }
            },
            { 
                data: null,
                title: '<i class="fas fa-gamepad mr-2"></i>Station',
                render: function(data, type, row) {
                    return `<div><span class="font-medium text-purple-300">${row.station_name}</span><br><small class="text-gray-400">${row.station_type}</small></div>`;
                }
            },
            { 
                data: 'booking_date',
                title: '<i class="fas fa-calendar mr-2"></i>Date',
                render: function(data) {
                    return `<span class="text-cyan-300">${new Date(data).toLocaleDateString('en-US', {
                        weekday: 'short',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    })}</span>`;
                }
            },
            { 
                data: null,
                title: '<i class="fas fa-clock mr-2"></i>Time',
                render: function(data, type, row) {
                    const startTime = new Date(`2000-01-01 ${row.start_time}`).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    const endTime = new Date(`2000-01-01 ${row.end_time}`).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    return `<span class="text-green-300">${startTime}</span><br><small class="text-gray-400">to ${endTime}</small>`;
                }
            },
            { 
                data: 'total_hours',
                title: '<i class="fas fa-hourglass mr-2"></i>Duration',
                render: function(data) {
                    return `<span class="text-yellow-300 font-semibold">${parseFloat(data).toFixed(1)}h</span>`;
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
                        'pending': 'admin-status-badge admin-status-pending',
                        'confirmed': 'admin-status-badge admin-status-confirmed',
                        'completed': 'admin-status-badge admin-status-completed',
                        'cancelled': 'admin-status-badge admin-status-cancelled'
                    };
                    return `<span class="${statusStyles[data]}">${data.toUpperCase()}</span>`;
                }
            },
            { 
                data: null,
                title: '<i class="fas fa-cog mr-2"></i>Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex space-x-1">
                            <button onclick="viewBooking(${row.id})" class="admin-action-btn admin-action-view" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${row.status === 'pending' ? `
                                <button onclick="updateBookingStatus(${row.id}, 'confirmed')" class="text-green-600 hover:text-green-800" title="Confirm">
                                    <i class="fas fa-check"></i>
                                </button>
                            ` : ''}
                            ${['pending', 'confirmed'].includes(row.status) ? `
                                <button onclick="updateBookingStatus(${row.id}, 'cancelled')" class="text-red-600 hover:text-red-800" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            ` : ''}
                        </div>
                    `;
                }
            }
        ]
    });
}

// Load bookings
async function loadBookings() {
    try {
        const response = await fetch('../../backend/api/bookings.php?admin=1');
        const data = await response.json();
        
        if (data.success) {
            allBookings = data.data;
            bookingsTable.clear().rows.add(allBookings).draw();
        } else {
            showAlert('Error loading bookings: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading bookings', 'error');
        console.error('Error:', error);
    }
}

// Load stations for filter
async function loadStations() {
    try {
        const response = await fetch('../../backend/api/stations.php');
        const data = await response.json();
        
        if (data.success) {
            const stationFilter = $('#stationFilter');
            data.data.forEach(station => {
                stationFilter.append(`<option value="${station.id}">${station.station_name}</option>`);
            });
        }
    } catch (error) {
        console.error('Error loading stations:', error);
    }
}

// Initialize filters
function initializeFilters() {
    $('#statusFilter').change(function() {
        const status = $(this).val();
        filterBookings();
    });
    
    $('#stationFilter').change(function() {
        const stationId = $(this).val();
        filterBookings();
    });
}

// Filter bookings
function filterBookings() {
    const statusFilter = $('#statusFilter').val();
    const stationFilter = $('#stationFilter').val();
    
    let filteredBookings = allBookings;
    
    if (statusFilter) {
        filteredBookings = filteredBookings.filter(booking => booking.status === statusFilter);
    }
    
    if (stationFilter) {
        filteredBookings = filteredBookings.filter(booking => booking.station_id == stationFilter);
    }
    
    bookingsTable.clear().rows.add(filteredBookings).draw();
}

// Initialize modal
function initializeModal() {
    $('#closeModal, #closeDetailsBtn').click(() => closeModal());
    $('#confirmBtn').click(() => updateBookingStatus(currentBooking.id, 'confirmed'));
    $('#cancelBookingBtn').click(() => updateBookingStatus(currentBooking.id, 'cancelled'));
    
    // Close modal when clicking outside
    $('#bookingModal').click(function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
}

// View booking details
function viewBooking(id) {
    currentBooking = allBookings.find(booking => booking.id == id);
    if (!currentBooking) return;
    
    const bookingDate = new Date(currentBooking.booking_date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const startTime = new Date(`2000-01-01 ${currentBooking.start_time}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    const endTime = new Date(`2000-01-01 ${currentBooking.end_time}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    const createdAt = new Date(currentBooking.created_at).toLocaleString('en-US');
    
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    
    $('#bookingDetails').html(`
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><strong>Reference:</strong> ${currentBooking.booking_reference}</div>
            <div><strong>Status:</strong> <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColors[currentBooking.status]}">${currentBooking.status.charAt(0).toUpperCase() + currentBooking.status.slice(1)}</span></div>
            <div><strong>User:</strong> ${currentBooking.user_email}</div>
            <div><strong>Station:</strong> ${currentBooking.station_name}</div>
            <div><strong>Type:</strong> ${currentBooking.station_type}</div>
            <div><strong>Date:</strong> ${bookingDate}</div>
            <div><strong>Time:</strong> ${startTime} - ${endTime}</div>
            <div><strong>Duration:</strong> ${parseFloat(currentBooking.total_hours).toFixed(1)} hours</div>
            <div><strong>Amount:</strong> LKR ${parseFloat(currentBooking.total_amount).toFixed(2)}</div>
            <div><strong>Booked On:</strong> ${createdAt}</div>
        </div>
        ${currentBooking.notes ? `<div class="mt-4"><strong>Notes:</strong><br><p class="mt-1 p-2 bg-gray-50 rounded">${currentBooking.notes}</p></div>` : ''}
    `);
    
    // Show/hide action buttons based on status
    if (currentBooking.status === 'pending') {
        $('#confirmBtn').removeClass('hidden');
        $('#cancelBookingBtn').removeClass('hidden');
    } else if (currentBooking.status === 'confirmed') {
        $('#confirmBtn').addClass('hidden');
        $('#cancelBookingBtn').removeClass('hidden');
    } else {
        $('#confirmBtn').addClass('hidden');
        $('#cancelBookingBtn').addClass('hidden');
    }
    
    $('#bookingModal').removeClass('hidden');
}

// Update booking status
async function updateBookingStatus(id, status) {
    if (status === 'cancelled' && !confirm('Are you sure you want to cancel this booking?')) {
        return;
    }
    
    try {
        const response = await fetch(`../../backend/api/bookings.php?id=${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: status })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(`Booking ${status} successfully!`, 'success');
            closeModal();
            loadBookings();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Error updating booking status', 'error');
        console.error('Error:', error);
    }
}

// Close modal
function closeModal() {
    $('#bookingModal').addClass('hidden');
    currentBooking = null;
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
