<?php
require_once 'helpers/session.php';
require_once 'include/config.php';
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Admin';


// Total Customers
$totalCustomersQuery = $conn->query("SELECT COUNT(*) as total FROM user_data WHERE type='customer'");
$totalCustomers = $totalCustomersQuery->fetch_assoc()['total'] ?? 0;

// Total Resellers
$totalResellersQuery = $conn->query("SELECT COUNT(*) as total FROM user_data WHERE type='reseller'");
$totalResellers = $totalResellersQuery->fetch_assoc()['total'] ?? 0;


$totalRequests = $conn->query("SELECT COUNT(*) as cnt FROM reseller_withdraw_requests")->fetch_assoc()['cnt'];
$pendingRequests = $conn->query("SELECT COUNT(*) as cnt FROM reseller_withdraw_requests WHERE status='pending'")->fetch_assoc()['cnt'];
$approvedRequests = $conn->query("SELECT COUNT(*) as cnt FROM reseller_withdraw_requests WHERE status='approved'")->fetch_assoc()['cnt'];



// Total admin income
$stmt = $conn->prepare("
    SELECT
        (SELECT IFNULL(SUM(admin_profit),0) FROM sms_orders WHERE status='FINISHED') +
        (SELECT IFNULL(SUM(admin_profit),0) FROM smm_orders WHERE status='Completed') AS total
");
$stmt->execute();
$totalRevenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Monthly admin income (current month)
$stmt = $conn->prepare("
    SELECT
        (SELECT IFNULL(SUM(admin_profit),0) FROM sms_orders
            WHERE status='FINISHED'
              AND MONTH(created_at) = MONTH(NOW())
              AND YEAR(created_at) = YEAR(NOW())
        ) +
        (SELECT IFNULL(SUM(admin_profit),0) FROM smm_orders
            WHERE status='Completed'
              AND MONTH(created_at) = MONTH(NOW())
              AND YEAR(created_at) = YEAR(NOW())
        ) AS total
");
$stmt->execute();
$monthlyRevenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Yearly admin income (current year)
$stmt = $conn->prepare("
    SELECT
        (SELECT IFNULL(SUM(admin_profit),0) FROM sms_orders
            WHERE status='FINISHED'
              AND YEAR(created_at) = YEAR(NOW())
        ) +
        (SELECT IFNULL(SUM(admin_profit),0) FROM smm_orders
            WHERE status='Completed'
              AND YEAR(created_at) = YEAR(NOW())
        ) AS total
");
$stmt->execute();
$yearlyRevenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();


// Total combined
$totalServices = $servicesCount + $smsServicesCount;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Foreign sms</title>
    <link rel="shortcut icon" href="<?php echo $WEBSITE_URL; ?>/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">

</head>

<body>
    <div class="main-wrapper p-3">
        <!-- Header -->
        <?php
        include __DIR__ . '/components/header.php';
        ?>


        <!-- Stats Cards -->
        <div class="row g-3">

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-users text-primary fa-2x mb-2"></i>
                    <h6>Total Customers</h6>
                    <h4><?php echo $totalCustomers; ?></h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-user-tie text-success fa-2x mb-2"></i>
                    <h6>Total Resellers</h6>
                    <h4><?php echo $totalResellers; ?></h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-cogs text-warning fa-2x mb-2"></i>
                    <h6>Total Services</h6>
                    <h4><?= $totalServices ?></h4>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card text-center p-3">
                        <i class="fa-solid fa-money-bill-wave text-success fa-2x mb-2"></i>
                        <h6>Total Revenue</h6>
                        <h4>₦<?= number_format($totalRevenue, 2) ?></h4>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center p-3">
                        <i class="fa-solid fa-calendar text-info fa-2x mb-2"></i>
                        <h6>Monthly Revenue</h6>
                        <h4>₦<?= number_format($monthlyRevenue, 2) ?></h4>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center p-3">
                        <i class="fa-solid fa-calendar text-danger fa-2x mb-2"></i>
                        <h6>Yearly Revenue</h6>
                        <h4>₦<?= number_format($yearlyRevenue, 2) ?></h4>
                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-money-bill-transfer text-danger fa-2x mb-2"></i>
                    <h6>Total W. Requests</h6>
                    <h4><?= $totalRequests ?></h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-clock text-warning fa-2x mb-2"></i>
                    <h6>Pending Withdraw</h6>
                    <h4><?= $pendingRequests ?></h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-circle-check text-success fa-2x mb-2"></i>
                    <h6>Approved Withdraw</h6>
                    <h4><?= $approvedRequests ?></h4>
                </div>
            </div>



        </div>


        <!-- Quick Admin Actions -->
        <h6 class="fw-bold mb-3 mt-4">Admin Actions</h6>
        <div class="row g-3">
            <div class="col-3 text-center">
                <a href="/views/admin/resellers/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-light rounded">
                        <div class="icon-box text-primary mb-2">
                            <i class="fa-solid fa-user-tie fa-2x"></i>
                        </div>
                        <div style="font-size: 0.85rem;" class="text-dark">
                            Manage Resellers
                        </div>
                    </div>
                </a>
            </div>




            <div class="col-3 text-center">
                <a href="/views/admin/customer/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-light rounded">
                        <div class="icon-box text-primary mb-2">

                            <i class="fa-solid fa-users fa-2x"></i>
                        </div>
                        <div style="font-size: 0.85rem;" class="text-dark">
                            Manage Customers
                        </div>
                    </div>
                </a>
            </div>



            <div class="col-3 text-center">
                <a href="/views/admin/services/smm/list" class="text-decoration-none">
                    <div class="service-card p-3 bg-light rounded shadow-sm">
                        <div class="icon-box text-dark mb-2">
                            <i class="fa-solid fa-layer-group fa-2x"></i>
                        </div>
                        <div style="font-size: 0.85rem;" class="text-dark">SMM Services</div>
                    </div>
                </a>
            </div>

            <!-- SMS Services Card -->
            <div class="col-3 text-center">
                <a href="/views/admin/services/sms/list" class="text-decoration-none">
                    <div class="service-card p-3 bg-light rounded shadow-sm">
                        <div class="icon-box text-dark mb-2">
                            <i class="fa-solid fa-message fa-2x"></i>
                        </div>
                        <div style="font-size: 0.85rem;" class="text-dark">SMS Services</div>
                    </div>
                </a>
            </div>




            <div class="col-3 text-center">
                <a href="/views/admin/profit/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-light rounded">
                        <div class="icon-box text-dark mb-2">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <div style="font-size: 0.85rem;" class="text-dark">Manage Profit</div>
                    </div>
                </a>
            </div>

            <div class="col-3 text-center">
                <a href="/views/admin/withdraw/manage" class="text-decoration-none">
                    <div class="service-card p-3 bg-light rounded">
                        <div class="icon-box text-warning mb-2">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div style="font-size: 0.85rem;" class="text-dark">Manage Withdrawals</div>
                    </div>
                </a>
            </div>


            <div class="col-3 text-center">
                <div class="service-card p-3 bg-light rounded">
                    <div class="icon-box text-warning mb-2"><i class="fa-solid fa-cogs"></i></div>
                    <div style="font-size: 0.85rem;" class="text-dark">Manage Services</div>
                </div>
            </div>
            <div class="col-3 text-center">
                <div class="service-card p-3 bg-light rounded">
                    <div class="icon-box text-danger mb-2"><i class="fa-solid fa-file-lines"></i></div>
                    <div style="font-size: 0.85rem;" class="text-dark">View Logs</div>
                </div>
            </div>
        </div>

        <?php
        $active = 'dashboard';
        include __DIR__ . '/components/bottom-nav.php';
        ?>

    </div>



</body>

</html>
