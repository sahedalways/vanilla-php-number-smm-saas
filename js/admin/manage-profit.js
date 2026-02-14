document.getElementById('profitForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Clear previous errors
    const errorEl = document.getElementById('error-profit_percentage');
    if (errorEl) errorEl.innerText = '';

    let hasError = false;
    const profitPercentage = document.getElementById('profit_percentage').value.trim();

    // Validation
    if (!profitPercentage) {
        errorEl.innerText = 'Profit percentage is required.';
        hasError = true;
    } else if (isNaN(profitPercentage) || profitPercentage < 0 || profitPercentage > 100) {
        errorEl.innerText = 'Enter a valid number between 0 and 100.';
        hasError = true;
    }

    if (hasError) return;

    // Disable button + show spinner
    const submitBtn = this.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('i') ? submitBtn.innerHTML : submitBtn.innerText;
    const spinner = submitBtn.querySelector('.spinner-border');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Updating... <span class="spinner-border spinner-border-sm ms-2"></span>';

    const csrfToken = document.getElementById('csrf_token').value;

    $.ajax({
        url: '/controllers/admin/profit/update', // create this endpoint
        method: 'POST',
        data: {
            profit_percentage: profitPercentage,
            csrf_token: csrfToken,
        },
        dataType: 'json',
        success: function (res) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Update Profit';

            if (res.status === 'success') {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();
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
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Update Profit';
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
