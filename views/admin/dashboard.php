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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Allsmsverify</title>
    <link rel="shortcut icon" href="<?php echo $WEBSITE_URL; ?>/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">

</head>

<body>
    <div class="main-wrapper p-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <small class="text-light">Welcome back,</small>
                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($userName); ?></h5>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary border-0 rounded-circle text-white">
                    <i class="fa-regular fa-sun"></i>
                </button>
                <button class="btn btn-outline-secondary border-0 rounded-circle text-white position-relative">
                    <i class="fa-regular fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;"><?php echo $pendingRequests; ?></span>
                </button>
                <!-- Logout Button -->
                <a href="/logout" class="btn btn-outline-danger border-0 rounded-pill fw-bold ms-2">Logout</a>
            </div>
        </div>

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
                    <h4>0</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-money-bill-wave text-success fa-2x mb-2"></i>
                    <h6>Total Revenue</h6>
                    <h4>₦0.00</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-calendar text-info fa-2x mb-2"></i>

                    <h6>Monthly Revenue</h6>
                    <h4>₦0.00</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-calendar text-danger fa-2x mb-2"></i>
                    <h6>Yearly Revenue</h6>
                    <h4>₦0.00</h4>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-money-bill-transfer text-danger fa-2x mb-2"></i>
                    <h6>Total W. Requests</h6>
                    <h4>0</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-3">
                    <i class="fa-solid fa-clock text-warning fa-2x mb-2"></i>
                    <h6>Pending Withdraw</h6>
                    <h4>0</h4>
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
                <div class="service-card p-3 bg-light rounded">
                    <div class="icon-box text-success mb-2"><i class="fa-solid fa-wallet"></i></div>
                    <div style="font-size: 0.85rem;" class="text-dark">Manage Balance</div>
                </div>
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

        <!-- Bottom Navigation -->
        <nav class="bottom-nav mt-4 mb-2">
            <a href="#" class="nav-item active">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-wallet"></i>
                <span>Balance</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-cogs"></i>
                <span>Services</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-file-lines"></i>
                <span>Logs</span>
            </a>
        </nav>

    </div>



</body>

</html>
