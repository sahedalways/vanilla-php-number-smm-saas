const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');
const registerBtn = document.getElementById('register');
const btnText = registerBtn.querySelector('.btn-text');
const spinner = registerBtn.querySelector('.spinner-border');

togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
});

const confirmPasswordInput = document.getElementById('confirm_password');
const toggleConfirm = document.getElementById('toggleConfirmPassword');

toggleConfirm.addEventListener('click', function () {
    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPasswordInput.setAttribute('type', type);

    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
});

document.getElementById('register').addEventListener('click', function (e) {
    e.preventDefault();

    ['name', 'email', 'phone', 'password', 'confirm_password', 'terms'].forEach((field) => {
        const el = document.getElementById('error-' + field);
        if (el) {
            el.innerText = '';
        }
    });

    let hasError = false;
    const csrfToken = document.getElementById('csrf_token').value;
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const referral = document.getElementById('referral').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const termsChecked = document.getElementById('terms_check').checked;

    if (!name) {
        document.getElementById('error-name').innerText = 'Full Name is required.';
        hasError = true;
    }

    if (!email) {
        document.getElementById('error-email').innerText = 'Email is required.';
        hasError = true;
    } else if (!isValidEmail(email)) {
        document.getElementById('error-email').innerText = 'Invalid email format.';
        hasError = true;
    }

    if (!phone) {
        document.getElementById('error-phone').innerText = 'Phone number is required.';
        hasError = true;
    } else if (!isValidPhone(phone)) {
        document.getElementById('error-phone').innerText = 'Invalid phone number.';
        hasError = true;
    }

    if (!password) {
        document.getElementById('error-password').innerText = 'Password is required.';
        hasError = true;
    } else if (password.length < 6) {
        document.getElementById('error-password').innerText =
            'Password must be at least 6 characters.';
        hasError = true;
    }
    if (!confirmPassword) {
        document.getElementById('error-confirm_password').innerText =
            'Confirm Password is required.';
        hasError = true;
    } else if (password !== confirmPassword) {
        document.getElementById('error-confirm_password').innerText = 'Passwords do not match.';
        hasError = true;
    }
    if (!termsChecked) {
        document.getElementById('error-terms').innerText =
            'You must accept Terms and Privacy Policy.';
        hasError = true;
    }

    if (!hasError) {
        registerBtn.disabled = true;
        btnText.innerText = 'Creating...';
        spinner.classList.remove('d-none');

        $.ajax({
            url: '/controllers/customer/auth/RegisterController',
            type: 'POST',
            data: {
                name: name,
                email: email,
                phone: phone,
                password: password,
                referral: referral,
                confirm_password: confirmPassword,
                csrf_token: csrfToken,
            },
            dataType: 'json',
            success: function (res) {
                registerBtn.disabled = false;
                btnText.innerText = 'Create Account';
                spinner.classList.add('d-none');

                if (res.status === 'success') {
                    clear();
                    Toastify({
                        text: res.message,
                        duration: 4000,
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                    }).showToast();
                    setTimeout(() => {
                        window.location.href = '/views/customer/dashboard';
                    }, 1500);
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
            error: function (xhr, status, error) {
                registerBtn.disabled = false;
                btnText.innerText = 'Create Account';
                spinner.classList.add('d-none');

                let message = 'Something went wrong.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    message = xhr.responseText;
                }

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

function clear() {
    document.getElementById('name').value = '';
    document.getElementById('email').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('password').value = '';
    document.getElementById('confirm_password').value = '';
    document.getElementById('terms_check').checked = false;
}

function isValidPhone(phone) {
    const re = /^\+?\d{7,15}$/;
    return re.test(phone);
}
