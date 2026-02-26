<?php
require_once 'helpers/session.php';
require_once 'include/config.php';
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'customer') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Customer';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];




$balance = 0.0;

if ($userId) {
    $stmt = $conn->prepare("SELECT balance FROM user_data WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Services | Customers</title>
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
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


    <div class="main-wrapper p-3">

        <?php include __DIR__ . '/../../components/header.php'; ?>

        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="d-flex justify-content-end align-items-center mb-3">
            <span class="badge bg-info text-dark">
                Balance: ₦ <?= number_format($balance ?? 0, 2) ?>
            </span>
        </div>



        <div class="container mt-4">

            <input type="hidden" id="userBalance" value="<?= $balance ?? 0 ?>">


            <h4 class="mb-4"> SMS Services</h4>
            <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
            <!-- Filter Section -->
            <div class="row mb-3 g-2 justify-content-center align-items-center">
                <div class="col-md-3">
                    <select id="filterCountry" class="form-select">
                        <option value="" disabled selected>Select a Country</option>
                        <option value="any">Any</option>
                        <?php
                        include __DIR__ . '/../../../../utils/countries.php';
                        foreach ($countries as $name => $slug): ?>
                            <option value="<?= htmlspecialchars($slug) ?>">
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="filterOperator" class="form-select">
                        <option value="" disabled selected>Select a Operator</option>
                        <option value="any">Any</option>
                        <?php
                        include __DIR__ . '/../../../../utils/operators.php';
                        foreach ($operators as $name => $value): ?>
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

        <!-- Purchase Modal -->
        <div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true" data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px;">
                    <div class="modal-header" style="background-color: #001f3f; color: #fff; border-radius: 20px 20px 0 0;">
                        <h5 class="modal-title" id="purchaseModalLabel">Purchase SMS Service</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Product:</label>
                            <select id="modalProduct" class="form-select">
                                <option value="" disabled selected>Select an Product</option>

                                <?php
                                include __DIR__ . '/../../../../utils/smsServices.php';
                                foreach ($smsServices as $name => $value): ?>
                                    <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-danger small d-none" id="modalProductError"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Country:</label>
                            <input type="text" id="modalCountry" class="form-control" readonly>
                            <span class="text-danger small d-none" id="modalCountryError"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Operator:</label>
                            <input type="text" id="modalServiceCode" class="form-control" readonly>
                            <span class="text-danger small d-none" id="modalOperatorError"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Category:</label>
                            <input type="text" id="modalCategory" class="form-control" readonly>
                        </div>



                        <div class="mb-3">
                            <label class="form-label fw-bold">Unit Price:</label>
                            <input type="text" id="modalPrice" class="form-control" readonly>
                            <span class="text-danger small d-none" id="modalPriceError"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmPurchaseBtn" class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                            <span class="btn-text">
                                <i class="fa-solid fa-circle-check me-1"></i> Confirm & Buy
                            </span>
                            <span class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>




        <?php
        $active = 'sms';
        include __DIR__ . '/../../components/bottom-nav.php';
        ?>

        <script src="/js/customer/manage-sms.js"></script>

    </div>
</body>

</html>
