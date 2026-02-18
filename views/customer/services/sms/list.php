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


$resellerId = null;

$stmt = $conn->prepare("
    SELECT reseller_id
    FROM reseller_customers
    WHERE customer_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($resellerId);
$stmt->fetch();
$stmt->close();

$resellerId = $resellerId ?? 0;

$sql = "
    SELECT
        s.*,
        rsp.reseller_price
    FROM sms_provider_services s
    LEFT JOIN reseller_sms_services_prices rsp
        ON rsp.service_id = s.id
        AND rsp.reseller_id = ?
    ORDER BY s.country, s.service_code, s.operator
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $resellerId);
$stmt->execute();
$result = $stmt->get_result();

$services = [];

while ($row = $result->fetch_assoc()) {

    // Use reseller price if exists
    if ($row['reseller_price'] !== null) {
        $row['final_price'] = $row['reseller_price'];
    } else {
        $row['final_price'] = $row['base_price'];
    }

    $services[] = $row;
}

$stmt->close();





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SMS Services | Customers</title>
    <link rel="shortcut icon" href="./../../images/logo-png.png" type="image/x-icon">
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

        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="d-flex justify-content-end align-items-center mb-3">
            <span class="badge bg-info text-dark">
                Balance: ₦ <?= number_format($balance ?? 0, 2) ?>
            </span>
        </div>


        <input type="hidden" id="userBalance" value="<?= $balance ?? 0 ?>">


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
            <div class="container mt-4">

                <!-- Section Title -->
                <div class="d-flex align-items-center mb-4" style="animation: fadeIn 0.8s ease-out;">
                    <div style="width: 5px; height: 30px; background: #0d6efd; border-radius: 10px; margin-right: 15px;"></div>
                    <h4 class="mb-0" style="font-weight: 800; color: #c9d0d6; letter-spacing: -0.5px;">
                        Available SMS Services
                    </h4>
                </div>

                <div id="buyError" class="text-danger small mt-2 d-flex align-items-center" style="font-weight: 500;"></div>

                <div class="row g-4">
                    <?php foreach ($services as $index => $s):
                        $delay = $index * 0.05;
                        $selling_price = $s['final_price'];
                    ?>

                        <div class="col-md-6 col-lg-4 service-card"
                            data-country="<?= htmlspecialchars($s['country']) ?>"
                            data-operator="<?= htmlspecialchars($s['operator']) ?>"
                            data-service="<?= htmlspecialchars($s['service_code']) ?>"
                            style="animation: zoomIn 0.5s ease forwards; animation-delay: <?= $delay ?>s; opacity: 0;">

                            <div class="card h-100 border-0"
                                style="border-radius: 20px; background: #ffffff;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
                    position: relative; overflow: hidden;"
                                onmouseover="this.style.transform='translateY(-8px)';
                                 this.style.boxShadow='0 20px 40px rgba(0,0,0,0.1)';"
                                onmouseout="this.style.transform='translateY(0)';
                                this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)';">

                                <!-- Decorative Circle -->
                                <div style="position:absolute; top:-20px; right:-20px;
                        width:100px; height:100px;
                        background: rgba(13,110,253,0.03);
                        border-radius:50%;"></div>

                                <div class="card-body p-4" style="position:relative; z-index:1;">

                                    <!-- Icon + ID -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div style="width:45px; height:45px;
                                background:#f0f7ff;
                                border-radius:12px;
                                display:flex;
                                align-items:center;
                                justify-content:center;">
                                            <i class="fa-solid fa-sim-card text-primary"></i>
                                        </div>
                                        <span class="badge bg-dark">ID: <?= $s['id'] ?></span>
                                    </div>

                                    <!-- Country -->
                                    <h6 class="fw-bold mb-2" style="color:#2c3e50;">
                                        <?= htmlspecialchars($s['country']) ?>
                                    </h6>

                                    <!-- Service Code & Operator -->
                                    <div class="mb-3">
                                        <span class="badge bg-info text-dark me-1">
                                            <?= htmlspecialchars($s['service_code']) ?>
                                        </span>
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($s['operator']) ?>
                                        </span>
                                    </div>

                                    <!-- Price Section -->
                                    <div class="mb-3">
                                        <small style="color:#95a5a6;">Service Price</small>
                                        <h5 style="font-weight:800; color:#2ecc71; margin:0;">
                                            ₦ <?= number_format($s['final_price'], 2) ?>
                                        </h5>
                                    </div>

                                    <!-- Available Count -->
                                    <div class="py-2 px-3 mb-3"
                                        style="background:#f8f9fa; border-radius:12px;">
                                        <small style="color:#7f8c8d;">Available Numbers</small>
                                        <div style="font-weight:700; color:#34495e;">
                                            <?= number_format($s['count']) ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Button -->
                                <div class="card-footer bg-transparent border-0 p-4 pt-0">
                                    <button class="btn btn-primary w-100 buy-service-btn"
                                        style="border-radius:12px; padding:12px; font-weight:700;
    background: linear-gradient(135deg,#0d6efd 0%,#0052cc 100%);
    border:none; box-shadow:0 4px 15px rgba(13,110,253,0.2);
    transition: all 0.3s;"
                                        data-service-id="<?= $s['id'] ?>"
                                        data-country="<?= htmlspecialchars($s['country']) ?>"
                                        data-operator="<?= htmlspecialchars($s['operator']) ?>"
                                        data-service="<?= htmlspecialchars($s['service_code']) ?>"
                                        data-price="<?= htmlspecialchars($s['final_price']) ?>">

                                        <span class="btn-text">
                                            <i class="fa-solid fa-basket-shopping me-2"></i> Order Now
                                        </span>

                                        <span class="spinner-border spinner-border-sm d-none"></span>
                                    </button>

                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            </div>

            <style>
                @keyframes zoomIn {
                    from {
                        opacity: 0;
                        transform: scale(0.95);
                    }

                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }

                    to {
                        opacity: 1;
                    }
                }
            </style>

        </div>
    </div>



    <?php
    $active = 'sms';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>

    <script src="/js/customer/manage-sms.js"></script>
