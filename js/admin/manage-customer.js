const registerBtn = document.getElementById('register');
const btnText = registerBtn.querySelector('.btn-text');
const spinner = registerBtn.querySelector('.spinner-border');

// Password toggle
const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', () => {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    togglePassword.classList.toggle('bi-eye');
    togglePassword.classList.toggle('bi-eye-slash');
});

// Confirm Password toggle
const confirmPasswordInput = document.getElementById('confirm_password');
const toggleConfirm = document.getElementById('toggleConfirmPassword');

toggleConfirm.addEventListener('click', () => {
    const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
    confirmPasswordInput.type = type;
    toggleConfirm.classList.toggle('bi-eye');
    toggleConfirm.classList.toggle('bi-eye-slash');
});

document.getElementById('register').addEventListener('click', function (e) {
    e.preventDefault();

    // Clear errors
    ['name', 'email', 'phone', 'password', 'confirm_password'].forEach((f) => {
        const el = document.getElementById('error-' + f);
        if (el) el.innerText = '';
    });

    let hasError = false;
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (!name) {
        document.getElementById('error-name').innerText = 'Full Name is required.';
        hasError = true;
    }
    if (!email) {
        document.getElementById('error-email').innerText = 'Email is required.';
        hasError = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('error-email').innerText = 'Invalid email.';
        hasError = true;
    }
    if (!phone) {
        document.getElementById('error-phone').innerText = 'Phone is required.';
        hasError = true;
    }

    if (!password) {
        document.getElementById('error-password').innerText = 'Password is required.';
        hasError = true;
    } else if (password.length < 6) {
        document.getElementById('error-password').innerText = 'Password must be at least 6 chars.';
        hasError = true;
    }
    if (!confirmPassword) {
        document.getElementById('error-confirm_password').innerText =
            'Confirm password is required.';
        hasError = true;
    } else if (password !== confirmPassword) {
        document.getElementById('error-confirm_password').innerText = 'Passwords do not match.';
        hasError = true;
    }

    if (hasError) return;

    // Disable button + show spinner
    registerBtn.disabled = true;
    btnText.innerText = 'Creating...';
    spinner.classList.remove('d-none');

    const csrfToken = document.getElementById('csrf_token').value;
    // Ajax request example
    $.ajax({
        url: '/controllers/admin/customer/register',
        method: 'POST',
        data: {
            name,
            email,
            phone,
            password,
            confirm_password: confirmPassword,
            csrf_token: csrfToken,
        },
        dataType: 'json',
        success: function (res) {
            registerBtn.disabled = false;
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Create Customer Account';
            spinner.classList.add('d-none');
            clear();

            if (res.status === 'success') {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();
                $('#addCustomerModal').modal('hide');

                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }).showToast();
            }
        },
        error: function (xhr, status, error) {
            registerBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Create Customer Account';

            Toastify({
                text: 'Server error: ' + error,
                duration: 4000,
                gravity: 'top',
                position: 'right',
                backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
            }).showToast();
        },
    });
});

function clear() {
    document.getElementById('name').value = '';
    document.getElementById('email').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('password').value = '';
    document.getElementById('confirm_password').value = '';
}

$(document).ready(function () {
    $('.edit-customer-btn').on('click', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const email = $(this).data('email');
        const phone = $(this).data('phone');

        // Set modal values
        $('#customer_id').val(id);
        $('#edit_name').val(name);
        $('#edit_email').val(email);
        $('#edit_phone').val(phone);
        $('#edit_password').val('');
        $('#edit_confirm_password').val('');

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        modal.show();
    });
});

document.getElementById('updateCustomer').addEventListener('click', function (e) {
    e.preventDefault();

    // Clear previous errors
    ['edit_name', 'edit_email', 'edit_phone', 'edit_password', 'edit_confirm_password'].forEach(
        (f) => {
            const el = document.getElementById('error-' + f);
            if (el) el.innerText = '';
        }
    );

    let hasError = false;
    const id = document.getElementById('customer_id').value;
    const name = document.getElementById('edit_name').value.trim();
    const email = document.getElementById('edit_email').value.trim();
    const phone = document.getElementById('edit_phone').value.trim();
    const password = document.getElementById('edit_password').value;
    const confirmPassword = document.getElementById('edit_confirm_password').value;

    if (!name) {
        document.getElementById('error-edit_name').innerText = 'Full Name is required.';
        hasError = true;
    }
    if (!email) {
        document.getElementById('error-edit_email').innerText = 'Email is required.';
        hasError = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('error-edit_email').innerText = 'Invalid email.';
        hasError = true;
    }
    if (!phone) {
        document.getElementById('error-edit_phone').innerText = 'Phone is required.';
        hasError = true;
    }

    if (password || confirmPassword) {
        if (password.length < 6) {
            document.getElementById('error-edit_password').innerText =
                'Password must be at least 6 chars.';
            hasError = true;
        }
        if (password !== confirmPassword) {
            document.getElementById('error-edit_confirm_password').innerText =
                'Passwords do not match.';
            hasError = true;
        }
    }

    if (hasError) return;

    // Disable button + show spinner
    const updateBtn = this;
    const btnText = updateBtn.querySelector('.btn-text');
    const spinner = updateBtn.querySelector('.spinner-border');
    updateBtn.disabled = true;
    btnText.innerText = 'Updating...';
    spinner.classList.remove('d-none');

    const csrfToken = document.getElementById('csrf_token').value;

    $.ajax({
        url: '/controllers/admin/customer/update',
        method: 'POST',
        data: {
            id,
            name,
            email,
            phone,
            password,
            confirm_password: confirmPassword,
            csrf_token: csrfToken,
        },
        dataType: 'json',
        success: function (res) {
            updateBtn.disabled = false;
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Customer';
            spinner.classList.add('d-none');

            if (res.status === 'success') {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();

                $('#editCustomerModal').modal('hide');

                // reload table to reflect changes
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }).showToast();
            }
        },
        error: function () {
            updateBtn.disabled = false;
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Customer';
            spinner.classList.add('d-none');
            Toastify({
                text: 'Something went wrong.',
                duration: 4000,
                gravity: 'top',
                position: 'right',
                backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
            }).showToast();
        },
    });
});

function confirmDelete(id) {
    if (!confirm('Are you sure you want to delete this customer?')) return;

    const csrfToken = document.getElementById('csrf_token').value;

    $.ajax({
        url: '/controllers/admin/customer/delete',
        method: 'POST',
        data: {
            id: id,
            csrf_token: csrfToken,
        },
        dataType: 'json',
        success: function (res) {
            if (res.status === 'success') {
                Toastify({
                    text: res.message,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();

                // Remove the row from table
                $(`button[onclick="confirmDelete(${id})"]`).closest('tr').remove();
            } else {
                Toastify({
                    text: res.message,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }).showToast();
            }
        },
        error: function () {
            Toastify({
                text: 'Something went wrong.',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
            }).showToast();
        },
    });
}
