<?php
// admin/index.php
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit();
}

// Get admin info
$admin_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NqobileQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 60px;
            --primary: #00b8a9;
            --primary-dark: #009688;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .sidebar-header p {
            margin: 5px 0 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .sidebar-menu-item:hover,
        .sidebar-menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }
        
        .sidebar-menu-item i {
            width: 20px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }
        
        .top-bar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info span {
            color: var(--dark);
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }
        
        .logout-btn:hover {
            background: #c82333;
            color: white;
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        /* Tables */
        .data-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .data-table th {
            background: var(--light);
            padding: 15px;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .data-table tr:hover {
            background: var(--light);
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        /* Modal */
        .modal-content {
            border-radius: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
                margin-right: 15px;
                font-size: 1.5rem;
                cursor: pointer;
            }
        }
        
        .menu-toggle {
            display: none;
        }
        
        /* Badges */
        .badge-pending { background: #ffc107; color: #000; }
        .badge-processing { background: #17a2b8; color: #fff; }
        .badge-completed { background: #28a745; color: #fff; }
        .badge-cancelled { background: #dc3545; color: #fff; }
        .badge-delivered { background: #28a745; color: #fff; }
        .badge-placed { background: #007bff; color: #fff; }
        
        /* Loading */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-hospital-user"></i> NqobileQ</h3>
            <p>Admin Dashboard</p>
        </div>
        <div class="sidebar-menu">
            <a class="sidebar-menu-item active" data-page="dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a class="sidebar-menu-item" data-page="bookings">
                <i class="fas fa-calendar-check"></i>
                <span>Service Bookings</span>
            </a>
            <a class="sidebar-menu-item" data-page="packages">
                <i class="fas fa-box"></i>
                <span>Package Bookings</span>
            </a>
            <a class="sidebar-menu-item" data-page="inquiries">
                <i class="fas fa-envelope"></i>
                <span>Inquiries</span>
            </a>
            <a class="sidebar-menu-item" data-page="testimonials">
                <i class="fas fa-star"></i>
                <span>Testimonials</span>
            </a>
            <a class="sidebar-menu-item" data-page="users">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div style="display: flex; align-items: center;">
                <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
                <h2 class="page-title" id="pageTitle">Dashboard</h2>
            </div>
            <div class="user-info">
                <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($admin_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="content-area" id="contentArea">
            <!-- Dynamic content will be loaded here -->
            <div class="text-center py-5">
                <div class="loading"></div>
                <p class="mt-3">Loading...</p>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingModalBody">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Testimonial Details Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Testimonial Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="testimonialModalBody">
                    <!-- Dynamic content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="approveTestimonial">Approve</button>
                    <button type="button" class="btn btn-danger" id="rejectTestimonial">Reject</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inquiry Details Modal -->
    <div class="modal fade" id="inquiryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="inquiryModalBody">
                    <!-- Dynamic content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="markInquiryRead">Mark as Read</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // API Base URL
        const API_BASE = '../admin-api/';
        
        // Current page
        let currentPage = 'dashboard';
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadPage('dashboard');
            
            // Menu toggle for mobile
            document.getElementById('menuToggle').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
            
            // Sidebar menu clicks
            document.querySelectorAll('.sidebar-menu-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    document.querySelectorAll('.sidebar-menu-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    loadPage(page);
                });
            });
        });
        
        // Load page content
        async function loadPage(page) {
            currentPage = page;
            const titles = {
                dashboard: 'Dashboard',
                bookings: 'Service Bookings',
                packages: 'Package Bookings',
                inquiries: 'Customer Inquiries',
                testimonials: 'Testimonials',
                users: 'User Management'
            };
            document.getElementById('pageTitle').innerText = titles[page] || 'Dashboard';
            
            const contentArea = document.getElementById('contentArea');
            contentArea.innerHTML = '<div class="text-center py-5"><div class="loading"></div><p class="mt-3">Loading...</p></div>';
            
            switch(page) {
                case 'dashboard':
                    await loadDashboard();
                    break;
                case 'bookings':
                    await loadBookings();
                    break;
                case 'packages':
                    await loadPackageBookings();
                    break;
                case 'inquiries':
                    await loadInquiries();
                    break;
                case 'testimonials':
                    await loadTestimonials();
                    break;
                case 'users':
                    await loadUsers();
                    break;
            }
        }
        
        // Load Dashboard
        async function loadDashboard() {
            try {
                const response = await fetch(`${API_BASE}dashboard-stats.php`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    const stats = data.data;
                    const content = `
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="stat-value">${stats.total_bookings || 0}</div>
                                            <div class="stat-label">Total Bookings</div>
                                        </div>
                                        <div class="stat-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="stat-value">${stats.total_packages || 0}</div>
                                            <div class="stat-label">Package Bookings</div>
                                        </div>
                                        <div class="stat-icon">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="stat-value">${stats.total_inquiries || 0}</div>
                                            <div class="stat-label">New Inquiries</div>
                                        </div>
                                        <div class="stat-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="stat-value">${stats.total_testimonials || 0}</div>
                                            <div class="stat-label">Testimonials</div>
                                        </div>
                                        <div class="stat-icon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="stat-value">${stats.total_users || 0}</div>
                                            <div class="stat-label">Registered Users</div>
                                        </div>
                                        <div class="stat-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="data-table p-3">
                                    <h5>Recent Bookings</h5>
                                    <div id="recentBookings"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="data-table p-3">
                                    <h5>Recent Inquiries</h5>
                                    <div id="recentInquiries"></div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('contentArea').innerHTML = content;
                    
                    // Load recent bookings
                    if (stats.recent_bookings && stats.recent_bookings.length > 0) {
                        let html = '<div class="list-group">';
                        stats.recent_bookings.forEach(booking => {
                            html += `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>${escapeHtml(booking.name)}</strong><br>
                                            <small>${booking.service_type}</small>
                                        </div>
                                        <span class="badge bg-${booking.status === 'pending' ? 'warning' : 'success'}">${booking.status}</span>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        document.getElementById('recentBookings').innerHTML = html;
                    } else {
                        document.getElementById('recentBookings').innerHTML = '<p class="text-muted">No recent bookings</p>';
                    }
                    
                    // Load recent inquiries
                    if (stats.recent_inquiries && stats.recent_inquiries.length > 0) {
                        let html = '<div class="list-group">';
                        stats.recent_inquiries.forEach(inquiry => {
                            html += `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>${escapeHtml(inquiry.name)}</strong><br>
                                            <small>${inquiry.email}</small>
                                        </div>
                                        <span class="badge bg-${inquiry.status === 'new' ? 'warning' : 'secondary'}">${inquiry.status}</span>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        document.getElementById('recentInquiries').innerHTML = html;
                    } else {
                        document.getElementById('recentInquiries').innerHTML = '<p class="text-muted">No recent inquiries</p>';
                    }
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
                document.getElementById('contentArea').innerHTML = '<div class="alert alert-danger">Failed to load dashboard data</div>';
            }
        }
        
        // Load Service Bookings
        async function loadBookings() {
            try {
                const response = await fetch(`${API_BASE}get-bookings.php`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    let html = `
                        <div class="data-table">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.bookings.forEach(booking => {
                        html += `
                            <tr>
                                <td>${booking.id}</td>
                                <td><strong>${escapeHtml(booking.name)}</strong></td>
                                <td>${escapeHtml(booking.email)}</td>
                                <td>${booking.phone || 'N/A'}</td>
                                <td>${escapeHtml(booking.service_type)}</td>
                                <td>${booking.preferred_date || 'N/A'}</td>
                                <td>
                                    <span class="badge bg-${booking.status === 'pending' ? 'warning' : 'success'}">
                                        ${booking.status}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewBooking(${booking.id})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="updateBookingStatus(${booking.id}, 'confirmed')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="updateBookingStatus(${booking.id}, 'cancelled')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    document.getElementById('contentArea').innerHTML = html;
                } else {
                    document.getElementById('contentArea').innerHTML = '<div class="alert alert-info">No bookings found</div>';
                }
            } catch (error) {
                console.error('Error loading bookings:', error);
                document.getElementById('contentArea').innerHTML = '<div class="alert alert-danger">Failed to load bookings</div>';
            }
        }
        
        // Load Package Bookings
        async function loadPackageBookings() {
            try {
                const response = await fetch(`${API_BASE}get-packages.php`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    let html = `
                        <div class="data-table">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Package</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.packages.forEach(pkg => {
                        html += `
                            <tr>
                                <td>${pkg.id}</td>
                                <td><strong>${escapeHtml(pkg.name)}</strong></td>
                                <td>${escapeHtml(pkg.email)}</td>
                                <td>${pkg.phone || 'N/A'}</td>
                                <td><span class="badge bg-primary">${escapeHtml(pkg.package_name)}</span></td>
                                <td>${new Date(pkg.created_at).toLocaleDateString()}</td>
                                <td>
                                    <span class="badge bg-${pkg.status === 'pending' ? 'warning' : 'success'}">
                                        ${pkg.status}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="updatePackageStatus(${pkg.id}, 'confirmed')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="updatePackageStatus(${pkg.id}, 'cancelled')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    document.getElementById('contentArea').innerHTML = html;
                } else {
                    document.getElementById('contentArea').innerHTML = '<div class="alert alert-info">No package bookings found</div>';
                }
            } catch (error) {
                console.error('Error loading packages:', error);
                document.getElementById('contentArea').innerHTML = '<div class="alert alert-danger">Failed to load packages</div>';
            }
        }
        
        // Load Inquiries
        async function loadInquiries() {
            try {
                const response = await fetch(`${API_BASE}get-inquiries.php`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    let html = `
                        <div class="data-table">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.inquiries.forEach(inquiry => {
                        const messagePreview = inquiry.message.length > 50 ? 
                            inquiry.message.substring(0, 50) + '...' : inquiry.message;
                        
                        html += `
                            <tr>
                                <td>${inquiry.id}</td>
                                <td><strong>${escapeHtml(inquiry.name)}</strong></td>
                                <td>${escapeHtml(inquiry.email)}</td>
                                <td>${inquiry.phone || 'N/A'}</td>
                                <td title="${escapeHtml(inquiry.message)}">${escapeHtml(messagePreview)}</td>
                                <td>${new Date(inquiry.created_at).toLocaleDateString()}</td>
                                <td>
                                    <span class="badge bg-${inquiry.status === 'new' ? 'warning' : 'secondary'}">
                                        ${inquiry.status}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewInquiry(${inquiry.id})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="markInquiryRead(${inquiry.id})">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    document.getElementById('contentArea').innerHTML = html;
                } else {
                    document.getElementById('contentArea').innerHTML = '<div class="alert alert-info">No inquiries found</div>';
                }
            } catch (error) {
                console.error('Error loading inquiries:', error);
                document.getElementById('contentArea').innerHTML = '<div class="alert alert-danger">Failed to load inquiries</div>';
            }
        }
        
        // Load Testimonials
        async function loadTestimonials() {
            try {
                const response = await fetch(`${API_BASE}get-testimonials.php`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    let html = `
                        <div class="data-table">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.testimonials.forEach(testimonial => {
                        const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
                        const messagePreview = testimonial.message.length > 50 ? 
                            testimonial.message.substring(0, 50) + '...' : testimonial.message;
                        
                        html += `
                            <tr>
                                <td>${testimonial.id}</td>
                                <td><strong>${escapeHtml(testimonial.name)}</strong></td>
                                <td>${escapeHtml(testimonial.email)}</td>
                                <td><span class="text-warning">${stars}</span></td>
                                <td title="${escapeHtml(testimonial.message)}">${escapeHtml(messagePreview)}</td>
                                <td>${new Date(testimonial.created_at).toLocaleDateString()}</td>
                                <td>
                                    <span class="badge bg-${testimonial.status === 'pending' ? 'warning' : 'success'}">
                                        ${testimonial.status}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewTestimonial(${testimonial.id})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${testimonial.status === 'pending' ? `
                                        <button class="btn btn-sm btn-success" onclick="approveTestimonial(${testimonial.id})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectTestimonial(${testimonial.id})">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    document.getElementById('contentArea').innerHTML = html;
                } else {
                    document.getElementById('contentArea').innerHTML = '<div class="alert alert-info">No testimonials found</div>';
                }
            } catch (error) {
                console.error('Error loading testimonials:', error);
                document.getElementById('contentArea').innerHTML = '<div class="alert alert-danger">Failed to load testimonials</div>';
            }
        }
        
        // Load Users
        async function loadUsers() {
            try {
                const response = await fetch(`${API_BASE}get-users.php`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    let html = `
                        <div class="data-table">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.users.forEach(user => {
                        html += `
                            <tr>
                                <td>${user.id}</td>
                                <td><strong>${escapeHtml(user.full_name)}</strong></td>
                                <td>${escapeHtml(user.email)}</td>
                                <td>${user.phone || 'N/A'}</td>
                                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    document.getElementById('contentArea').innerHTML = html;
                } else {
                    document.getElementById('contentArea').innerHTML = '<div class="alert alert-info">No users found</div>';
                }
            } catch (error) {
                console.error('Error loading users:', error);
                document.getElementById('contentArea').innerHTML = '<div class="alert alert-danger">Failed to load users</div>';
            }
        }
        
        // View Booking Details
        async function viewBooking(id) {
            try {
                const response = await fetch(`${API_BASE}get-booking.php?id=${id}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    const booking = data.booking;
                    const modalBody = document.getElementById('bookingModalBody');
                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <label class="fw-bold">Name:</label>
                            <p>${escapeHtml(booking.name)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Email:</label>
                            <p>${escapeHtml(booking.email)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Phone:</label>
                            <p>${booking.phone || 'N/A'}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Service Type:</label>
                            <p>${escapeHtml(booking.service_type)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Preferred Date:</label>
                            <p>${booking.preferred_date || 'N/A'}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Message:</label>
                            <p>${escapeHtml(booking.message) || 'No message'}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Status:</label>
                            <p><span class="badge bg-${booking.status === 'pending' ? 'warning' : 'success'}">${booking.status}</span></p>
                        </div>
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error viewing booking:', error);
                alert('Failed to load booking details');
            }
        }
        
        // View Inquiry Details
        async function viewInquiry(id) {
            try {
                const response = await fetch(`${API_BASE}get-inquiry.php?id=${id}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    const inquiry = data.inquiry;
                    const modalBody = document.getElementById('inquiryModalBody');
                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <label class="fw-bold">Name:</label>
                            <p>${escapeHtml(inquiry.name)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Email:</label>
                            <p>${escapeHtml(inquiry.email)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Phone:</label>
                            <p>${inquiry.phone || 'N/A'}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Message:</label>
                            <p>${escapeHtml(inquiry.message)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Date:</label>
                            <p>${new Date(inquiry.created_at).toLocaleString()}</p>
                        </div>
                    `;
                    
                    document.getElementById('markInquiryRead').onclick = () => markInquiryRead(id);
                    const modal = new bootstrap.Modal(document.getElementById('inquiryModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error viewing inquiry:', error);
                alert('Failed to load inquiry details');
            }
        }
        
        // View Testimonial Details
        async function viewTestimonial(id) {
            try {
                const response = await fetch(`${API_BASE}get-testimonial.php?id=${id}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    const testimonial = data.testimonial;
                    const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
                    const modalBody = document.getElementById('testimonialModalBody');
                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <label class="fw-bold">Name:</label>
                            <p>${escapeHtml(testimonial.name)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Email:</label>
                            <p>${escapeHtml(testimonial.email)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Rating:</label>
                            <p><span class="text-warning fs-4">${stars}</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Review:</label>
                            <p>${escapeHtml(testimonial.message)}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Date:</label>
                            <p>${new Date(testimonial.created_at).toLocaleString()}</p>
                        </div>
                    `;
                    
                    document.getElementById('approveTestimonial').onclick = () => approveTestimonial(id);
                    document.getElementById('rejectTestimonial').onclick = () => rejectTestimonial(id);
                    const modal = new bootstrap.Modal(document.getElementById('testimonialModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error viewing testimonial:', error);
                alert('Failed to load testimonial details');
            }
        }
        
        // Update Booking Status
        async function updateBookingStatus(id, status) {
            if (!confirm(`Are you sure you want to mark this booking as ${status}?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                
                const response = await fetch(`${API_BASE}update-booking-status.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    alert('Booking status updated successfully');
                    loadBookings();
                } else {
                    alert('Failed to update booking status');
                }
            } catch (error) {
                console.error('Error updating booking:', error);
                alert('Failed to update booking status');
            }
        }
        
        // Update Package Status
        async function updatePackageStatus(id, status) {
            if (!confirm(`Are you sure you want to mark this package booking as ${status}?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                
                const response = await fetch(`${API_BASE}update-package-status.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    alert('Package status updated successfully');
                    loadPackageBookings();
                } else {
                    alert('Failed to update package status');
                }
            } catch (error) {
                console.error('Error updating package:', error);
                alert('Failed to update package status');
            }
        }
        
        // Mark Inquiry as Read
        async function markInquiryRead(id) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch(`${API_BASE}mark-inquiry-read.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    alert('Inquiry marked as read');
                    loadInquiries();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('inquiryModal'));
                    if (modal) modal.hide();
                } else {
                    alert('Failed to mark inquiry as read');
                }
            } catch (error) {
                console.error('Error marking inquiry:', error);
                alert('Failed to mark inquiry as read');
            }
        }
        
        // Approve Testimonial
        async function approveTestimonial(id) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', 'approved');
                
                const response = await fetch(`${API_BASE}update-testimonial-status.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    alert('Testimonial approved successfully');
                    loadTestimonials();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('testimonialModal'));
                    if (modal) modal.hide();
                } else {
                    alert('Failed to approve testimonial');
                }
            } catch (error) {
                console.error('Error approving testimonial:', error);
                alert('Failed to approve testimonial');
            }
        }
        
        // Reject Testimonial
        async function rejectTestimonial(id) {
            if (!confirm('Are you sure you want to reject this testimonial?')) return;
            
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', 'rejected');
                
                const response = await fetch(`${API_BASE}update-testimonial-status.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    alert('Testimonial rejected');
                    loadTestimonials();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('testimonialModal'));
                    if (modal) modal.hide();
                } else {
                    alert('Failed to reject testimonial');
                }
            } catch (error) {
                console.error('Error rejecting testimonial:', error);
                alert('Failed to reject testimonial');
            }
        }
        
        // Delete User
        async function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
            
            try {
                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch(`${API_BASE}delete-user.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    alert('User deleted successfully');
                    loadUsers();
                } else {
                    alert('Failed to delete user');
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                alert('Failed to delete user');
            }
        }
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>