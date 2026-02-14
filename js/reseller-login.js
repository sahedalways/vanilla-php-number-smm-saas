const loginBtn = document.getElementById('login');
const btnText = loginBtn.querySelector('.btn-text');
const spinner = loginBtn.querySelector('.spinner-border');

const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
});

loginBtn.addEventListener('click', function (e) {
    e.preventDefault();

    // Clear previous errors
    ['email', 'password'].forEach((field) => {
        const el = document.getElementById('error-' + field);
        if (el) el.innerText = '';
    });

    let hasError = false;
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const csrfToken = document.getElementById('csrf_token').value;

    if (!email) {
        document.getElementById('error-email').innerText = 'Email is required.';
        hasError = true;
    } else if (!isValidEmail(email)) {
        document.getElementById('error-email').innerText = 'Invalid email format.';
        hasError = true;
    }

    if (!password) {
        document.getElementById('error-password').innerText = 'Password is required.';
        hasError = true;
    }

    if (!hasError) {
        loginBtn.disabled = true;
        btnText.innerText = 'Signing in...';
        spinner.classList.remove('d-none');

        $.ajax({
            url: '/controllers/reseller/auth/login',
            type: 'POST',
            data: { email, password, csrf_token: csrfToken },
            dataType: 'json',
            success: function (res) {
                loginBtn.disabled = false;
                btnText.innerText = 'Sign In';
                spinner.classList.add('d-none');

                if (res.status === 'success') {
                    Toastify({
                        text: res.message,
                        duration: 2000,
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                    }).showToast();

                    setTimeout(() => {
                        window.location.href = '/views/reseller/dashboard';
                    }, 1000);
                } else {
                    Toastify({
                        text: res.message,
                        duration: 4000,
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                    }).showToast();
                }
            },
            error: function (xhr) {
                loginBtn.disabled = false;
                btnText.innerText = 'Sign In';
                spinner.classList.add('d-none');

                let message = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.message)
                    message = xhr.responseJSON.message;
                Toastify({
                    text: message,
                    duration: 5000,
                    close: true,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }).showToast();
            },
        });
    }
});

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

document.addEventListener('DOMContentLoaded', function () {
    const msg = '<?php echo $msg; ?>';

    if (msg === 'logout_success') {
        Toastify({
            text: 'You have successfully logged out.',
            duration: 4000,
            close: true,
            gravity: 'top',
            position: 'right',
            backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
        }).showToast();
    }
});
