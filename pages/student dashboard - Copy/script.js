// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all interactive features
    initializeSidebar();
    initializeNotifications();
    initializeProfileDropdown();
    initializeSearch();
    initializeCharts();
});

// Sidebar functionality
function initializeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Create mobile menu toggle button
    createMobileMenuToggle();
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('open');
        }
    });
}

// Create mobile menu toggle button
function createMobileMenuToggle() {
    const navbar = document.querySelector('.navbar');
    const sidebar = document.querySelector('.sidebar');
    
    // Create toggle button
    const toggleButton = document.createElement('button');
    toggleButton.className = 'mobile-menu-toggle';
    toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
    toggleButton.style.cssText = `
        display: none;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 18px;
        margin-right: 15px;
    `;
    
    // Insert toggle button at the beginning of navbar
    navbar.insertBefore(toggleButton, navbar.firstChild);
    
    // Toggle sidebar on mobile
    toggleButton.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });
    
    // Show/hide toggle button based on screen size
    function checkScreenSize() {
        if (window.innerWidth <= 768) {
            toggleButton.style.display = 'block';
        } else {
            toggleButton.style.display = 'none';
            sidebar.classList.remove('open');
        }
    }
    
    // Initial check and resize listener
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !toggleButton.contains(e.target) &&
            sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
        }
    });
}

// Notification functionality
function initializeNotifications() {
    const notificationIcons = document.querySelectorAll('.notification-icon');
    
    notificationIcons.forEach(icon => {
        const popup = icon.querySelector('.notification-popup');
        
        // Enhanced hover behavior
        let hoverTimeout;
        
        icon.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            popup.style.opacity = '1';
            popup.style.visibility = 'visible';
            popup.style.transform = 'translateY(0)';
        });
        
        icon.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(() => {
                popup.style.opacity = '0';
                popup.style.visibility = 'hidden';
                popup.style.transform = 'translateY(-10px)';
            }, 300);
        });
        
        // Keep popup open when hovering over it
        popup.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
        });
        
        popup.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(() => {
                popup.style.opacity = '0';
                popup.style.visibility = 'hidden';
                popup.style.transform = 'translateY(-10px)';
            }, 300);
        });
        
        // Click to toggle popup on mobile
        icon.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const isVisible = popup.style.opacity === '1';
                
                // Hide all other popups
                document.querySelectorAll('.notification-popup').forEach(p => {
                    if (p !== popup) {
                        p.style.opacity = '0';
                        p.style.visibility = 'hidden';
                        p.style.transform = 'translateY(-10px)';
                    }
                });
                
                // Toggle current popup
                if (isVisible) {
                    popup.style.opacity = '0';
                    popup.style.visibility = 'hidden';
                    popup.style.transform = 'translateY(-10px)';
                } else {
                    popup.style.opacity = '1';
                    popup.style.visibility = 'visible';
                    popup.style.transform = 'translateY(0)';
                }
            }
        });
    });
    
    // Close popups when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notification-icon')) {
            document.querySelectorAll('.notification-popup').forEach(popup => {
                popup.style.opacity = '0';
                popup.style.visibility = 'hidden';
                popup.style.transform = 'translateY(-10px)';
            });
        }
    });
    
    // Simulate new notifications
    simulateNotifications();
}

// Profile dropdown functionality
function initializeProfileDropdown() {
    const profileSection = document.querySelector('.profile-section');
    const dropdown = document.querySelector('.profile-dropdown');
    
    // Enhanced hover behavior
    let hoverTimeout;
    
    profileSection.addEventListener('mouseenter', function() {
        clearTimeout(hoverTimeout);
        dropdown.style.opacity = '1';
        dropdown.style.visibility = 'visible';
        dropdown.style.transform = 'translateY(0)';
    });
    
    profileSection.addEventListener('mouseleave', function() {
        hoverTimeout = setTimeout(() => {
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.transform = 'translateY(-10px)';
        }, 300);
    });
    
    // Click to toggle on mobile
    profileSection.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            const isVisible = dropdown.style.opacity === '1';
            
            if (isVisible) {
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
                dropdown.style.transform = 'translateY(-10px)';
            } else {
                dropdown.style.opacity = '1';
                dropdown.style.visibility = 'visible';
                dropdown.style.transform = 'translateY(0)';
            }
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileSection.contains(e.target)) {
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.transform = 'translateY(-10px)';
        }
    });
    
    // Handle dropdown item clicks
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.textContent.trim().toLowerCase();
            
            if (action === 'logout') {
                handleLogout();
            } else if (action === 'settings') {
                window.location.href = 'settings.html';
            }
        });
    });
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    
    searchInput.addEventListener('focus', function() {
        this.style.background = 'rgba(255, 255, 255, 0.3)';
        this.style.boxShadow = '0 0 20px rgba(255, 255, 255, 0.2)';
    });
    
    searchInput.addEventListener('blur', function() {
        this.style.background = 'rgba(255, 255, 255, 0.2)';
        this.style.boxShadow = 'none';
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleSearch(this.value);
        }
    });
}

// Simulate new notifications
function simulateNotifications() {
    // Simulate a new message notification after 5 seconds
    setTimeout(() => {
        const messageBadge = document.querySelector('.message-icon').parentElement.querySelector('.notification-badge');
        const currentCount = parseInt(messageBadge.textContent);
        messageBadge.textContent = currentCount + 1;
        
        // Add a flash effect
        messageBadge.style.animation = 'none';
        setTimeout(() => {
            messageBadge.style.animation = 'pulse 2s infinite';
        }, 10);
        
        // Show a brief notification
        showBriefNotification('New message received!');
    }, 5000);
    
    // Simulate a new bell notification after 10 seconds
    setTimeout(() => {
        const bellBadge = document.querySelector('.bell-icon').parentElement.querySelector('.notification-badge');
        const currentCount = parseInt(bellBadge.textContent);
        bellBadge.textContent = currentCount + 1;
        
        // Add a flash effect
        bellBadge.style.animation = 'none';
        setTimeout(() => {
            bellBadge.style.animation = 'pulse 2s infinite';
        }, 10);
        
        // Show a brief notification
        showBriefNotification('New notification received!');
    }, 10000);
}

// Show brief notification
function showBriefNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 90px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        font-weight: 500;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Handle search
function handleSearch(query) {
    if (query.trim()) {
        showBriefNotification(`Searching for: ${query}`);
        // Here you would implement actual search functionality
        console.log('Searching for:', query);
    }
}

// Handle logout
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        showBriefNotification('Logging out...');
        // Here you would implement actual logout functionality
        console.log('Logging out...');
    }
}

// Add smooth scrolling for better UX
document.documentElement.style.scrollBehavior = 'smooth';

// Add loading animation for cards
function animateCards() {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Initialize card animations when page loads
window.addEventListener('load', function() {
    animateCards();
    initializeCharts();
});

// Initialize charts
function initializeCharts() {
    // Performance Chart (Bar Chart)
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
                    borderRadius: 8
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
    
    // Distribution Chart (Pie Chart)
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
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
}

// Add CSS for additional styling
const additionalCSS = `
    .status.passed {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status.failed {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .course-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    
    .course-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.9em;
        color: #666;
        background: rgba(0, 0, 0, 0.05);
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    .course-code {
        font-weight: bold;
        color: #007bff !important;
    }
    
    .marks-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }
    
    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .summary-card:nth-child(1) .summary-icon {
        background: #d4edda;
        color: #155724;
    }
    
    .summary-card:nth-child(2) .summary-icon {
        background: #f8d7da;
        color: #721c24;
    }
    
    .summary-card:nth-child(3) .summary-icon {
        background: #fff3cd;
        color: #856404;
    }
    
    .summary-card:nth-child(4) .summary-icon {
        background: #cce5ff;
        color: #004085;
    }
    
    .summary-content h3 {
        margin: 0;
        font-size: 24px;
        font-weight: bold;
    }
    
    .summary-content p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
`;

// Inject additional CSS
const style = document.createElement('style');
style.textContent = additionalCSS;
document.head.appendChild(style);

