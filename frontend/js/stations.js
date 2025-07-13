// Gaming Stations Management JavaScript
let stationsTable;
let isEditing = false;

// Initialize when page loads
$(document).ready(function() {
    initializeDataTable();
    loadStations();
    initializeModal();
});

// Initialize DataTable
function initializeDataTable() {
    stationsTable = $('#stationsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']], // Sort by station name
        columns: [
            { data: 'station_name', title: 'Station Name' },
            { data: 'station_type', title: 'Type' },
            { 
                data: 'hourly_rate',
                title: 'Hourly Rate',
                render: function(data) {
                    return '$' + parseFloat(data).toFixed(2);
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
                    return `<span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColors[data]}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            { 
                data: 'description',
                title: 'Description',
                render: function(data) {
                    return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : 'No description';
                }
            },
            { 
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex space-x-2">
                            <button onclick="editStation(${row.id})" class="text-blue-600 hover:text-blue-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteStation(${row.id})" class="text-red-600 hover:text-red-800" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });
}

// Load stations
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

// Initialize modal
function initializeModal() {
    $('#addStationBtn').click(() => openModal());
    $('#closeModal, #cancelBtn').click(() => closeModal());
    $('#stationForm').submit(function(e) {
        e.preventDefault();
        submitStation();
    });
    
    // Close modal when clicking outside
    $('#stationModal').click(function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
}

// Open modal
function openModal(station = null) {
    if (station) {
        // Edit mode
        isEditing = true;
        $('#modalTitle').text('Edit Gaming Station');
        $('#stationId').val(station.id);
        $('#stationName').val(station.station_name);
        $('#stationType').val(station.station_type);
        $('#hourlyRate').val(station.hourly_rate);
        $('#status').val(station.status);
        $('#description').val(station.description);
    } else {
        // Add mode
        isEditing = false;
        $('#modalTitle').text('Add Gaming Station');
        $('#stationForm')[0].reset();
        $('#stationId').val('');
    }
    
    $('#stationModal').removeClass('hidden');
}

// Close modal
function closeModal() {
    $('#stationModal').addClass('hidden');
    $('#stationForm')[0].reset();
    isEditing = false;
}

// Submit station (create or update)
async function submitStation() {
    const formData = {
        station_name: $('#stationName').val(),
        station_type: $('#stationType').val(),
        hourly_rate: $('#hourlyRate').val(),
        status: $('#status').val(),
        description: $('#description').val()
    };
    
    // Validation
    if (!formData.station_name || !formData.station_type || !formData.hourly_rate) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }
    
    try {
        const url = isEditing ? 
            `../../backend/api/stations.php?id=${$('#stationId').val()}` : 
            '../../backend/api/stations.php';
        
        const method = isEditing ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(
                isEditing ? 'Station updated successfully!' : 'Station created successfully!', 
                'success'
            );
            closeModal();
            loadStations();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Error saving station', 'error');
        console.error('Error:', error);
    }
}

// Edit station
function editStation(id) {
    const row = stationsTable.rows().data().toArray().find(station => station.id == id);
    if (row) {
        openModal(row);
    }
}

// Delete station
async function deleteStation(id) {
    if (!confirm('Are you sure you want to delete this gaming station?')) {
        return;
    }
    
    try {
        const response = await fetch(`../../backend/api/stations.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Station deleted successfully!', 'success');
            loadStations();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Error deleting station', 'error');
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
        $('.fixed.top-4.right-4').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}
