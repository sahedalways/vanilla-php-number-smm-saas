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


$toast_msg = $_SESSION['success_msg'] ?? $_SESSION['error_msg'] ?? null;
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Foreign sms</title>
    <link rel="shortcut icon" href="./../../images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/customer_dashboard.css">
</head>

<body>
    <?php if ($toast_msg): ?>
        <script>
            Toastify({
                text: "<?php echo addslashes($toast_msg); ?>",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "<?php echo isset($_SESSION['success_msg']) ? '#4CAF50' : '#F44336'; ?>",
                stopOnFocus: true
            }).showToast();
        </script>
    <?php endif; ?>

    <div class="main-wrapper p-3">

        <?php
        include __DIR__ . '/components/header.php';
        ?>

        <div class="wallet-card mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-white"><i class="fa-solid fa-wallet me-2 text-white"></i> Main Balance</span>
                <div class="bg-dark bg-opacity-50 rounded-pill p-1 d-flex gap-1" style="font-size: 0.8rem;">

                    <span class="px-3 py-1 text-white">NGN</span>
                </div>
            </div>
            <div class="balance-amount text-white"> ₦<?php echo number_format($balance, 2); ?></div>
            <div class="row g-2">
                <div class="col-6">
                    <a href="/recharge" class="btn btn-light w-100 py-2 fw-bold text-primary">
                        <i class="fa-solid fa-plus me-1"></i> Top Up
                    </a>
                </div>
                <div class="col-6">
                    <a href="/transactions" class="btn btn-primary w-100 py-2 border-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> History
                    </a>
                </div>
            </div>
        </div>

        <h6 class="fw-bold mb-3">Quick Services</h6>
        <div class="row g-3">
            <div class="col-4 text-center">
                <a href="/views/customer/services/smm/manage" class="text-decoration-none text-white">
                    <div class="service-card p-3 shadow-sm rounded border border-secondary bg-dark h-100">
                        <div class="icon-box text-success mb-2" style="font-size: 24px;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="fw-semibold small">SMM Services</div>
                        <div class="text-secondary" style="font-size: 0.65rem;">
                            Manage digital services
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-4 text-center">
                <a href="/views/customer/services/sms/list" class="text-decoration-none text-white">
                    <div class="service-card p-3 shadow-sm rounded border border-secondary bg-dark h-100">
                        <div class="icon-box text-warning mb-2" style="font-size: 24px;">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div class="fw-semibold small">SMS Services</div>
                        <div class="text-secondary" style="font-size: 0.65rem;">
                            View SMS
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-4 text-center">
                <a href="/views/customer/services/smm/orders" class="text-decoration-none text-white">
                    <div class="service-card p-3 shadow-sm rounded border border-secondary bg-dark h-100">
                        <div class="icon-box text-info mb-2" style="font-size: 24px;">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <div class="fw-semibold small">My SMM Orders</div>
                        <div class="text-secondary" style="font-size: 0.65rem;">
                            Purchased SMM
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-4 text-center">
                <a href="/views/customer/services/sms/orders" class="text-decoration-none text-white">
                    <div class="service-card p-3 shadow-sm rounded border border-secondary bg-dark h-100">
                        <div class="icon-box text-info mb-2" style="font-size: 24px;">
                            <i class="fa-solid fa-sms"></i>
                        </div>
                        <div class="fw-semibold small">My SMS Orders</div>
                        <div class="text-secondary" style="font-size: 0.65rem;">
                            Purchased SMS
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
