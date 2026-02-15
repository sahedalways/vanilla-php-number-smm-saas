document.querySelectorAll('.buy-service-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
        const serviceName = $(this).data('service-name');
        const servicePrice = $(this).data('service-price');
        const serviceMin = $(this).data('service-min');
        const serviceMax = $(this).data('service-max');
        const serviceId = $(this).data('service-id');

        // User balance from PHP session
        const userBalance = parseFloat($('#userBalanceHidden').val()) || 0;

        // Fill modal
        $('#buyServiceId').val(serviceId);
        $('#buyServiceName').text(serviceName);
        $('#buyServicePrice').text(servicePrice.toFixed(2));
        $('#buyServiceMin').text(serviceMin);
        $('#buyServiceMax').text(serviceMax);
        $('#userBalance').text(userBalance.toFixed(2));

        const $qty = $('#buyQuantity');
        $qty.val(serviceMin);
        $qty.attr('min', serviceMin);
        $qty.attr('max', serviceMax);

        // Show modal
        const buyModal = new bootstrap.Modal(document.getElementById('buyServiceModal'));
        buyModal.show();
    });
});
document.getElementById('buyServiceBtn').addEventListener('click', function (e) {
    e.preventDefault();

    const btn = this;
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');
    const errorEl = document.getElementById('buyError');

    errorEl.textContent = '';

    const serviceName = document.getElementById('buyServiceName').textContent;
    const unitPrice = parseFloat(document.getElementById('buyServicePrice').textContent) || 0;
    const minQty = parseInt(document.getElementById('buyServiceMin').textContent) || 1;
    const maxQty = parseInt(document.getElementById('buyServiceMax').textContent) || 999999;
    const userBalance = parseFloat(document.getElementById('userBalanceHidden').value) || 0;
    const serviceId = document.getElementById('buyServiceId').value;

    let quantity = parseInt(document.getElementById('buyQuantity').value);

    if (quantity < minQty) {
        errorEl.textContent = `Quantity cannot be less than minimum (${minQty}).`;
        return;
    }
    if (quantity > maxQty) {
        errorEl.textContent = `Quantity cannot exceed maximum (${maxQty}).`;
        return;
    }

    const totalPrice = unitPrice * quantity;
    if (totalPrice > userBalance) {
        errorEl.textContent = `Insufficient balance. Your total price is ₦${totalPrice.toFixed(2)}, but your balance is ₦${userBalance.toFixed(2)}.`;
        return;
    }

    btn.disabled = true;
    btnText.textContent = 'Processing...';
    spinner.classList.remove('d-none');

    const csrfToken = document.getElementById('csrf_token').value;

    // Ajax request
    $.ajax({
        url: '/controllers/customer/services/smm/buy-service',
        method: 'POST',
        data: {
            service_id: serviceId,
            service_name: serviceName,
            unit_price: unitPrice,
            quantity: quantity,
            total_price: totalPrice,
            csrf_token: csrfToken,
        },
        dataType: 'json',
        success: function (res) {
            btn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> Confirm & Purchase';
            spinner.classList.add('d-none');

            if (res.status === 'success') {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();

                // Update user balance in modal and top badge
                const newBalance = userBalance - totalPrice;
                document.getElementById('userBalance').textContent = newBalance.toFixed(2);
                document.getElementById('userBalanceHidden').value = newBalance;

                // Close modal
                const modal = bootstrap.Modal.getInstance(
                    document.getElementById('buyServiceModal')
                );
                modal.hide();

                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                errorEl.textContent = res.message || 'Something went wrong.';
            }
        },
        error: function () {
            btn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> Confirm & Purchase';
            spinner.classList.add('d-none');
            errorEl.textContent = 'Something went wrong.';
        },
    });
});
