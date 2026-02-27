let selectedCountry = null;
let selectedOperator = null;
let selectedService = null;

function fetchFilteredServices() {
    let country = $('#filterCountry').val();
    let service = $('#filterService').val();
    selectedCountry = country;
    selectedService = service;
    $('#initialPlaceholder').addClass('d-none');

    $.ajax({
        url: '/controllers/customer/services/sms/get-numbers',
        method: 'GET',
        data: {
            country: country,
            service: service,
            csrf_token: $('#csrf_token').val(),
        },
        dataType: 'json',
        beforeSend: function () {
            $('#filterLoader').removeClass('d-none');
            $('#servicesContainer').empty();
        },
        success: function (res) {
            $('#filterLoader').addClass('d-none');
            let $container = $('#servicesContainer');
            $container.empty();
            $('#initialPlaceholder').addClass('d-none');
            $('#servicesSection').removeClass('d-none');

            if (res.success && res.data && Object.keys(res.data).length > 0) {
                Object.entries(res.data).forEach(([countryCode, countryData]) => {
                    Object.entries(countryData).forEach(([serviceName, products]) => {
                        if (serviceName === 'PriceWithProfit') return;

                        Object.entries(products).forEach(([operator, productInfo]) => {
                            // Skip invalid entries
                            if (
                                !productInfo ||
                                typeof productInfo !== 'object' ||
                                !('cost' in productInfo)
                            )
                                return;

                            let qty = productInfo.count ?? 0;
                            let price = parseFloat(
                                productInfo.PriceWithProfit ?? 0
                            ).toLocaleString();

                            let isDisabled = qty === 0 ? 'disabled' : '';
                            let btnStyle =
                                qty === 0
                                    ? 'background: #cccccc; color: #666666; cursor: not-allowed;'
                                    : 'background: #001f3f; color: white;';

                            let $card = $(`
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-lg service-card"
                                 style="border-radius: 20px; transition: transform 0.3s ease, box-shadow 0.3s ease; background: #ffffff;">
                        <div class="card-header p-3"
                            style="background: linear-gradient(90deg, #001f3f, #003366); border-radius: 20px 20px 0 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <div class="d-flex flex-column gap-2">
                                <!-- Country -->
                                <div>
                                    <small class="text-white-50 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Country</small>
                                    <h6 class="text-white fw-bold mb-0">${countryCode}</h6>
                                </div>

                                <!-- Operator -->
                                <div>
                                    <small class="text-white-50 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Operator</small>
                                    <h6 class="text-white fw-bold mb-0">${operator}</h6>
                                </div>

                                <!-- Product -->
                                <div>
                                    <small class="text-white-50 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Product</small>
                                    <h6 class="text-white fw-bold mb-0">${serviceName}</h6>
                                </div>
                            </div>
                        </div>

                                <div class="card-body p-4">
                                    <div class="row text-center mb-4">
                                        <div class="col-6 border-end">
                                            <p class="text-muted mb-0" style="font-size: 12px;">Available Qty</p>
                                            <h5 class="fw-bold mb-0 text-dark">${qty}</h5>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-0" style="font-size: 12px;">Unit Price</p>
                                            <h5 class="fw-bold mb-0 text-primary">₦${price}</h5>
                                        </div>
                                    </div>

                                  <button class="btn w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center purchase-btn"
                                    style="border-radius: 12px; border: none; font-size: 14px; ${btnStyle}" ${isDisabled}>
                                <i class="fas fa-shopping-basket me-2"></i> Purchase Now
                            </button>
                                </div>
                            </div>
                        </div>
                    `);

                            $container.append($card);
                        });
                    });
                });
            } else {
                $('#servicesSection').addClass('d-none');
                $('#initialPlaceholder').addClass('d-none');

                $container.html(
                    '<div class="col-12 text-center py-5"><div class="p-5 bg-light rounded-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><h5 class="text-muted">No services found</h5></div></div>'
                );
            }
        },

        error: function () {
            $('#filterLoader').addClass('d-none');

            console.error('API call failed');
        },
    });
}

// Search button click
$('#searchFilters').on('click', fetchFilteredServices);

// Reset button click
$('#resetFilters').on('click', function () {
    $('#filterCountry, #filterOperator').val('');
    $('.service-card').show();
    $('#servicesContainer').empty();
    $('#initialPlaceholder').removeClass('d-none');
    $('#servicesSection').addClass('d-none');
});

$(document).on('click', '.purchase-btn', function () {
    // Reset modal inputs
    $('#modalServiceCode').val('');
    $('#modalOperator').val('');
    $('#modalQty').val('');
    $('#modalPrice').val('');
    $('#modalCountry').val('');
    $('#modalCategory').val('');

    let $card = $(this).closest('.card');

    let $headerH6s = $card.find('.card-header h6');
    let country = $headerH6s.eq(0).text().trim();
    let operator = $headerH6s.eq(1).text().trim();
    let product = $headerH6s.eq(2).text().trim();

    // Grab card body info
    let qty = parseInt($card.find('.card-body .col-6:nth-child(1) h5').text().trim()) || 0;
    let price = $card.find('.card-body .col-6:nth-child(2) h5').text().trim();

    // Set modal values
    $('#modalCountry').val(country);
    $('#modalOperator').val(operator);
    $('#modalServiceCode').val(product);
    $('#modalQty').val(qty);
    $('#modalPrice').val(price);

    var purchaseModal = new bootstrap.Modal(document.getElementById('purchaseModal'));
    purchaseModal.show();
});
$('#confirmPurchaseBtn').on('click', function () {
    const btn = this;
    const $btn = $(this);

    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');
    const errorEl = document.getElementById('buyError');

    errorEl.textContent = '';

    const country = $('#modalCountry').val();
    const serviceCode = $('#modalServiceCode').val() || 'any';
    const operator = $('#modalOperator').val() || 'any';
    const priceText = $('#modalPrice')
        .val()
        .replace(/[^\d.]/g, '');
    const price = parseFloat(priceText) || 0;

    const userBalance = parseFloat($('#userBalance').val()) || 0;

    // Clear all previous errors
    $('#modalServiceCodeError, #modalCountryError, #modalOperatorError, #modalPriceError')
        .text('')
        .addClass('d-none');

    let hasError = false;

    if (!serviceCode) {
        $('#modalServiceCodeError').text('Service is required.').removeClass('d-none');
        hasError = true;
    }

    if (!country) {
        $('#modalCountryError').text('Country is required.').removeClass('d-none');
        hasError = true;
    }

    if (!operator) {
        $('#modalOperatorError').text('Operator is required.').removeClass('d-none');
        hasError = true;
    }

    if (price <= 0) {
        $('#modalPriceError').text('Invalid price.').removeClass('d-none');
        hasError = true;
    }

    if (price > userBalance) {
        $('#modalPriceError')
            .text(`Insufficient balance (₦${userBalance.toFixed(2)})`)
            .removeClass('d-none');
        hasError = true;
    }

    if (hasError) return;

    if (!confirm(`Are you sure you want to buy a number for ₦${price.toFixed(2)} ?`)) {
        return;
    }

    btn.disabled = true;
    btnText.textContent = 'Processing...';
    spinner.classList.remove('d-none');

    $.ajax({
        url: '/controllers/customer/services/sms/buy-number',
        method: 'POST',
        data: {
            country: country,
            operator: operator,
            serviceCode: serviceCode,
            price: price,
            csrf_token: $('#csrf_token').val(),
        },
        dataType: 'json',

        success: function (res) {
            btn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Confirm & Buy';
            spinner.classList.add('d-none');

            if (res.status === 'success') {
                const failMessages = ['no free phones', 'bad operator', 'not enough balance'];
                const raw = (res.raw || '').toLowerCase();

                if (failMessages.includes(raw)) {
                    Toastify({
                        text: raw || 'Failed to purchase number',
                        duration: 4000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                    }).showToast();

                    errorEl.textContent = raw || 'Failed to purchase number';
                    return;
                }

                // Real success
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                }).showToast();

                const newBalance = userBalance - price;
                $('#userBalance').val(newBalance.toFixed(2));
                $('.badge.bg-info').text(`Balance: ₦ ${newBalance.toFixed(2)}`);

                const modal = bootstrap.Modal.getInstance(document.getElementById('purchaseModal'));
                if (modal) modal.hide();

                setTimeout(() => {
                    window.location.href = '/views/customer/services/sms/orders';
                }, 500);
            } else {
                Toastify({
                    text: res.message || 'Something went wrong',
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }).showToast();

                errorEl.textContent = res.message || 'Something went wrong';
            }
        },

        error: function () {
            btn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Confirm & Buy';
            spinner.classList.add('d-none');

            Toastify({
                text: 'Something went wrong',
                duration: 4000,
                gravity: 'top',
                position: 'right',
                backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
            }).showToast();

            errorEl.textContent = 'Something went wrong';
        },
    });
});
