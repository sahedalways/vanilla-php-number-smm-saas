<?php
require_once 'helpers/session.php';
require_once 'include/config.php';

if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: /");
    exit;
}

$userName = $_SESSION['name'] ?? 'Admin';
authOnly();

// Fetch all SMS services
$stmt = $conn->prepare("SELECT * FROM sms_provider_services ORDER BY created_at DESC");
$stmt->execute();
$services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin | All SMS Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">
</head>

<body>
    <div class="container my-4">
        <?php include __DIR__ . '/../../components/header.php'; ?>

        <h4 class="mb-4">All SMS Services</h4>

        <div class="row g-4">
            <?php foreach ($services as $index => $s):
                $delay = $index * 0.05;
            ?>
                <div class="col-md-6 col-lg-4" style="animation: zoomIn 0.5s ease forwards; animation-delay: <?= $delay ?>s; opacity: 0;">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 20px;">

                        <div class="card-body p-4">

                            <!-- Icon -->
                            <div class="mb-3">
                                <div style="width:45px;height:45px;background:#f0f7ff;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-message text-primary"></i>
                                </div>
                            </div>

                            <!-- Service Title -->
                            <h6 class="fw-bold mb-2">
                                <?= ucfirst(htmlspecialchars($s['service_code'])) ?> -
                                <?= ucfirst(htmlspecialchars($s['country'])) ?>
                            </h6>

                            <!-- Operator Badge -->
                            <div class="mb-3">
                                <span class="badge bg-info text-dark">
                                    Operator: <?= htmlspecialchars($s['operator']) ?>
                                </span>
                            </div>

                            <!-- Pricing -->
                            <div class="mb-3">
                                <small class="text-muted">Base Price</small>
                                <h5 class="text-success fw-bold">
                                    ₦ <?= number_format($s['base_price'], 2) ?>
                                </h5>
                            </div>

                            <!-- Cost & Profit -->
                            <div class="row text-center mb-3">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">Provider Cost</small>
                                    <span class="fw-semibold">
                                        ₦ <?= number_format($s['provider_cost'], 2) ?>
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Admin Profit</small>
                                    <span class="fw-semibold text-primary">
                                        ₦ <?= number_format($s['admin_profit'], 2) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Stock & Rate -->
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">Available</small>
                                    <span class="fw-bold">
                                        <?= number_format($s['count']) ?>
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Success Rate</small>
                                    <span class="fw-bold">
                                        <?= number_format($s['rate'], 2) ?>%
                                    </span>
                                </div>
                            </div>

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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    $active = 'sms';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>

</body>

</html>
