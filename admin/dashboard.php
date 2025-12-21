<?php 
    include "header.php";
?>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Real-time Clock -->
            <div class="real-time-clock reveal">
                <div class="clock-container">
                    <i class="fas fa-clock clock-icon"></i>
                    <div>
                        <div class="time-display" id="currentTime">Loading...</div>
                        <div class="date-display" id="currentDate">Loading...</div>
                    </div>
                </div>
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i> Owerri, Nigeria
                </div>
            </div>

            <div class="header">
                <h2>Dashboard Overview</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search...">
                    </div>
                    <div class="notification-user-container">
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">5</span>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-dropdown-header">
                                    <h4>Notifications</h4>
                                    <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                                </div>
                                <ul class="notification-list" id="notificationList">
                                    <!-- Notifications will be loaded here -->
                                </ul>
                            </div>
                        </div>
                        <div class="user-menu" id="userMenuBtn">
                            <i class="fas fa-user-circle"></i>
                            <!-- User Menu Dropdown -->
                            <div class="user-menu-dropdown" id="userMenuDropdown">
                                <div class="user-menu-header">
                                    <div class="user-menu-avatar"><?php echo $user_initials; ?></div>
                                    <div class="user-menu-info">
                                        <h4><?php echo htmlspecialchars($username); ?></h4>
                                        <p>Super Admin</p>
                                    </div>
                                </div>
                                <ul class="user-menu-items">
                                    <li class="user-menu-item" onclick="window.location.href='admin-settings.php'">
                                        <i class="fas fa-user-cog"></i>
                                        <span>Profile Settings</span>
                                    </li>
                                    <li class="user-menu-item" onclick="window.location.href='admin-settings.php'">
                                        <i class="fas fa-cog"></i>
                                        <span>Account Settings</span>
                                    </li>
                                    <li class="user-menu-item" onclick="window.location.href='admin-logout.php'">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card orders reveal">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['today_orders']; ?></div>
                    <div class="stat-label">Today's Orders</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 12% from yesterday
                    </div>
                </div>

                <div class="stat-card revenue reveal reveal-delay-1">
                    <i class="fa-solid fa-naira-sign"></i>
                    <div class="stat-value">₦<?php echo number_format($dashboard_stats['total_revenue']); ?></div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 8% from last week
                    </div>
                </div>

                <!-- Changed from "Total Customers" to "Active Orders" -->
                <div class="stat-card active-orders reveal reveal-delay-2">
                    <i class="fas fa-clock"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['active_orders']; ?></div>
                    <div class="stat-label">Active Orders</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 2 from yesterday
                    </div>
                </div>

                <div class="stat-card reservations reveal reveal-delay-3">
                    <i class="fas fa-calendar-check"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['today_reservations']; ?></div>
                    <div class="stat-label">Today's Reservations</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 3% from yesterday
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card reveal">
                    <div class="chart-header">
                        <h3>Revenue Overview</h3>
                        <div class="chart-actions">
                            <select id="revenuePeriodSelect">
                                <option value="7">Last 7 Days</option>
                                <option value="30">Last 30 Days</option>
                                <option value="90">Last 3 Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="chart-card reveal reveal-delay-1">
                    <div class="chart-header">
                        <h3>Order Status</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="orderStatusChart"></canvas>
                        <div id="orderStatusLegend" style="display: flex; justify-content: center; gap: 20px; margin-top: 20px; flex-wrap: wrap;"></div>
                    </div>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="activity-section">
                <div class="activity-card reveal">
                    <h3>Recent Activity</h3>
                    <ul class="activity-list" id="activityList">
                        <!-- Activity items will be loaded here -->
                    </ul>
                </div>

                <div class="top-items-card reveal reveal-delay-1">
                    <h3>Top Menu Items</h3>
                    <ul class="top-items-list" id="topItemsList">
                        <!-- Top items will be loaded here -->
                    </ul>
                </div>
            </div>
            <!-- Login History Section -->
            <div class="login-history reveal">
                <h3>Recent Login Activity</h3>
                <table class="login-history-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Device</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($login_history)): ?>
                            <?php foreach ($login_history as $login): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($login['login_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($login['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars($login['city'] . ', ' . $login['country']); ?></td>
                                    <td><?php echo htmlspecialchars($login['device_type'] . ' - ' . $login['browser']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $login['status'] === 'success' ? 'status-success' : 'status-failed'; ?>">
                                            <?php echo ucfirst($login['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No login history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Admin Management Section -->
            <div class="admin-management reveal">
                <div class="admin-management-header">
                    <h3>Admin Management</h3>
                    <button class="add-admin-btn" id="addAdminBtn">
                        <i class="fas fa-plus"></i>
                        Add New Admin
                    </button>
                </div>
                <div class="admins-grid" id="adminsGrid">
                    <!-- Admin cards will be dynamically added here -->
                </div>
            </div>

           <?php include "footer.php";
            ?>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal" id="addAdminModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Admin</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form id="adminForm">
                <div class="form-group">
                    <label for="adminName">Full Name</label>
                    <input type="text" id="adminName" required>
                </div>
                <div class="form-group">
                    <label for="adminEmail">Email Address</label>
                    <input type="email" id="adminEmail" required>
                </div>
                <div class="form-group">
                    <label for="adminRole">Role</label>
                    <select id="adminRole" required>
                        <option value="">Select Role</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Content Manager">Content Manager</option>
                        <option value="Support">Support</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adminPermissions">Permissions</label>
                    <select id="adminPermissions" required>
                        <option value="">Select Permissions</option>
                        <option value="Full Access">Full Access</option>
                        <option value="Limited Access">Limited Access</option>
                        <option value="View Only">View Only</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Logout confirmation function
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        // Real-time Clock Functionality
        function updateClock() {
            const now = new Date();

            // Format time
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'

            // Add leading zeros
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            // Format date
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateString = now.toLocaleDateString('en-US', options);

            // Update the DOM
            document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('currentDate').textContent = dateString;
        }

        // Update the clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Sample admin data
        const admins = [{
                id: 1,
                name: 'Admin Joseph',
                role: 'Super Admin',
                avatar: 'AJ'
            },
            {
                id: 2,
                name: 'Manager David',
                role: 'Manager',
                avatar: 'MD'
            },
            {
                id: 3,
                name: 'Content Sarah',
                role: 'Content Manager',
                avatar: 'CS'
            },
            {
                id: 4,
                name: 'Support Mike',
                role: 'Support',
                avatar: 'SM'
            }
        ];

        // Sample notification data
        const notifications = [
            {
                id: 1,
                title: 'New Order',
                message: 'Order #JP-2848 has been placed',
                time: '2 minutes ago',
                unread: true
            },
            {
                id: 2,
                title: 'Reservation Confirmed',
                message: 'Table reservation for 4 people confirmed',
                time: '15 minutes ago',
                unread: true
            },
            {
                id: 3,
                title: 'Payment Received',
                message: '₦8,500 payment confirmed for Order #JP-2845',
                time: '1 hour ago',
                unread: false
            },
            {
                id: 4,
                title: 'New Review',
                message: 'Customer left a 5-star review',
                time: '3 hours ago',
                unread: false
            },
            {
                id: 5,
                title: 'System Update',
                message: 'Dashboard has been updated to version 2.1',
                time: '1 day ago',
                unread: false
            }
        ];

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const addAdminBtn = document.getElementById('addAdminBtn');
        const addAdminModal = document.getElementById('addAdminModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const adminForm = document.getElementById('adminForm');
        const adminsGrid = document.getElementById('adminsGrid');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllRead');
        const notificationBadge = document.querySelector('.notification-badge');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenuDropdown = document.getElementById('userMenuDropdown');

        // Mobile sidebar toggler functionality
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking on a menu item on mobile
        const menuItems = document.querySelectorAll('.menu-item a');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });

        // Modal functionality
        addAdminBtn.addEventListener('click', function() {
            addAdminModal.style.display = 'flex';
        });

        closeModal.addEventListener('click', function() {
            addAdminModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', function() {
            addAdminModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addAdminModal) {
                addAdminModal.style.display = 'none';
            }
        });

        // Handle form submission
        adminForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('adminName').value;
            const role = document.getElementById('adminRole').value;

            // Generate avatar initials
            const avatar = name.split(' ').map(n => n[0]).join('').toUpperCase();

            // Create new admin object
            const newAdmin = {
                id: admins.length + 1,
                name: name,
                role: role,
                avatar: avatar
            };

            // Add to admins array
            admins.push(newAdmin);

            // Update UI
            renderAdmins();

            // Close modal and reset form
            addAdminModal.style.display = 'none';
            adminForm.reset();

            // Show success message
            alert(`Admin ${name} added successfully!`);
        });

        // Render admins in the grid
        function renderAdmins() {
            adminsGrid.innerHTML = '';

            admins.forEach(admin => {
                const adminCard = document.createElement('div');
                adminCard.className = 'admin-card';
                adminCard.innerHTML = `
                    <div class="admin-card-avatar">${admin.avatar}</div>
                    <div class="admin-card-name">${admin.name}</div>
                    <div class="admin-card-role">${admin.role}</div>
                    <div class="admin-card-actions">
                        <button class="admin-card-btn edit" title="Edit Admin">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="admin-card-btn delete" title="Delete Admin">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                adminsGrid.appendChild(adminCard);
            });
        }

        // User Menu functionality
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('active');
            // Close notification dropdown if open
            notificationDropdown.classList.remove('active');
        });

        // Notification functionality
        function renderNotifications(notificationsData) {
            if (!notificationList) return;
            
            notificationList.innerHTML = '';
            
            if (!notificationsData || notificationsData.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                return;
            }
            
            notificationsData.forEach(notification => {
                const notificationItem = document.createElement('li');
                notificationItem.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
                notificationItem.dataset.id = notification.id;
                
                const timeAgo = getTimeAgo(notification.created_at);
                
                notificationItem.innerHTML = `
                    <div class="notification-dot" style="${!notification.is_read ? 'background: var(--primary)' : 'background: transparent'}"></div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${timeAgo}</div>
                    </div>
                `;
                
                if (!notification.is_read) {
                    notificationItem.addEventListener('click', function() {
                        markAsRead(notification.id);
                    });
                }
                
                notificationList.appendChild(notificationItem);
            });
        }

        function updateNotificationBadge(count) {
            if (notificationBadge) {
                notificationBadge.textContent = count || 0;
                notificationBadge.style.display = (count > 0) ? 'flex' : 'none';
            }
        }

        function loadNotifications() {
            fetch('api/get-notifications.php?limit=10')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderNotifications(data.data);
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        function markAsRead(notificationId) {
            fetch('api/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload notifications to update UI
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        function markAllAsRead() {
            fetch('api/mark-all-notifications-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload notifications to update UI
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }

        // Toggle notification dropdown
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
            // Close user menu dropdown if open
            userMenuDropdown.classList.remove('active');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            // Close notification dropdown
            if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
            
            // Close user menu dropdown
            if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                userMenuDropdown.classList.remove('active');
            }
        });

        // Mark all as read button
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllAsRead();
            });
        }
        
        // Initial notification load
        loadNotifications();

        // Scroll Reveal Functionality
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.reveal');

            for (let i = 0; i < reveals.length; i++) {
                const windowHeight = window.innerHeight;
                const elementTop = reveals[i].getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add('active');
                } else {
                    reveals[i].classList.remove('active');
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Simple animation for stats cards on load
            const statCards = document.querySelectorAll('.stat-card');

            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Set initial state for animation
            statCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            });

            // Initialize admin cards
            renderAdmins();
            
            // Load notifications
            loadNotifications();
            
            // Poll for notifications every 45 seconds
            setInterval(loadNotifications, 45000);

            // Initialize scroll reveal
            window.addEventListener('scroll', revealOnScroll);
            // Trigger once on load to check initial position
            revealOnScroll();
            
            // Initialize charts
            initializeCharts();
            
            // Load activity feed and top items
            loadActivityFeed();
            loadTopMenuItems();
        });

        // Chart.js initialization
        let revenueChart = null;
        let orderStatusChart = null;

        function initializeCharts() {
            // Initialize revenue chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                revenueChart = new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Revenue (₦)',
                            data: [],
                            backgroundColor: 'rgba(33, 150, 243, 0.6)',
                            borderColor: 'rgba(33, 150, 243, 1)',
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
                                    callback: function(value) {
                                        return '₦' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Load initial revenue data
                loadRevenueData(7);
            }

            // Initialize order status chart
            const orderStatusCtx = document.getElementById('orderStatusChart');
            if (orderStatusCtx) {
                orderStatusChart = new Chart(orderStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                'rgba(76, 175, 80, 0.8)',
                                'rgba(255, 152, 0, 0.8)',
                                'rgba(244, 67, 54, 0.8)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                
                // Load order status data
                loadOrderStatusData();
            }

            // Bind revenue period selector
            const periodSelect = document.getElementById('revenuePeriodSelect');
            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
                    const days = parseInt(this.value);
                    loadRevenueData(days);
                });
            }
        }

        function loadRevenueData(days) {
            fetch(`api/get-revenue.php?days=${days}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && revenueChart) {
                        if (data.data && data.data.length > 0) {
                            const labels = data.data.map(item => {
                                const date = new Date(item.date);
                                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                            });
                            const revenues = data.data.map(item => parseFloat(item.revenue));
                            
                            revenueChart.data.labels = labels;
                            revenueChart.data.datasets[0].data = revenues;
                            revenueChart.update();
                        } else {
                            // Handle empty data
                            revenueChart.data.labels = ['No data'];
                            revenueChart.data.datasets[0].data = [0];
                            revenueChart.update();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading revenue data:', error);
                });
        }

        function loadOrderStatusData() {
            fetch('api/get-order-status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && orderStatusChart) {
                        const labels = data.data.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
                        const values = data.data.map(item => item.count);
                        const percentages = data.data.map(item => item.percentage);
                        
                        orderStatusChart.data.labels = labels;
                        orderStatusChart.data.datasets[0].data = values;
                        orderStatusChart.update();
                        
                        // Update legend
                        const legendDiv = document.getElementById('orderStatusLegend');
                        if (legendDiv) {
                            legendDiv.innerHTML = '';
                            data.data.forEach((item, index) => {
                                const colors = ['var(--success)', 'var(--warning)', 'var(--danger)'];
                                const color = colors[index] || 'var(--gray)';
                                const legendItem = document.createElement('div');
                                legendItem.style.display = 'flex';
                                legendItem.style.alignItems = 'center';
                                legendItem.innerHTML = `
                                    <div style="width: 12px; height: 12px; background: ${color}; border-radius: 50%; margin-right: 5px;"></div>
                                    <span>${item.status.charAt(0).toUpperCase() + item.status.slice(1)} (${item.percentage}%)</span>
                                `;
                                legendDiv.appendChild(legendItem);
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading order status data:', error);
                });
        }

        function loadActivityFeed() {
            fetch('api/get-activity-feed.php?limit=5')
                .then(response => response.json())
                .then(data => {
                    const activityList = document.getElementById('activityList');
                    if (!activityList) return;
                    
                    if (data.success && data.data && data.data.length > 0) {
                        activityList.innerHTML = '';
                        data.data.forEach(activity => {
                            const timeAgo = getTimeAgo(activity.timestamp);
                            const iconClass = getActivityIcon(activity.type);
                            const iconBg = getActivityIconBg(activity.type);
                            
                            const item = document.createElement('li');
                            item.className = 'activity-item';
                            item.innerHTML = `
                                <div class="activity-icon ${iconBg}">
                                    <i class="${iconClass}"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>${activity.title}</h4>
                                    <p>${activity.message}</p>
                                </div>
                                <div class="activity-time">${timeAgo}</div>
                            `;
                            activityList.appendChild(item);
                        });
                    } else {
                        // Fallback static data
                        activityList.innerHTML = `
                            <li class="activity-item">
                                <div class="activity-icon order">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>New Order Received</h4>
                                    <p>Order #JP-2847 for 2 people</p>
                                </div>
                                <div class="activity-time">10 min ago</div>
                            </li>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading activity feed:', error);
                });
        }

        function loadTopMenuItems() {
            fetch('api/get-top-menu-items.php')
                .then(response => response.json())
                .then(data => {
                    const topItemsList = document.getElementById('topItemsList');
                    if (!topItemsList) return;
                    
                    if (data.success && data.data && data.data.length > 0) {
                        topItemsList.innerHTML = '';
                        data.data.forEach((item, index) => {
                            const rank = index + 1;
                            const rankClass = rank <= 3 ? `rank-${rank}` : '';
                            const itemEl = document.createElement('li');
                            itemEl.className = 'top-item';
                            itemEl.innerHTML = `
                                <div class="item-rank ${rankClass}">${rank}</div>
                                <div class="item-details">
                                    <h4>${item.item_name}</h4>
                                    <p>Menu item</p>
                                </div>
                                <div class="item-sales">${item.total_quantity} sales</div>
                            `;
                            topItemsList.appendChild(itemEl);
                        });
                    } else {
                        // Fallback static data
                        topItemsList.innerHTML = `
                            <li class="top-item">
                                <div class="item-rank rank-1">1</div>
                                <div class="item-details">
                                    <h4>Ofe Owerri Special</h4>
                                    <p>Traditional Igbo soup</p>
                                </div>
                                <div class="item-sales">142 sales</div>
                            </li>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading top menu items:', error);
                });
        }

        function getTimeAgo(timestamp) {
            const now = Math.floor(Date.now() / 1000);
            const time = Math.floor(new Date(timestamp).getTime() / 1000);
            const diff = now - time;
            
            if (diff < 60) {
                return 'Just now';
            } else if (diff < 3600) {
                const minutes = Math.floor(diff / 60);
                return minutes + ' min ago';
            } else if (diff < 86400) {
                const hours = Math.floor(diff / 3600);
                return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
            } else {
                const days = Math.floor(diff / 86400);
                return days + ' day' + (days > 1 ? 's' : '') + ' ago';
            }
        }

        function getActivityIcon(type) {
            const icons = {
                'order': 'fas fa-shopping-bag',
                'reservation': 'fas fa-calendar-plus',
                'payment': 'fas fa-credit-card',
                'review': 'fas fa-star'
            };
            return icons[type] || 'fas fa-circle';
        }

        function getActivityIconBg(type) {
            const backgrounds = {
                'order': 'order',
                'reservation': 'reservation',
                'payment': 'payment',
                'review': 'review'
            };
            return backgrounds[type] || 'order';
        }
    </script>
</body>
</html>