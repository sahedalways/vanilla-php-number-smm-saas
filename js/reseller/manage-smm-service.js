document.addEventListener('DOMContentLoaded', function () {
    // Use event delegation - listen for clicks on the document or a parent container
    $(document).on('click', '.set-custom-price-btn', function () {
        const btn = this;
        const serviceId = btn.dataset.serviceId;
        const serviceName = btn.dataset.serviceName;
        const basePrice = parseFloat(btn.dataset.basePrice);
        const resellerPrice = parseFloat(btn.dataset.resellerPrice);

        // Fill modal fields
        document.getElementById('modalServiceId').value = serviceId;
        document.getElementById('modalServiceName').value = serviceName;
        document.getElementById('modalBasePrice').value = `₦ ${basePrice.toFixed(2)}`;
        document.getElementById('modalResellerPrice').value = `₦ ${resellerPrice.toFixed(2)}`;
        document.getElementById('modalCustomPrice').value = resellerPrice;

        // Clear previous error
        const errorEl = document.getElementById('customPriceError');
        if (errorEl) errorEl.textContent = '';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('customPriceModal'));
        modal.show();
    });

    $('#saveCustomPrice')
        .off('click')
        .on('click', function (e) {
            e.preventDefault();

            const serviceId = document.getElementById('modalServiceId').value;
            const priceInput = document.getElementById('modalCustomPrice');
            const errorEl = document.getElementById('modalPriceError');
            const btn = this;
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const basePriceText = document.getElementById('modalBasePrice').value;
            const basePrice = parseFloat(basePriceText.replace('₦ ', ''));

            errorEl.textContent = '';

            const newPrice = parseFloat(priceInput.value);

            if (isNaN(newPrice)) {
                errorEl.textContent = 'Please enter a valid number.';
                return;
            }

            if (newPrice < basePrice) {
                errorEl.textContent = `Price cannot be less than base price (₦ ${basePrice.toFixed(2)})`;
                return;
            }

            // Disable button + show spinner
            btn.disabled = true;
            btnText.textContent = 'Setting...';
            spinner.classList.remove('d-none');

            const csrfToken = document.getElementById('csrf_token').value;

            console.log(serviceId);

            // Ajax request
            $.ajax({
                url: '/controllers/reseller/services/smm/set-custom-price',
                method: 'POST',
                data: {
                    service_id: serviceId,
                    custom_price: newPrice,
                    csrf_token: csrfToken,
                },
                dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Set';
                    spinner.classList.add('d-none');

                    if (res.status === 'success') {
                        Toastify({
                            text: res.message,
                            duration: 4000,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                        }).showToast();

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(
                            document.getElementById('customPriceModal')
                        );
                        modal.hide();

                        // Optional: reload page or update price in card dynamically
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        errorEl.textContent = res.message || 'Something went wrong.';
                    }
                },
                error: function () {
                    btn.disabled = false;
                    btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Set';
                    spinner.classList.add('d-none');
                    errorEl.textContent = 'Something went wrong.';
                },
            });
        });
});
