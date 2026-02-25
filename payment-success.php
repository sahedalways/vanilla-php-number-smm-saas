<?php
session_start();
require_once 'helpers/session.php';
require_once 'include/config.php';


$user_data = $_SESSION['payment_user'] ?? null;

if (!$user_data) {
    header("Location: /dashboard");
    exit;
}


loginUser($user_data);


$userName = $_SESSION['name'] ?? 'User';


unset($_SESSION['payment_user']);

$dashboardURL = ($_SESSION['type'] === 'reseller')
    ? '/views/reseller/dashboard'
    : '/dashboard';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .success-card {
            max-width: 500px;
            margin: 100px auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 40px 30px;
        }

        .success-card i {
            font-size: 4rem;
            color: #28a745;
        }

        .btn-custom {
            min-width: 150px;
        }
    </style>
</head>

<body>

    <div class="success-card">
        <i class="fa-solid fa-circle-check"></i>
        <h2 class="mt-3">Payment Successful!</h2>
        <p class="text-muted">Thank you, <strong><?= htmlspecialchars($userName) ?></strong>! Your payment has been processed.</p>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="<?= $dashboardURL ?>" class="btn btn-success btn-custom">Go to Dashboard</a>
            <a href="/recharge" class="btn btn-primary btn-custom">Deposit More</a>
        </div>
    </div>

    <!-- Font Awesome for the check icon -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

</body>

</html>
