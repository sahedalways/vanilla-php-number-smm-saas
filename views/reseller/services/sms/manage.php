<?php
require_once 'helpers/session.php';
require_once 'include/config.php';
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'reseller') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Reseller';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch SMS services for reseller
$servicesQuery = $conn->query("SELECT * FROM sms_provider_services ORDER BY country, service_code, operator");
$services = $servicesQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage SMS Services | Reseller</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <?php include __DIR__ . '/../../components/header.php'; ?>
        <h4 class="mb-4">Manage SMS Services</h4>
        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
        <!-- Filter Section -->
        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <select id="filterCountry" class="form-select">
                    <option value="">All Countries</option>
                    <?php
                    $countries = array_unique(array_column($services, 'country'));
                    foreach ($countries as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars(ucfirst($c)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterOperator" class="form-select">
                    <option value="">All Operators</option>
                    <?php
                    $operators = array_unique(array_column($services, 'operator'));
                    foreach ($operators as $o): ?>
                        <option value="<?= htmlspecialchars($o) ?>"><?= htmlspecialchars($o) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterService" class="form-select">
                    <option value="">All Services</option>
                    <?php
                    $serviceCodes = array_unique(array_column($services, 'service_code'));
                    foreach ($serviceCodes as $sc): ?>
                        <option value="<?= htmlspecialchars($sc) ?>"><?= htmlspecialchars($sc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button id="resetFilters" class="btn btn-secondary w-100">Reset Filters</button>
            </div>
        </div>

        <!-- Services Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="smsServicesTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Country</th>
                        <th>Service Code</th>
                        <th>Operator</th>
                        <th>Base Price</th>
                        <th>Selling Price</th>
                        <th>Count</th>

                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $s): ?>
                        <?php
                        $stmt = $conn->prepare("SELECT reseller_price FROM reseller_sms_services_prices WHERE reseller_id = ? AND service_id = ?");
                        $stmt->bind_param("ii", $userId, $s['id']);
                        $stmt->execute();
                        $stmt->bind_result($reseller_price);
                        $stmt->fetch();
                        $stmt->close();

                        // Use reseller price if exists, else use base price
                        $selling_price = $reseller_price ? $reseller_price : $s['base_price'];
                        ?>

                        <tr data-country="<?= htmlspecialchars($s['country']) ?>"
                            data-operator="<?= htmlspecialchars($s['operator']) ?>"
                            data-service="<?= htmlspecialchars($s['service_code']) ?>">
                            <td><?= $s['id'] ?></td>
                            <td><?= htmlspecialchars($s['country']) ?></td>
                            <td><?= htmlspecialchars($s['service_code']) ?></td>
                            <td><?= htmlspecialchars($s['operator']) ?></td>


                            <td>₦ <?= number_format($s['base_price'], 2) ?></td>
                            <td>₦ <?= number_format($selling_price, 2) ?></td>
                            <td><?= $s['count'] ?></td>

                            <td>
                                <button class="btn btn-sm btn-success editPriceBtn"
                                    data-service-id="<?= $s['id'] ?>"
                                    data-selling-price="<?= $selling_price ?>"
                                    data-base-price="<?= $s['base_price'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Price Modal -->
    <div class="modal fade" id="customPriceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Edit Reseller Price</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalServiceId">
                    <div class="mb-3">
                        <label class="text-dark">Base Price</label>
                        <input type="text" id="modalBasePrice" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="text-dark">Selling Price</label>
                        <input type="number" min="0" step="0.01" id="modalResellerPrice" class="form-control">
                    </div>
                    <div id="modalPriceError" class="text-danger small"></div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="savePriceBtn" class="btn btn-success btn-create px-5">
                        <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Set</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </div>


            </div>
        </div>
    </div>


    <?php
    $active = 'sms';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>


    <script>
        // Filter Table
        function filterTable() {
            let country = $('#filterCountry').val();
            let operator = $('#filterOperator').val();
            let service = $('#filterService').val();

            $('#smsServicesTable tbody tr').each(function() {
                let show = true;
                if (country && $(this).data('country') !== country) show = false;
                if (operator && $(this).data('operator') !== operator) show = false;
                if (service && $(this).data('service') !== service) show = false;
                $(this).toggle(show);
            });
        }

        $('#filterCountry, #filterOperator, #filterService').on('change', filterTable);
        $('#resetFilters').on('click', function() {
            $('#filterCountry, #filterOperator, #filterService').val('');
            filterTable();
        });


        // Edit Price Modal
        $('.editPriceBtn').on('click', function() {
            let row = $(this).closest('tr');

            const serviceId = this.dataset.serviceId;

            const basePrice = parseFloat(this.dataset.basePrice);
            const sellingPrice = parseFloat(this.dataset.sellingPrice);
            const resellerPrice = parseFloat(this.dataset.resellerPrice);

            $('#modalServiceId').val(serviceId);
            $('#modalBasePrice').val('₦ ' + basePrice.toFixed(2));
            $('#modalResellerPrice').val(sellingPrice.toFixed(2));
            $('#modalPriceError').text('');

            $('#customPriceModal').modal('show');
        });

        // Save Custom Price
        $('#savePriceBtn').on('click', function() {
            const btn = this;
            const spinner = $(btn).find('.spinner-border')[0];
            const btnText = $(btn).find('.btn-text')[0];
            const errorEl = $('#modalPriceError');

            let serviceId = $('#modalServiceId').val();
            let newPrice = parseFloat($('#modalResellerPrice').val());
            let basePrice = parseFloat($('#modalBasePrice').val().replace(/[^0-9.-]+/g, ""));
            const csrfToken = $('#csrf_token').val();

            // Validation
            if (isNaN(newPrice) || newPrice <= 0) {
                errorEl.text('Please enter a valid price.');
                return;
            }
            if (newPrice < basePrice) {
                errorEl.text('Selling price cannot be less than base price.');
                return;
            }
            errorEl.text('');

            // Disable button and show spinner
            btn.disabled = true;
            if (btnText) btnText.innerHTML = 'Saving...';
            if (spinner) spinner.classList.remove('d-none');
            console.log({
                serviceId,
                newPrice,
                csrfToken
            });
            $.ajax({
                url: '/controllers/reseller/services/sms/set-custom-price',
                method: 'POST',
                data: {
                    service_id: serviceId,
                    custom_price: newPrice,
                    csrf_token: csrfToken
                },
                dataType: 'json',
                success: function(res) {
                    btn.disabled = false;
                    if (btnText) btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Set';
                    if (spinner) spinner.classList.add('d-none');

                    if (res.status === 'success') {
                        Toastify({
                            text: res.message,
                            duration: 4000,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                        }).showToast();

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('customPriceModal'));
                        if (modal) modal.hide();

                        // Update table dynamically
                        const row = $('#smsServicesTable tbody tr').filter(function() {
                            return $(this).data('id') == serviceId;
                        });
                        row.find('td:eq(6)').text('₦ ' + newPrice.toFixed(2));

                        setTimeout(() => {
                            location.reload();
                        }, 500);

                    } else {
                        errorEl.text(res.message || 'Something went wrong.');
                    }
                },
                error: function() {
                    btn.disabled = false;
                    if (btnText) btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Set';
                    if (spinner) spinner.classList.add('d-none');
                    errorEl.text('Something went wrong.');
                }
            });
        });
    </script>
