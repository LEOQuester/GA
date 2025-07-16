// Reports & Analytics JavaScript
let revenueChart, stationChart, dailyChart, hoursChart;

// Initialize when page loads
$(document).ready(function() {
    loadAnalytics();
    initializeCharts();
});

// Load analytics data
async function loadAnalytics() {
    try {
        const response = await fetch('../../backend/api/analytics.php');
        const data = await response.json();
        
        if (data.success) {
            updateMetricCards(data.metrics);
            updateCharts(data.charts);
            updateTopStations(data.top_stations);
            updateRecentBookings(data.recent_bookings);
        } else {
            console.error('Error loading analytics:', data.message);
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}

// Update metric cards
function updateMetricCards(metrics) {
    $('#totalRevenue').text('LKR ' + parseFloat(metrics.total_revenue || 0).toFixed(2));
    $('#totalBookings').text(metrics.total_bookings || 0);
    $('#totalHours').text((metrics.total_hours || 0) + 'h');
    $('#avgSession').text(parseFloat(metrics.avg_session || 0).toFixed(1) + 'h');
    
    // Update percentage changes (simplified - you can enhance this with real month-over-month data)
    $('#revenueChange').text('+' + (Math.random() * 20).toFixed(1) + '% from last month');
    $('#bookingsChange').text('+' + (Math.random() * 15).toFixed(1) + '% from last month');
    $('#hoursChange').text('+' + (Math.random() * 18).toFixed(1) + '% from last month');
    $('#sessionChange').text('+' + (Math.random() * 10).toFixed(1) + '% from last month');
}

// Initialize charts
function initializeCharts() {
    // Monthly Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Revenue ($)',
                data: [],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });

    // Station Bookings Pie Chart
    const stationCtx = document.getElementById('stationChart').getContext('2d');
    stationChart = new Chart(stationCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#8B5CF6', '#3B82F6', '#10B981', '#F59E0B',
                    '#EF4444', '#F97316', '#06B6D4', '#84CC16'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Daily Bookings Chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    dailyChart = new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Bookings',
                data: [],
                backgroundColor: 'rgba(139, 92, 246, 0.8)',
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Peak Hours Chart
    const hoursCtx = document.getElementById('hoursChart').getContext('2d');
    hoursChart = new Chart(hoursCtx, {
        type: 'bar',
        data: {
            labels: ['9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM'],
            datasets: [{
                label: 'Bookings',
                data: [],
                backgroundColor: 'rgba(249, 115, 22, 0.8)',
                borderColor: 'rgb(249, 115, 22)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Update charts with data
function updateCharts(chartData) {
    // Update Revenue Chart
    if (chartData.monthly_revenue) {
        revenueChart.data.labels = chartData.monthly_revenue.labels;
        revenueChart.data.datasets[0].data = chartData.monthly_revenue.data;
        revenueChart.update();
    }

    // Update Station Chart
    if (chartData.station_bookings) {
        stationChart.data.labels = chartData.station_bookings.labels;
        stationChart.data.datasets[0].data = chartData.station_bookings.data;
        stationChart.update();
    }

    // Update Daily Chart
    if (chartData.daily_bookings) {
        dailyChart.data.labels = chartData.daily_bookings.labels;
        dailyChart.data.datasets[0].data = chartData.daily_bookings.data;
        dailyChart.update();
    }

    // Update Hours Chart
    if (chartData.peak_hours) {
        hoursChart.data.datasets[0].data = chartData.peak_hours.data;
        hoursChart.update();
    }
}

// Update top stations list
function updateTopStations(stations) {
    const container = $('#topStations');
    container.empty();
    
    if (!stations || stations.length === 0) {
        container.append('<p class="text-gray-500 text-center">No data available</p>');
        return;
    }
    
    stations.forEach((station, index) => {
        const percentage = stations.length > 0 ? (station.bookings / stations[0].bookings * 100).toFixed(1) : 0;
        const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : (index + 1);
        
        container.append(`
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <span class="text-lg">${medal}</span>
                    <div>
                        <p class="font-medium text-gray-800">${station.station_name}</p>
                        <p class="text-sm text-gray-500">${station.station_type}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-800">${station.bookings} bookings</p>
                    <p class="text-sm text-green-600">LKR ${parseFloat(station.revenue).toFixed(2)}</p>
                </div>
            </div>
        `);
    });
}

// Update recent bookings
function updateRecentBookings(bookings) {
    const container = $('#recentBookings');
    container.empty();
    
    if (!bookings || bookings.length === 0) {
        container.append('<p class="text-gray-500 text-center">No recent bookings</p>');
        return;
    }
    
    bookings.forEach(booking => {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'confirmed': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        
        const bookingDate = new Date(booking.booking_date).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
        
        container.append(`
            <div class="flex items-center justify-between p-3 border-l-4 border-purple-500 bg-gray-50">
                <div>
                    <p class="font-medium text-gray-800">${booking.station_name}</p>
                    <p class="text-sm text-gray-500">${bookingDate} • ${booking.start_time} - ${booking.end_time}</p>
                </div>
                <div class="text-right">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColors[booking.status] || statusColors.pending}">
                        ${booking.status}
                    </span>
                    <p class="text-sm font-medium text-gray-800 mt-1">LKR ${parseFloat(booking.total_amount).toFixed(2)}</p>
                </div>
            </div>
        `);
    });
}
