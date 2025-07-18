// Arena Unavailable Slots Management JavaScript
let slotsTable;
let isEditing = false;
let datePicker, startTimePicker, endTimePicker;

// Initialize when page loads
$(document).ready(function() {
    initializeDataTable();
    initializeDateTimePickers();
    loadSlots();
    initializeForm();
});

// Initialize DataTable
function initializeDataTable() {
    slotsTable = $('#slotsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc'], [1, 'asc']], // Sort by date, then time
        language: {
            search: "🔍 Search Arena Periods:",
            lengthMenu: "Show _MENU_ periods per page",
            info: "Showing _START_ to _END_ of _TOTAL_ unavailable periods",
            infoEmpty: "No unavailable periods found",
            infoFiltered: "(filtered from _MAX_ total periods)",
            paginate: {
                first: "⏮️ First",
                last: "⏭️ Last",
                next: "▶️ Next",
                previous: "◀️ Previous"
            },
            emptyTable: "🎮 No arena maintenance periods scheduled!",
            zeroRecords: "🔍 No matching periods found. Try a different search!",
            loadingRecords: "⚡ Loading maintenance periods...",
            processing: "🎮 Processing arena schedule..."
        },
        dom: '<"flex flex-col md:flex-row md:justify-between md:items-center mb-6"<"mb-4 md:mb-0"l><"mb-4 md:mb-0"f>>rtip',
        columns: [
            { 
                data: 'unavailable_date',
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
                title: '<i class="fas fa-clock mr-2"></i>Time Period',
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
                data: 'reason',
                title: '<i class="fas fa-exclamation-triangle mr-2"></i>Reason',
                render: function(data) {
                    return `<span class="text-gray-300">${data}</span>`;
                }
            },
            { 
                data: 'created_at',
                title: '<i class="fas fa-calendar-plus mr-2"></i>Created',
                render: function(data) {
                    return `<span class="text-purple-300">${new Date(data).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit'
                    })}</span>`;
                }
            },
            {
                data: null,
                title: '<i class="fas fa-cog mr-2"></i>Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex space-x-1">
                            <button onclick="editSlot(${row.id})" class="admin-action-btn admin-action-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteSlot(${row.id})" class="admin-action-btn admin-action-delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });
}

// Initialize date and time pickers
function initializeDateTimePickers() {
    // Date picker - only allow future dates
    datePicker = flatpickr("#slotDate", {
        dateFormat: "Y-m-d",
        minDate: "today",
        maxDate: new Date().fp_incr(30) // 30 days from today
    });

    // Time pickers
    startTimePicker = flatpickr("#startTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i:S",
        time_24hr: true,
        defaultHour: 9,
        defaultMinute: 0,
        minuteIncrement: 30,
        minTime: "09:00",
        maxTime: "20:00"
    });

    endTimePicker = flatpickr("#endTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i:S",
        time_24hr: true,
        defaultHour: 10,
        defaultMinute: 0,
        minuteIncrement: 30,
        minTime: "09:00",
        maxTime: "20:00"
    });
}

// Load all arena unavailable slots
function loadSlots() {
    fetch('../../backend/api/unavailable_slots.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                slotsTable.clear();
                slotsTable.rows.add(data.data);
                slotsTable.draw();
            } else {
                showNotification('Error loading slots: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load unavailable slots', 'error');
        });
}

// Initialize form submission
function initializeForm() {
    $('#slotForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            unavailable_date: $('#slotDate').val(),
            start_time: $('#startTime').val(),
            end_time: $('#endTime').val(),
            reason: $('#reason').val()
        };

        // Validation
        if (!formData.unavailable_date || !formData.start_time || !formData.end_time || !formData.reason) {
            showNotification('Please fill in all fields', 'error');
            return;
        }

        // Validate time range
        if (formData.start_time >= formData.end_time) {
            showNotification('End time must be after start time', 'error');
            return;
        }

        if (isEditing) {
            updateSlot(formData);
        } else {
            createSlot(formData);
        }
    });
}

// Create new arena unavailable slot
function createSlot(formData) {
    fetch('../../backend/api/unavailable_slots.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Arena unavailable period created successfully', 'success');
            resetForm();
            loadSlots();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to create unavailable period', 'error');
    });
}

// Edit slot
function editSlot(id) {
    const rowData = slotsTable.rows().data().toArray().find(row => row.id == id);
    if (!rowData) return;

    // Fill form with existing data
    $('#slotId').val(rowData.id);
    $('#slotDate').val(rowData.unavailable_date);
    $('#startTime').val(rowData.start_time);
    $('#endTime').val(rowData.end_time);
    $('#reason').val(rowData.reason);

    // Update form UI
    isEditing = true;
    $('#submitText').text('Update Period');
    
    // Scroll to form
    $('html, body').animate({
        scrollTop: $('#slotForm').offset().top - 100
    }, 500);
}

// Update existing slot
function updateSlot(formData) {
    const id = $('#slotId').val();
    
    fetch(`../../backend/api/unavailable_slots.php?id=${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Arena unavailable period updated successfully', 'success');
            resetForm();
            loadSlots();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update unavailable period', 'error');
    });
}

// Delete slot
function deleteSlot(id) {
    if (!confirm('Are you sure you want to delete this arena unavailable period?')) return;

    fetch(`../../backend/api/unavailable_slots.php?id=${id}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Arena unavailable period deleted successfully', 'success');
            loadSlots();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete unavailable period', 'error');
    });
}

// Reset form
function resetForm() {
    $('#slotForm')[0].reset();
    $('#slotId').val('');
    isEditing = false;
    $('#submitText').text('Add Period');
    
    // Reset date/time pickers
    if (datePicker) datePicker.clear();
    if (startTimePicker) startTimePicker.clear();
    if (endTimePicker) endTimePicker.clear();
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    $('.notification').remove();
    
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    const notification = $(`
        <div class="notification fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300">
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info'} mr-2"></i>
                <span>${message}</span>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    // Animate in
    setTimeout(() => {
        notification.removeClass('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.addClass('translate-x-full');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}
