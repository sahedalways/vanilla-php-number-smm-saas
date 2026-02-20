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
    <title>Manage SMM Services | Foreign sms</title>
    <link rel="shortcut icon" href="<?php echo $WEBSITE_URL; ?>/images/logo-png.png" type="image/x-icon">
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


        <?php
        include __DIR__ . '/../components/header.php';
        ?>


        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">


        <div class="container mt-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-weight: 600; font-size: 1.1rem; display: flex; align-items: center;">
                    <i class="fa-solid fa-layer-group text-primary" style="margin-right: 0.5rem;"></i>
                    Available Services
                </h5>

                <button class="btn btn-sm btn-outline-primary d-flex align-items-center" onclick="loadServices()" style="gap:0.3rem;">
                    <i class="fa-solid fa-rotate" style="font-size: 0.9rem;"></i> Refresh
                </button>
            </div>

            <div class="row g-3" id="servicesList">

                <div class="text-center text-muted w-100" style="padding: 3rem 0; font-style: italic;">
                    Loading services...
                </div>

            </div>

        </div>




        <?php
        $active = 'smm';
        include __DIR__ . '/../components/bottom-nav.php';
        ?>

    </div>

    <script src="/js/customer/manage-smm-services.js"></script>


</body>

</html>
