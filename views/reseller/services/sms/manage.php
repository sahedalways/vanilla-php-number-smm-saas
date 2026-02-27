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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Services | Reseller</title>
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <?php include __DIR__ . '/../../components/header.php'; ?>

        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
        <!-- Filter Section -->

        <div class="container mt-4">

            <input type="hidden" id="userBalance" value="<?= $balance ?? 0 ?>">


            <h4 class="mb-4"> SMS Services</h4>
            <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
            <!-- Filter Section -->
            <div class="row mb-3 g-2 justify-content-center align-items-center">
                <div class="col-md-3">
                    <select id="filterCountry" class="form-select select2-search">
                        <option value="" disabled selected>Select a Country</option>
                        <?php
                        include __DIR__ . '/../../../../utils/countries.php';
                        foreach ($countries as $name => $slug): ?>
                            <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <style>
                    .select2-container .select2-selection--single {
                        height: 38px;

                        padding: 5px 12px;
                    }

                    /* Dropdown arrow er positioning thik kora */
                    .select2-container--default .select2-selection--single .select2-selection__arrow {
                        height: 43px;
                        right: 10px;
                    }

                    /* Selected text er positioning */
                    .select2-container--default .select2-selection--single .select2-selection__rendered {
                        line-height: 28px;
                    }
                </style>

                <div class="col-md-3">
                    <select id="filterService" class="form-select select2-search">
                        <option value="" disabled selected>Select a Service</option>

                        <?php
                        include __DIR__ . '/../../../../utils/smsServices.php';
                        foreach ($smsServices as $name => $value): ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button id="searchFilters" class="btn btn-primary w-100">Search</button>
                </div>

                <div class="col-md-3">
                    <button id="resetFilters" class="btn btn-secondary w-100">Reset Filters</button>
                </div>
            </div>

            <!-- Services Table -->
            <div class="table-responsive">
                <div class="container mt-4">

                    <!-- Section Title -->
                    <div id="servicesSection" class="d-none">
                        <div class="d-flex align-items-center mb-4" style="animation: fadeIn 0.8s ease-out;">
                            <div style="width: 5px; height: 30px; background: #0d6efd; border-radius: 10px; margin-right: 15px;"></div>
                            <h4 class="mb-0" style="font-weight: 800; color: #1f2020; letter-spacing: -0.5px;">
                                Available SMS Services
                            </h4>
                        </div>
                    </div>

                    <div id="initialPlaceholder" class="text-center text-muted my-5">
                        Search for SMS services to see results.
                    </div>

                    <div id="buyError" class="text-danger small mt-2 d-flex align-items-center" style="font-weight: 500;"></div>



                    <div id="filterLoader" class="text-center my-3 d-none">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Loading services...</div>
                    </div>


                    <div id="servicesContainer" class="row g-3 mt-3">
                        <!-- Cards will be injected here dynamically -->
                    </div>

                </div>



            </div>
        </div>
    </div>




    <?php
    $active = 'sms';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>


    <script>
        function fetchFilteredServices() {
            let country = $('#filterCountry').val();
            let service = $('#filterService').val();
            selectedCountry = country;
            $('#initialPlaceholder').addClass('d-none');

            $.ajax({
                url: '/controllers/reseller/services/sms/get-numbers',
                method: 'GET',
                data: {
                    country: country,
                    service: service,
                    csrf_token: $('#csrf_token').val(),
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#filterLoader').removeClass('d-none');
                    $('#servicesContainer').empty();
                },

                success: function(res) {
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
                                        qty === 0 ?
                                        'background: #cccccc; color: #666666; cursor: not-allowed;' :
                                        'background: #001f3f; color: white;';

                                    let resellerProfit = parseFloat(productInfo.reseller_profit ?? 0).toLocaleString();

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

                                        <!-- Reseller Profit Display -->
                                        <div class="row text-center">
                                            <div class="col-12">
                                                <p class="text-muted mb-1" style="font-size: 12px;">Reseller Profit</p>
                                                <h5 class="fw-bold mb-0 text-success">₦${resellerProfit}</h5>
                                            </div>
                                        </div>
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

                error: function() {
                    $('#filterLoader').addClass('d-none');

                    console.error('API call failed');
                },
            });
        }

        // Search button click
        $('#searchFilters').on('click', fetchFilteredServices);

        // Reset button click
        $('#resetFilters').on('click', function() {
            $('#filterCountry, #filterOperator').val('');
            $('.service-card').show();
            $('#servicesContainer').empty();
            $('#initialPlaceholder').removeClass('d-none');
            $('#servicesSection').addClass('d-none');
        });
    </script>


    <script>
        $(document).ready(function() {
            // Initialize both dropdowns
            $('#filterService, #filterCountry').select2({
                placeholder: 'Search...',
                allowClear: true,
                width: '100%'
            });


            $('#filterService').select2({
                placeholder: 'Search for a service...',
                allowClear: true,
                width: '100%'
            });

            $('#filterCountry').select2({
                placeholder: 'Search for a country...',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
