const sendBtn = document.getElementById('sendWithdrawRequest');
const btnText = sendBtn.querySelector('.btn-text');
const spinner = sendBtn.querySelector('.spinner-border');

sendBtn.addEventListener('click', function (e) {
    e.preventDefault();

    // Clear previous errors
    ['bank_name', 'account_name', 'account_number', 'swift_code', 'withdraw_amount'].forEach(
        (f) => {
            const el = document.getElementById('error-' + f);
            if (el) el.innerText = '';
        }
    );

    let hasError = false;
    const bankName = document.getElementById('bank_name').value.trim();
    const accountName = document.getElementById('account_name').value.trim();
    const accountNumber = document.getElementById('account_number').value.trim();
    const swiftCode = document.getElementById('swift_code').value.trim();
    const withdrawAmount = document.getElementById('withdraw_amount').value.trim();

    // Validation
    if (!bankName) {
        document.getElementById('error-bank_name').innerText = 'Bank Name is required.';
        hasError = true;
    }
    if (!accountName) {
        document.getElementById('error-account_name').innerText = 'Account Name is required.';
        hasError = true;
    }
    if (!accountNumber) {
        document.getElementById('error-account_number').innerText = 'Account Number is required.';
        hasError = true;
    } else if (!/^\d{10}$/.test(accountNumber)) {
        document.getElementById('error-account_number').innerText =
            'Account Number must be 10 digits.';
        hasError = true;
    }
    if (!withdrawAmount) {
        document.getElementById('error-withdraw_amount').innerText = 'Amount is required.';
        hasError = true;
    } else if (isNaN(withdrawAmount) || parseFloat(withdrawAmount) <= 0) {
        document.getElementById('error-withdraw_amount').innerText = 'Enter a valid amount.';
        hasError = true;
    }

    if (hasError) return;

    // Disable button + show spinner
    sendBtn.disabled = true;
    btnText.innerText = 'Sending...';
    spinner.classList.remove('d-none');

    const csrfToken = document.getElementById('csrf_token').value;

    // AJAX request
    $.ajax({
        url: '/controllers/reseller/withdraw/send',
        method: 'POST',
        data: {
            bank_name: bankName,
            account_name: accountName,
            account_number: accountNumber,
            swift_code: swiftCode,
            amount: withdrawAmount,
            csrf_token: csrfToken,
        },
        dataType: 'json',
        success: function (res) {
            sendBtn.disabled = false;
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Send Withdraw Request';
            spinner.classList.add('d-none');

            if (res.status === 'success') {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();
                $('#addWithdrawRequestModal').modal('hide');

                // Reload page or table
                setTimeout(() => location.reload(), 500);
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
            sendBtn.disabled = false;
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Send Withdraw Request';
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
