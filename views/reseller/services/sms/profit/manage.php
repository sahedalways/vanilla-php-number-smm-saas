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

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Default profit
$profit_percentage = 0;


if ($userId) {
    $stmt = $conn->prepare("SELECT profit_percentage FROM reseller_sms_profit_settings WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($profit_percentage);
    $stmt->fetch();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profit | Reseller Dashboard</title>
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" href="/css/manage_profit.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>
    <div class="main-wrapper p-3">
        <!-- Header -->
        <?php
        include __DIR__ . '/../../../components/header.php';
        ?>
        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">


        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card p-5 shadow-lg rounded-4 border-0">
                        <h4 class="fw-bold mb-4 text-center text-success">Manage SMS Profit</h4>

                        <form id="profitForm">
                            <div class="mb-4">
                                <label for="profit_percentage" class="form-label fw-semibold">
                                    Profit Percentage <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                    id="profit_percentage"
                                    name="profit_percentage"
                                    class="form-control form-control-lg border-success"
                                    placeholder="Enter profit percentage"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    required value="<?= htmlspecialchars($profit_percentage); ?>">
                                <div id="error-profit_percentage" class="text-danger small mt-1"></div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle me-2"></i> Update Profit
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>





        <?php
        $active = '';
        include __DIR__ . '/../../../components/bottom-nav.php';
        ?>

    </div>

    <script src="/js/reseller/manage-profit.js"></script>


</body>

</html>
