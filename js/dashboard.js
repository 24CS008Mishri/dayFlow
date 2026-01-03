    // Dashboard Script
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Get user info from localStorage
    const userId = localStorage.getItem('userId');
    const userName = localStorage.getItem('userName');
    const userEmail = localStorage.getItem('userEmail');
    const userRole = localStorage.getItem('userRole');

    // Check if user is logged in
    if (!userId || !userRole) {
        window.location.href = 'index.html';
        return;
    }

    // Update UI with user information
    document.getElementById('userName').textContent = userName || 'User';
    document.getElementById('userEmail').textContent = userEmail || 'user@example.com';
    document.getElementById('welcomeMessage').textContent = `Welcome back, ${userName}! You are logged in as ${userRole === 'admin' ? 'Administrator' : 'Employee'}.`;
    
    // Update profile badge with first letter of name
    const firstLetter = (userName || 'U').charAt(0).toUpperCase();
    document.getElementById('profileBadge').textContent = firstLetter;
    document.getElementById('userAvatarDisplay').textContent = firstLetter;

    // Show/hide sections based on user role
    if (userRole === 'admin') {
        document.getElementById('adminSection').classList.add('show');
        document.getElementById('employeeSection').classList.remove('show');
        
        // Show admin navigation links
        document.getElementById('adminNavLink').style.display = 'block';
        document.getElementById('adminNavLink2').style.display = 'block';
        document.getElementById('employeeNavLink').style.display = 'none';
        document.getElementById('employeeNavLink2').style.display = 'none';
    } else if (userRole === 'employee') {
        document.getElementById('adminSection').classList.remove('show');
        document.getElementById('employeeSection').classList.add('show');
        
        // Show employee navigation links
        document.getElementById('adminNavLink').style.display = 'none';
        document.getElementById('adminNavLink2').style.display = 'none';
        document.getElementById('employeeNavLink').style.display = 'block';
        document.getElementById('employeeNavLink2').style.display = 'block';
    }
}

// Logout function
function logout() {
    // Clear localStorage
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRole');
    localStorage.removeItem('rememberedLoginId');

    // Clear session via PHP
    fetch('php/session_check.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=logout'
    }).then(() => {
        // Redirect to login page
        window.location.href = 'index.html';
    }).catch(error => {
        console.error('Error:', error);
        // Still redirect even if logout request fails
        window.location.href = 'index.html';
    });
}

// Check-in/Check-out modal function
function openCheckInModal() {
    const currentTime = new Date();
    const timeString = currentTime.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: true 
    });
    const dateString = currentTime.toLocaleDateString();
    
    const message = `Current Time: ${timeString}\nDate: ${dateString}\n\nWould you like to Check In?`;
    
    if (confirm(message)) {
        recordCheckIn(currentTime);
    }
}

// Record check-in/out
function recordCheckIn(timestamp) {
    const userId = localStorage.getItem('userId');
    
    fetch('php/record_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            userId: userId,
            timestamp: timestamp.toISOString(),
            action: 'check_in'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Check-in recorded successfully!');
            loadRecentActivity();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error recording check-in');
    });
}

// Load recent activity
function loadRecentActivity() {
    const userId = localStorage.getItem('userId');
    
    fetch('php/get_recent_activity.php?userId=' + userId)
        .then(response => response.json())
        .then(data => {
            const activityContainer = document.getElementById('recentActivity');
            
            if (data.success && data.activities && data.activities.length > 0) {
                let html = '<div style="display: flex; flex-direction: column; gap: 1rem;">';
                
                data.activities.forEach(activity => {
                    const time = new Date(activity.timestamp).toLocaleString();
                    html += `
                        <div style="padding: 1rem; background: #333; border-radius: 6px; border-left: 3px solid var(--primary-color);">
                            <div style="font-weight: 600; color: var(--light-text);">${activity.activity_type}</div>
                            <div style="color: #aaa; font-size: 0.9rem;">${activity.description}</div>
                            <div style="color: #666; font-size: 0.8rem; margin-top: 0.5rem;">${time}</div>
                        </div>
                    `;
                });
                
                html += '</div>';
                activityContainer.innerHTML = html;
            } else {
                activityContainer.innerHTML = `
                    <div style="color: #aaa; text-align: center;">
                        <i class="fas fa-info-circle"></i>
                        <p>No recent activity</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading activity:', error);
        });
}
