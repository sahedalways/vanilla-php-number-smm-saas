$(document).ready(function () {
    loadServices();
});

function loadServices() {
    $('#servicesList').html(`
        <div class="text-center text-muted">
            Loading services...
        </div>
    `);

    $.ajax({
        url: '/controllers/customer/services/smm/get-services',
        type: 'GET',

        success: function (response) {
            if (response.status === 'success') {
                renderServices(response.services);
            } else {
                $('#servicesList').html('Failed to load services');
            }
        },

        error: function () {
            $('#servicesList').html('API Error');
        },
    });
}

function renderServices(services) {
    let html = '';

    if (services.length === 0) {
        html = `
            <div class="text-center text-muted">
                No services found
            </div>
        `;
    } else {
        services.forEach((service) => {
            html += `

            <div class="col-6">

                <div class="card shadow-sm border-0">

                    <div class="card-body text-center">

                        <i class="fa-solid fa-share-nodes fa-2x text-primary mb-2"></i>

                        <div class="fw-semibold">
                            ${service.name}
                        </div>

                        <div class="text-muted small">
                            Min: ${service.min}
                            <br>
                            Max: ${service.max}
                        </div>

                        <div class="text-success fw-semibold mt-1 mb-2">
                            ₦${service.price.toFixed(2)}
                        </div>

                        <button class="btn btn-primary btn-sm"
                            onclick="buyService(${service.id})">

                            Buy Now

                        </button>

                    </div>

                </div>

            </div>

            `;
        });
    }

    $('#servicesList').html(html);
}

function buyService(serviceId) {
    $.ajax({
        url: '/controllers/customer/services/smm/buy-service',
        type: 'POST',

        data: {
            service_id: serviceId,
            csrf_token: $('#csrf_token').val(),
        },

        success: function (response) {
            if (response.status === 'success') {
                Toastify({
                    text: 'Order placed. ID: ' + response.order_id,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'green',
                }).showToast();
            } else {
                alert('Failed');
            }
        },
    });
}
