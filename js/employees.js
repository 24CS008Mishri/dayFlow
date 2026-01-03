// Employees Page Script
let allEmployees = [];
let currentTab = 'all';

document.addEventListener('DOMContentLoaded', function() {
    checkUserRole();
    loadEmployees();
    setupSearchListener();
});

function checkUserRole() {
    const userRole = localStorage.getItem('userRole');
    const userName = localStorage.getItem('userName');
    
    // Check if user is admin or hr
    if (userRole !== 'admin' && userRole !== 'hr') {
        window.location.href = 'index.html';
        return;
    }
    
    // Update profile badge
    const firstLetter = (userName || 'U').charAt(0).toUpperCase();
    document.getElementById('profileBadge').textContent = firstLetter;
}

function loadEmployees() {
    // Add cache-busting parameter
    const timestamp = new Date().getTime();
    fetch(`php/get_all_employees_salary.php?t=${timestamp}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Employee data received:', data);
            if (data.success && data.data && data.data.length > 0) {
                allEmployees = data.data;
                displayEmployees(allEmployees);
                updateEmployeeCount();
            } else {
                console.warn('No employees found or error:', data.message);
                allEmployees = [];
                showEmptyState();
            }
        })
        .catch(error => {
            console.error('Error loading employees:', error);
            showEmptyState();
        });
}

function displayEmployees(employees) {
    const grid = document.getElementById('employeesGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (employees.length === 0) {
        grid.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    grid.style.display = 'grid';
    emptyState.style.display = 'none';
    grid.innerHTML = '';
    
    employees.forEach(employee => {
        const card = createEmployeeCard(employee);
        grid.appendChild(card);
    });
}

function createEmployeeCard(employee) {
    const card = document.createElement('div');
    card.className = 'employee-card';
    
    // Get first letter of name for avatar
    const avatarLetter = (employee.name || 'E').charAt(0).toUpperCase();
    
    // Determine status (this would typically come from attendance data)
    const statusClass = 'status-present'; // Default to present
    const statusIcon = 'fas fa-circle';
    
    card.innerHTML = `
        <div class="employee-card-header">
            <div class="employee-avatar">${avatarLetter}</div>
            <div class="status-indicator ${statusClass}" title="Present"></div>
        </div>
        <div class="employee-card-body">
            <div class="employee-name">${employee.name || 'N/A'}</div>
            <div class="employee-position">${employee.job_position || 'Position TBD'}</div>
            <div class="employee-info">
                <div class="employee-info-item">
                    <i class="fas fa-building"></i>
                    <span>${employee.company || 'N/A'}</span>
                </div>
                <div class="employee-info-item">
                    <i class="fas fa-briefcase"></i>
                    <span>${employee.department || 'N/A'}</span>
                </div>
                <div class="employee-info-item">
                    <i class="fas fa-envelope"></i>
                    <span>${employee.email || 'N/A'}</span>
                </div>
                <div class="employee-info-item">
                    <i class="fas fa-phone"></i>
                    <span>${employee.mobile || 'N/A'}</span>
                </div>
            </div>
            <div class="employee-actions">
                <button class="action-btn" onclick="viewProfile(${employee.id})" title="View Profile">
                    <i class="fas fa-eye"></i>
                    View
                </button>
                <button class="action-btn" onclick="editEmployee(${employee.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                    Edit
                </button>
                <button class="action-btn" onclick="deleteEmployee(${employee.id})" title="Delete" style="color: #ff6b6b;">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    `;
    
    return card;
}

function updateEmployeeCount() {
    const count = allEmployees.length;
    const countText = count === 1 ? '1 employee' : `${count} employees`;
    document.getElementById('employeeCount').textContent = countText;
}

function showEmptyState() {
    document.getElementById('employeesGrid').style.display = 'none';
    document.getElementById('emptyState').style.display = 'block';
    document.getElementById('employeeCount').textContent = '0 employees';
}

function setupSearchListener() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filtered = allEmployees.filter(emp => 
            emp.name.toLowerCase().includes(searchTerm) ||
            emp.email.toLowerCase().includes(searchTerm) ||
            emp.department.toLowerCase().includes(searchTerm) ||
            emp.job_position.toLowerCase().includes(searchTerm)
        );
        displayEmployees(filtered);
    });
}

function switchTab(tab) {
    currentTab = tab;
    
    // Update active tab styling
    document.querySelectorAll('.tab-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.closest('.tab-item').classList.add('active');
    
    // Handle different tab views
    switch(tab) {
        case 'all':
            displayEmployees(allEmployees);
            break;
        case 'attendance':
            alert('Attendance view coming soon!');
            break;
        case 'settings':
            alert('Settings coming soon!');
            break;
    }
}

function viewProfile(employeeId) {
    // Store the selected employee id and redirect to profile
    localStorage.setItem('selectedEmployeeId', employeeId);
    window.location.href = 'employeeprofile.html?id=' + employeeId;
}

function editEmployee(employeeId) {
    localStorage.setItem('selectedEmployeeId', employeeId);
    window.location.href = 'employeeprofile.html?id=' + employeeId + '&edit=true';
}

function deleteEmployee(employeeId) {
    if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
        fetch('php/delete_employee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + employeeId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Employee deleted successfully!');
                loadEmployees(); // Reload the list
            } else {
                alert('Error deleting employee: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting employee');
        });
    }
}

// Logout function
function logout() {
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRole');
    localStorage.removeItem('selectedEmployeeId');
    localStorage.removeItem('rememberedLoginId');
    
    fetch('php/session_check.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=logout'
    }).then(() => {
        window.location.href = 'index.html';
    }).catch(() => {
        window.location.href = 'index.html';
    });
}
