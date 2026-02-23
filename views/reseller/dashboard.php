<?php
session_start();
require_once 'helpers/session.php';
require_once 'include/config.php';

// Restrict access to resellers only
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'reseller') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Reseller';




// Withdraw Requests
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reseller_withdraw_requests WHERE reseller_id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalRequests = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reseller_withdraw_requests WHERE reseller_id=? AND status='pending'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$pendingRequests = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reseller_withdraw_requests WHERE reseller_id=? AND status='approved'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$approvedRequests = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// Wallet Balance
$stmt = $conn->prepare("SELECT balance FROM user_data WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$balance = $stmt->get_result()->fetch_assoc()['balance'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reseller_customers WHERE reseller_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalCustomers = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Dashboard | <?php echo htmlspecialchars($userName); ?></title>
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/reseller_dashboard.css">
</head>

<body>
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



        <!-- Stats Cards -->
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card text-center p-3 shadow-sm">
                    <i class="fa-solid fa-users text-primary fa-2x mb-2"></i>
                    <h6>Total Customers</h6>
                    <h4><?= $totalCustomers ?></h4>
                </div>
            </div>


            <div class="col-md-3">
                <div class="card text-center p-3 shadow-sm">
                    <i class="fa-solid fa-money-bill-transfer text-warning fa-2x mb-2"></i>
                    <h6>Total Withdraw Requests</h6>
                    <h4><?= $totalRequests ?></h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center p-3 shadow-sm">
                    <i class="fa-solid fa-clock text-danger fa-2x mb-2"></i>
                    <h6>Pending Withdraw</h6>
                    <h4><?= $pendingRequests ?></h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center p-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-success fa-2x mb-2"></i>
                    <h6>Approved Withdraw</h6>
                    <h4><?= $approvedRequests ?></h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center p-3 shadow-sm">
                    <i class="fa-solid fa-circle-xmark text-danger fa-2x mb-2"></i>
                    <h6>Rejected Withdraw</h6>
                    <h4>
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reseller_withdraw_requests WHERE reseller_id=? AND status='rejected'");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $rejectedRequests = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
                        echo $rejectedRequests;
                        ?>
                    </h4>
                </div>
            </div>

        </div>

        <!-- Quick Actions -->
        <h6 class="fw-bold mt-4 mb-3">Quick Actions</h6>
        <div class="row g-3">
            <div class="col-md-4 text-center">
                <a href="/views/reseller/customer/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-gradient rounded shadow-sm">
                        <div class="icon-box text-white mb-2">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="text-white">Manage Customers</div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 text-center">
                <a href="/views/reseller/services/smm/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-gradient rounded shadow-sm">
                        <div class="icon-box text-white mb-2">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="text-white">Manage SMM Services</div>
                    </div>
                </a>
            </div>



            <div class="col-md-4 text-center">
                <a href="/views/reseller/services/sms/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-gradient rounded shadow-sm">
                        <div class="icon-box text-white mb-2">
                            <i class="fa-solid fa-sms"></i>
                        </div>
                        <div class="text-white">Manage SMS Services</div>
                    </div>
                </a>
            </div>





            <div class="col-md-4 text-center">
                <a href="/views/reseller/bank/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-gradient rounded shadow-sm">
                        <div class="icon-box text-white mb-2">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div class="text-white">Manage Bank Account</div>
                    </div>
                </a>
            </div>


            <div class="col-md-4 text-center">
                <a href="/views/reseller/withdraw/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-gradient rounded shadow-sm">
                        <div class="icon-box text-white mb-2">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </div>
                        <div class="text-white">Withdraw Balance</div>
                    </div>
                </a>
            </div>



            <div class="col-md-4 text-center">
                <a href="/views/reseller/services/smm/orders" class="text-decoration-none">
                    <div class="service-card p-3 bg-gradient rounded shadow-sm">
                        <div class="icon-box text-white mb-2">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <div class="text-white">View SMM Orders</div>
                    </div>
                </a>
            </div>


            <div class="col-md-4 text-center">
                <a href="/views/reseller/services/sms/orders" class="text-decoration-none">
                    <div class="service-card p-3 bg-gradient rounded shadow-sm">
                        <div class="icon-box text-white mb-2">
                            <!-- SMS icon -->
                            <i class="fa-solid fa-sms fa-2x"></i>
                        </div>
                        <div class="text-white">View SMS Orders</div>
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
