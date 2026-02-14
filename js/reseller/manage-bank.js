document.getElementById('updateBankAccount').addEventListener('click', function () {
    // Clear previous errors
    document.querySelectorAll('[id^="error-"]').forEach((el) => (el.innerText = ''));

    let hasError = false;

    const bankName = document.getElementById('bank_name').value.trim();
    const accountName = document.getElementById('account_name').value.trim();
    const accountNumber = document.getElementById('account_number').value.trim();
    const swiftCode = document.getElementById('swift_code').value.trim();
    const csrfToken = document.getElementById('csrf_token').value;
    const bank_id = document.getElementById('bank_id').value;

    // Validation

    if (!bankName) {
        document.getElementById('error-bank_name').innerText = 'Bank name is required.';
        hasError = true;
    }

    if (!accountName) {
        document.getElementById('error-account_name').innerText = 'Account name is required.';
        hasError = true;
    }

    if (!accountNumber) {
        document.getElementById('error-account_number').innerText = 'Account number is required.';
        hasError = true;
    } else if (!/^\d{10}$/.test(accountNumber)) {
        document.getElementById('error-account_number').innerText =
            'Account number must be 10 digits.';
        hasError = true;
    }

    if (hasError) return;

    const btn = this;
    const spinner = btn.querySelector('.spinner-border');
    const btnText = btn.querySelector('.btn-text');

    btn.disabled = true;
    spinner.classList.remove('d-none');
    btnText.innerHTML = 'Updating...';

    $.ajax({
        url: '/controllers/reseller/bank/update',
        method: 'POST',
        data: {
            bank_name: bankName,
            id: bank_id,
            account_name: accountName,
            account_number: accountNumber,
            swift_code: swiftCode,
            csrf_token: csrfToken,
        },
        dataType: 'json',
        success: function (res) {
            btn.disabled = false;
            spinner.classList.add('d-none');
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Bank Account';

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
            btn.disabled = false;
            spinner.classList.add('d-none');
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Bank Account';

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
