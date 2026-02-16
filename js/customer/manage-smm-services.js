document.querySelectorAll('.buy-service-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
        const service = {
            id: $(this).data('service-id'),
            name: $(this).data('service-name'),
            price: parseFloat($(this).data('service-price')),
            min: parseInt($(this).data('service-min')),
            max: parseInt($(this).data('service-max')),
            type: $(this).data('service-type'),
        };

        const userBalance = parseFloat($('#userBalanceHidden').val()) || 0;

        // Fill modal basics
        $('#buyServiceId').val(service.id);
        $('#buyServiceName').text(service.name);
        $('#buyServicePrice').text(service.price.toFixed(2));
        $('#buyServiceMin').text(service.min);
        $('#buyServiceMax').text(service.max);
        $('#userBalance').text(userBalance.toFixed(2));
        const $qty = $('#buyQuantity');
        $qty.val(service.min).attr('min', service.min).attr('max', service.max);

        const $fieldsContainer = $('#typeSpecificFields');
        $fieldsContainer.empty();

        // Generate inputs based on service type
        const type = service.type;
        if (type === 'Default' || type === 'YouTube Likes' || type === 'TikTok Views') {
            $fieldsContainer.append(`
                <label class="form-label text-dark">Link</label>
                <input type="url" class="form-control type-input" id="order_link" placeholder="Enter URL">
            `);
        } else if (type === 'Custom Comments' || type === 'Custom Comments Package') {
            $fieldsContainer.append(`
                <label class="form-label text-dark">Link</label>
                <input type="url" class="form-control type-input mb-2" id="order_link" placeholder="Enter URL">
                <label class="form-label text-dark">Comments</label>
                <textarea class="form-control type-input" id="order_comments" placeholder="Enter comments"></textarea>
            `);
        } else if (type === 'Comment Likes') {
            $fieldsContainer.append(`
                <label class="form-label text-dark">Link</label>
                <input type="url" class="form-control type-input mb-2" id="order_link" placeholder="Enter URL">
                <label class="form-label text-dark">Username</label>
                <input type="text" class="form-control type-input" id="order_username" placeholder="Enter username">
            `);
        }

        // Show modal
        new bootstrap.Modal(document.getElementById('buyServiceModal')).show();
    });
});

document.getElementById('buyServiceBtn').addEventListener('click', function () {
    const btn = this;
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');
    const errorEl = document.getElementById('buyError');
    errorEl.textContent = '';

    const serviceId = $('#buyServiceId').val();
    const serviceName = $('#buyServiceName').text();
    const unitPrice = parseFloat($('#buyServicePrice').text()) || 0;
    const minQty = parseInt($('#buyServiceMin').text()) || 1;
    const maxQty = parseInt($('#buyServiceMax').text()) || 999999;
    const userBalance = parseFloat($('#userBalanceHidden').val()) || 0;
    let quantity = parseInt($('#buyQuantity').val());

    if (quantity < minQty)
        return (errorEl.textContent = `Quantity cannot be less than minimum (${minQty})`);
    if (quantity > maxQty)
        return (errorEl.textContent = `Quantity cannot exceed maximum (${maxQty})`);

    // Gather type-specific fields
    const typeInputs = {};
    let hasError = false;

    $('#typeSpecificFields .type-input').each(function () {
        const val = $(this).val().trim();
        const id = this.id;

        if (!val) {
            errorEl.textContent = `Please fill out ${id.replace('order_', '')}`;
            hasError = true;
            return false; // stop loop
        }

        // URL validation for link fields
        if (id === 'order_link' && !isValidURL(val)) {
            errorEl.textContent = 'Please enter a valid URL.';
            hasError = true;
            return false; // stop loop
        }

        typeInputs[id.replace('order_', '')] = val;
    });

    if (hasError) return;

    const totalPrice = unitPrice * quantity;
    if (totalPrice > userBalance)
        return (errorEl.textContent = `Insufficient balance (₦${userBalance.toFixed(2)})`);

    btn.disabled = true;
    btnText.textContent = 'Processing...';
    spinner.classList.remove('d-none');

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
            order_params: typeInputs,
            csrf_token: $('#csrf_token').val(),
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
                const newBalance = userBalance - totalPrice;
                $('#userBalance').text(newBalance.toFixed(2));
                $('#userBalanceHidden').val(newBalance);
                bootstrap.Modal.getInstance($('#buyServiceModal')).hide();
                setTimeout(() => location.reload(), 500);
            } else {
                errorEl.textContent = res.message || 'Something went wrong';
            }
        },
        error: function () {
            btn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> Confirm & Purchase';
            spinner.classList.add('d-none');
            errorEl.textContent = 'Something went wrong';
        },
    });
});

function isValidURL(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}
