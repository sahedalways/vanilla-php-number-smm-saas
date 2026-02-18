function filterCards() {
    let country = $('#filterCountry').val();
    let operator = $('#filterOperator').val();
    let service = $('#filterService').val();

    $('.service-card').each(function () {
        let show = true;

        if (country && $(this).data('country') != country) show = false;
        if (operator && $(this).data('operator') != operator) show = false;
        if (service && $(this).data('service') != service) show = false;

        $(this).toggle(show);
    });
}

$('#filterCountry, #filterOperator, #filterService').on('change', filterCards);

$('#resetFilters').on('click', function () {
    $('#filterCountry, #filterOperator, #filterService').val('');
    filterCards();
});

$(document).on('click', '.buy-service-btn', function () {
    const btn = this;
    const $btn = $(this);

    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');
    const errorEl = document.getElementById('buyError');

    errorEl.textContent = '';

    const serviceId = $btn.data('service-id');
    const country = $btn.data('country');
    const operator = $btn.data('operator') || 'any';
    const product = $btn.data('service');
    const price = parseFloat($btn.data('price')) || 0;

    const userBalance = parseFloat($('#userBalance').val()) || 0;

    if (!serviceId || !country || !product) {
        return alert('Invalid service data');
    }

    if (price > userBalance) {
        return alert(`Insufficient balance (₦${userBalance.toFixed(2)})`);
    }

    if (!confirm(`Are you sure you want to buy this number for ₦${price.toFixed(2)} ?`)) {
        return;
    }

    btn.disabled = true;
    btnText.textContent = 'Processing...';
    spinner.classList.remove('d-none');

    $.ajax({
        url: '/controllers/customer/services/sms/buy-number',
        method: 'POST',
        data: {
            service_id: serviceId,
            country: country,
            operator: operator,
            product: product,
            price: price,
            csrf_token: $('#csrf_token').val(),
        },
        dataType: 'json',

        success: function (res) {
            btn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> Confirm & Buy';
            spinner.classList.add('d-none');

            if (res.status === 'success') {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();

                const newBalance = userBalance - price;
                $('#userBalance').text(newBalance.toFixed(2));
                $('#userBalanceHidden').val(newBalance);

                const modal = bootstrap.Modal.getInstance(document.getElementById('buySmsModal'));
                if (modal) modal.hide();

                setTimeout(() => {
                    window.location.href = '/views/customer/services/sms/orders';
                }, 500);
            } else {
                errorEl.textContent = res.message || 'Something went wrong';
            }
        },

        error: function () {
            btn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> Confirm & Buy';
            spinner.classList.add('d-none');
            errorEl.textContent = 'Something went wrong';
        },
    });
});
