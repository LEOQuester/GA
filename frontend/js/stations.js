// Gaming Stations Management JavaScript
let stationsTable;
let isEditing = false;

// Test function to ensure JavaScript is working
window.editStation = function(id) {
    console.log('🔥 EDIT STATION CALLED! ID:', id);
    editStationActual(id);
};

// Initialize when page loads
$(document).ready(function() {
    console.log('🚀 stations.js loaded successfully');
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
        language: {
            search: "🔍 Search Gaming Stations:",
            lengthMenu: "Show _MENU_ stations per page",
            info: "Showing _START_ to _END_ of _TOTAL_ gaming stations",
            infoEmpty: "No gaming stations found",
            infoFiltered: "(filtered from _MAX_ total stations)",
            paginate: {
                first: "⏮️ First",
                last: "⏭️ Last",
                next: "▶️ Next",
                previous: "◀️ Previous"
            },
            emptyTable: "🎮 No gaming stations in the arena yet!",
            zeroRecords: "🔍 No matching gaming stations found. Try a different search!",
            loadingRecords: "⚡ Loading gaming stations...",
            processing: "🎮 Processing station data..."
        },
        dom: '<"flex flex-col md:flex-row md:justify-between md:items-center mb-6"<"mb-4 md:mb-0"l><"mb-4 md:mb-0"f>>rtip',
        columns: [
            { data: 'station_name', title: '<i class="fas fa-gamepad mr-2"></i>Station Name' },
            { 
                data: 'station_type', 
                title: '<i class="fas fa-tag mr-2"></i>Type',
                render: function(data) {
                    return `<span class="text-purple-300 font-semibold">${data}</span>`;
                }
            },
            { 
                data: 'hourly_rate',
                title: '<i class="fas fa-coins mr-2"></i>Hourly Rate',
                render: function(data) {
                    return `<span class="text-green-400 font-bold">LKR ${parseFloat(data).toFixed(2)}</span>`;
                }
            },
            { 
                data: 'status',
                title: '<i class="fas fa-power-off mr-2"></i>Status',
                render: function(data) {
                    const statusStyles = {
                        'active': 'admin-status-badge admin-status-active',
                        'maintenance': 'admin-status-badge admin-status-pending',
                        'inactive': 'admin-status-badge admin-status-inactive'
                    };
                    return `<span class="${statusStyles[data]}">${data.toUpperCase()}</span>`;
                }
            },
            { 
                data: 'description',
                title: '<i class="fas fa-info-circle mr-2"></i>Description',
                render: function(data) {
                    const desc = data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : 'No description';
                    return `<span class="text-cyan-300">${desc}</span>`;
                }
            },
            { 
                data: null,
                title: '<i class="fas fa-cog mr-2"></i>Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex space-x-1">
                            <button onclick="console.log('Edit button clicked for ID:', ${row.id}); editStation(${row.id})" class="admin-action-btn admin-action-edit" title="Edit">
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
    console.log('=== OPEN MODAL DEBUG ===');
    console.log('Station parameter:', station);
    
    if (station) {
        // Edit mode
        isEditing = true;
        $('#modalTitle').text('Edit Gaming Station');
        $('#stationId').val(station.id);
        $('#stationName').val(station.station_name);
        $('#hourlyRate').val(station.hourly_rate);
        $('#description').val(station.description);
        
        // Handle station type with debugging
        console.log('Setting station type to:', station.station_type);
        $('#stationType').val(station.station_type);
        console.log('Station type field value after setting:', $('#stationType').val());
        
        // If the value didn't set, try to find a matching option
        if ($('#stationType').val() !== station.station_type) {
            console.log('Station type value did not match exactly. Looking for alternatives...');
            const typeOptions = $('#stationType option').map(function() {
                return $(this).val();
            }).get();
            console.log('Available type options:', typeOptions);
            
            // Try to find a case-insensitive match
            const matchingOption = typeOptions.find(option => 
                option.toLowerCase() === station.station_type.toLowerCase()
            );
            
            if (matchingOption) {
                console.log('Found matching option:', matchingOption);
                $('#stationType').val(matchingOption);
            } else {
                console.warn('No matching station type option found for:', station.station_type);
            }
        }
        
        // Handle status with debugging
        console.log('Setting status to:', station.status);
        $('#status').val(station.status);
        console.log('Status field value after setting:', $('#status').val());
        
        console.log('Set to edit mode - ID set to:', station.id);
        console.log('Form field #stationId now has value:', $('#stationId').val());
    } else {
        // Add mode
        isEditing = false;
        $('#modalTitle').text('Add Gaming Station');
        $('#stationForm')[0].reset();
        $('#stationId').val('');
        
        console.log('Set to add mode - cleared form');
    }
    
    console.log('isEditing flag is now:', isEditing);
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
        hourly_rate: parseFloat($('#hourlyRate').val()) || 0,
        status: $('#status').val() || 'active',
        description: $('#description').val() || ''
    };
    
    // Always add id for editing, ensure it's a valid number
    if (isEditing) {
        const stationId = $('#stationId').val();
        formData.id = stationId ? parseInt(stationId) : 0;
        
        // Extra safety check
        if (!formData.id || formData.id <= 0) {
            showAlert('Error: Station ID is missing. Please try refreshing and editing again.', 'error');
            return;
        }
    }
    
    // Much more relaxed validation - only check basics
    if (!formData.station_name) {
        showAlert('Station name is required', 'error');
        return;
    }
    
    try {
        const url = '../../backend/api/stations.php';
        const method = isEditing ? 'PATCH' : 'POST';
        
        // Debug logging
        console.log('=== FRONTEND DEBUG START ===');
        console.log('Is editing mode:', isEditing);
        console.log('Station ID from form:', $('#stationId').val());
        console.log('Station ID type:', typeof $('#stationId').val());
        console.log('Submitting station data:', formData);
        console.log('Method:', method);
        console.log('URL:', url);
        console.log('JSON to send:', JSON.stringify(formData));
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        const responseText = await response.text();
        console.log('Raw response text:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
            console.log('Parsed JSON result:', result);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.log('Unable to parse response as JSON');
            showAlert('Server returned invalid response', 'error');
            return;
        }
        
        // Log debug information if available
        if (result.debug) {
            console.group('🔧 Backend Debug Information');
            console.log('Raw Input Received:', result.debug.raw_input);
            console.log('Decoded JSON:', result.debug.decoded_input);
            console.log('JSON Decode Error:', result.debug.json_decode_error);
            
            if (result.debug.parsed_values) {
                console.group('📋 Parsed Values');
                Object.entries(result.debug.parsed_values).forEach(([key, value]) => {
                    console.log(`${key}:`, value, `(${typeof value})`);
                });
                console.groupEnd();
            }
            
            if (result.debug.validation_checks) {
                console.group('✅ Validation Checks');
                Object.entries(result.debug.validation_checks).forEach(([check, passed]) => {
                    console.log(`${check}:`, passed ? '✅ PASS' : '❌ FAIL');
                });
                console.groupEnd();
            }
            console.groupEnd();
        } else {
            console.warn('No debug information received from backend');
        }
        
        if (result.success) {
            showAlert(
                isEditing ? 'Station updated successfully!' : 'Station created successfully!', 
                'success'
            );
            closeModal();
            loadStations();
        } else {
            console.error('Server returned error:', result.message);
            showAlert(result.message, 'error');
        }
    } catch (error) {
        console.error('=== FETCH ERROR ===');
        console.error('Error details:', error);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        showAlert('Network error occurred', 'error');
    }
}

// Edit station (actual implementation)
async function editStationActual(id) {
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
                
                // Use our local openModal function
                console.log('Using stations.php modal');
                isEditing = true;
                openModal(station);
                
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
