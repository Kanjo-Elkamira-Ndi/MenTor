document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile menu toggle
    const mobileMenuIcon = document.querySelector('.mobile-menu-icon');
    const sidebar = document.querySelector('.sidebar');
    const profileSection = document.querySelector('.profile-section');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    const notificationIcons = document.querySelectorAll('.notification-icon');

    mobileMenuIcon.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !mobileMenuIcon.contains(e.target) && 
            sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
        }
    });

    // Profile dropdown toggle
    profileSection.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        }
    });

    profileSection.addEventListener('mouseenter', function() {
        if (window.innerWidth > 768) {
            dropdownMenu.classList.add('show');
        }
    });

    profileSection.addEventListener('mouseleave', function() {
        if (window.innerWidth > 768) {
            setTimeout(() => {
                if (!profileSection.matches(':hover') && !dropdownMenu.matches(':hover')) {
                    dropdownMenu.classList.remove('show');
                }
            }, 300);
        }
    });

    // Notification popup toggle
    notificationIcons.forEach(icon => {
        const popup = icon.querySelector('.notification-popup');
        icon.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                popup.classList.toggle('show');
            }
        });

        icon.addEventListener('mouseenter', function() {
            if (window.innerWidth > 768) {
                popup.classList.add('show');
            }
        });

        icon.addEventListener('mouseleave', function() {
            if (window.innerWidth > 768) {
                setTimeout(() => {
                    if (!icon.matches(':hover') && !popup.matches(':hover')) {
                        popup.classList.remove('show');
                    }
                }, 300);
            }
        });
    });

    // Handle dropdown item clicks
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.textContent.trim().toLowerCase();
            if (action === 'logout') {
                if (confirm('Are you sure you want to logout?')) {
                    console.log('Logging out...');
                    // Implement logout functionality here
                }
            } else {
                window.location.href = this.href;
            }
            sidebar.classList.remove('open');
            dropdownMenu.classList.remove('show');
        });
    });

    // Handle sidebar nav item clicks
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('open');
            }
        });
    });

    // Responsive adjustments
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('open');
            dropdownMenu.classList.remove('show');
            document.querySelectorAll('.notification-popup').forEach(popup => {
                popup.classList.remove('show');
            });
        }
    });

    // Initialize charts (for dashboard.html)
    const performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx && !performanceCtx.chart) {
        performanceCtx.chart = new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: ['Android Programming', 'Data Structures', 'Database Systems', 'English Communication', 'Mathematics'],
                datasets: [{
                    label: 'Scores',
                    data: [92, 85, 88, 78, 65],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
                    ],
                    borderColor: [
                        'rgba(102, 126, 234, 1)',
                        'rgba(118, 75, 162, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 6
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
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Distribution Chart (for dashboard.html)
    const distributionCtx = document.getElementById('distributionChart');
    if (distributionCtx && !distributionCtx.chart) {
        distributionCtx.chart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Departmental Courses', 'General Courses'],
                datasets: [{
                    data: [3, 2],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(102, 126, 234, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // Simulate notifications
    setTimeout(() => {
        const messageBadge = document.querySelector('.message-icon')?.parentElement.querySelector('.notification-badge');
        if (messageBadge) {
            const currentCount = parseInt(messageBadge.textContent);
            messageBadge.textContent = currentCount + 1;
            messageBadge.style.animation = 'none';
            setTimeout(() => {
                messageBadge.style.animation = 'pulse 2s infinite';
            }, 10);
            showBriefNotification('New message received!');
        }
    }, 5000);

    setTimeout(() => {
        const bellBadge = document.querySelector('.bell-icon')?.parentElement.querySelector('.notification-badge');
        if (bellBadge) {
            const currentCount = parseInt(bellBadge.textContent);
            bellBadge.textContent = currentCount + 1;
            bellBadge.style.animation = 'none';
            setTimeout(() => {
                bellBadge.style.animation = 'pulse 2s infinite';
            }, 10);
            showBriefNotification('New notification received!');
        }
    }, 10000);

    function showBriefNotification(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 10px;
            background: #4CAF50;
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            font-weight: 500;
            font-size: 12px;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});