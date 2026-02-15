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
$userName = $_SESSION['name'] ?? 'User';

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
    <title>Customer Dashboard | Allsmsverify</title>
    <link rel="shortcut icon" href="./images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/customer_dashboard.css">
</head>

<body>
    <div class="main-wrapper p-3">

        <?php
        include __DIR__ . '/components/header.php';
        ?>

        <div class="wallet-card mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-wallet me-2"></i> Main Balance</span>
                <div class="bg-dark bg-opacity-50 rounded-pill p-1 d-flex gap-1" style="font-size: 0.8rem;">

                    <span class="px-3 py-1 text-white">NGN</span>
                </div>
            </div>
            <div class="balance-amount"> ₦<?php echo number_format($balance, 2); ?></div>
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-light w-100 py-2 fw-bold text-primary"><i class="fa-solid fa-plus me-1"></i> Top Up</button>
                </div>
                <div class="col-6">
                    <button class="btn btn-primary w-100 py-2 border-0" style="background: rgba(255,255,255,0.2);"><i class="fa-solid fa-clock-rotate-left me-1"></i> History</button>
                </div>
            </div>
        </div>

        <h6 class="fw-bold mb-3">Quick Services</h6>
        <div class="row g-3">



            <!-- Services -->
            <div class="col-4 text-center">
                <a href="/views/customer/services/smm/manage" class="text-decoration-none">
                    <div class="service-card p-3 shadow-sm rounded border h-100">
                        <div class="icon-box text-success mb-2" style="font-size: 24px;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="fw-semibold small">SMM Services</div>
                        <div class="text-light" style="font-size: 0.7rem;">
                            Manage available digital services
                        </div>
                    </div>
                </a>
            </div>

            <!-- Phone Numbers -->
            <div class="col-4 text-center">
                <div class="service-card p-3 shadow-sm rounded border h-100">
                    <div class="icon-box text-warning mb-2" style="font-size: 24px;">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div class="fw-semibold small">Phone Numbers</div>
                    <div class="text-light" style="font-size: 0.7rem;">
                        View and manage phone numbers
                    </div>
                </div>
            </div>


            <div class="col-4 text-center">
                <a href="/views/customer/services/smm/orders" class="text-decoration-none">
                    <div class="service-card p-3 shadow-sm rounded border h-100">
                        <div class="icon-box text-info mb-2" style="font-size: 24px;">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <div class="fw-semibold small">My SMM Orders</div>
                        <div class="text-light" style="font-size: 0.7rem;">
                            View all purchased SMM services
                        </div>
                    </div>
                </a>
            </div>

        </div>

    </div>



    <?php
    $active = 'dashboard';
    include __DIR__ . '/components/bottom-nav.php';
    ?>

</body>

</html>
