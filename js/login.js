// Handle login form submission
function handleLogin(event) {
    event.preventDefault();
    
    const loginIdOrEmail = document.getElementById('loginId').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!loginIdOrEmail || !password) {
        showError('Please enter both Login ID/Email and Password');
        return;
    }
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
    
    // Clear previous errors
    document.getElementById('errorMessage').style.display = 'none';
    
    // Send login request to backend
    fetch('php/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            loginId: loginIdOrEmail,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store user info in localStorage
            localStorage.setItem('userId', data.userId);
            localStorage.setItem('userName', data.userName);
            localStorage.setItem('userEmail', data.userEmail);
            localStorage.setItem('userRole', data.userRole);
            // Redirect to dashboard or profile page
            window.location.href = 'employeeprofile.html';
        } else {
            showError(data.message || 'Invalid Login ID/Email or Password');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function handleForgotPassword() {
    const email = prompt('Enter your registered email address:');
    if (email && email.trim()) {
        alert('Password reset link sent to ' + email);
    }
}

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = event.target.closest('button');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordInput.type = 'password';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

function handleRememberMe() {
    const rememberMe = document.getElementById('rememberMe').checked;
    if (rememberMe) {
        const loginId = document.getElementById('loginId').value.trim();
        if (loginId) {
            localStorage.setItem('rememberedLoginId', loginId);
        }
    } else {
        localStorage.removeItem('rememberedLoginId');
    }
}

window.addEventListener('DOMContentLoaded', function() {
    const rememberedLoginId = localStorage.getItem('rememberedLoginId');
    if (rememberedLoginId) {
        document.getElementById('loginId').value = rememberedLoginId;
        document.getElementById('rememberMe').checked = true;
    }
});
