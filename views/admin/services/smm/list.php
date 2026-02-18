<?php
require_once 'helpers/session.php';
require_once 'include/config.php';

if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: /");
    exit;
}

$userName = $_SESSION['name'] ?? 'Admin';
authOnly();

// Fetch all services
$stmt = $conn->prepare("SELECT * FROM services ORDER BY created_at DESC");
$stmt->execute();
$services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin | All SMM Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">
</head>

<body>
    <div class="container my-4">
        <?php include __DIR__ . '/../../components/header.php'; ?>

        <h4 class="mb-4">All SMM Services</h4>

        <div class="row g-4">
            <?php foreach ($services as $index => $s):
                $delay = $index * 0.05;

                $profit = $s['base_price'] - $s['api_price'];
                $profitPercent = $s['api_price'] > 0
                    ? ($profit / $s['api_price']) * 100
                    : 0;
            ?>
                <div class="col-md-6 col-lg-4"
                    style="animation: zoomIn 0.5s ease forwards; animation-delay: <?= $delay ?>s; opacity: 0;">

                    <div class="card h-100 border-0 shadow-sm"
                        style="border-radius: 20px; overflow: hidden;">

                        <div class="card-body p-4">

                            <!-- Icon -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div style="width:45px;height:45px;background:#f0f7ff;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-bolt-lightning text-primary"></i>
                                </div>

                                <?php if ($profitPercent < 5): ?>
                                    <span class="badge bg-danger">Low Profit</span>
                                <?php endif; ?>
                            </div>

                            <!-- Service Name -->
                            <h6 class="fw-bold mb-2"
                                style="line-height:1.5;height:45px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                <?= htmlspecialchars($s['name']) ?>
                            </h6>

                            <!-- Badges -->
                            <div class="mb-3">
                                <span class="badge bg-secondary me-1">
                                    <?= htmlspecialchars($s['type']) ?>
                                </span>
                                <span class="badge bg-info text-dark me-1">
                                    <?= htmlspecialchars($s['category']) ?>
                                </span>

                                <?php if ($s['cancel']): ?>
                                    <span class="badge bg-warning text-dark me-1">Cancelable</span>
                                <?php endif; ?>

                                <?php if ($s['refill']): ?>
                                    <span class="badge bg-success">Refill</span>
                                <?php endif; ?>
                            </div>

                            <!-- Base Price -->
                            <div class="mb-2">
                                <small style="color:#95a5a6;display:block;">Base Price</small>
                                <h5 style="font-weight:700;color:#2ecc71;">
                                    ₦ <?= number_format($s['base_price'], 2) ?>
                                </h5>
                            </div>

                            <!-- API Price & Profit -->
                            <div class="row g-0 py-2 px-3 mb-3"
                                style="background:#f8f9fa;border-radius:12px;">

                                <div class="col-6 border-end text-center">
                                    <small style="color:#7f8c8d;font-size:0.65rem;">API Price</small>
                                    <div style="font-weight:700;color:#34495e;">
                                        ₦ <?= number_format($s['api_price'], 2) ?>
                                    </div>
                                </div>

                                <div class="col-6 text-center">
                                    <small style="color:#7f8c8d;font-size:0.65rem;">Admin Profit</small>
                                    <div style="font-weight:700;
                                    color:<?= $profit >= 0 ? '#27ae60' : '#e74c3c' ?>;">
                                        ₦ <?= number_format($profit, 2) ?>
                                    </div>
                                    <small style="color:#6c757d;">
                                        <?= number_format($profitPercent, 2) ?>%
                                    </small>
                                </div>
                            </div>

                            <!-- Min / Max -->
                            <div class="row g-0 py-2 px-3"
                                style="background:#eef2f7;border-radius:12px;">
                                <div class="col-6 border-end text-center">
                                    <small style="color:#7f8c8d;font-size:0.65rem;">Min</small>
                                    <div style="font-weight:700;color:#34495e;">
                                        <?= number_format($s['min']) ?>
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <small style="color:#7f8c8d;font-size:0.65rem;">Max</small>
                                    <div style="font-weight:700;color:#34495e;">
                                        <?= number_format($s['max']) ?>
                                    </div>
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
    $active = 'smm';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>

</body>

</html>
