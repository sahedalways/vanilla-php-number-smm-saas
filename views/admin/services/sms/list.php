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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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



                <div class="col-md-6 col-lg-4 service-item"
                    data-name="<?= strtolower(htmlspecialchars($s['service_code'])) ?>"
                    data-country="<?= strtolower(htmlspecialchars($s['country'])) ?>"
                    data-operator="<?= strtolower(htmlspecialchars($s['operator'])) ?>"
                    style="animation: zoomIn 0.5s ease forwards; animation-delay: <?= $delay ?>s; opacity: 0;">

                    <div class="card service-card h-100 border-0">

                        <div class="card-body p-4">

                            <!-- Top -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-box">
                                    <i class="fa-solid fa-message text-primary"></i>
                                </div>

                                <span class="badge bg-light text-dark fw-semibold">
                                    <?= htmlspecialchars($s['operator']) ?>
                                </span>
                            </div>

                            <!-- Title -->
                            <h6 class="service-title mb-3">
                                <?= ucfirst(htmlspecialchars($s['service_code'])) ?> —
                                <?= ucfirst(htmlspecialchars($s['country'])) ?>
                            </h6>

                            <!-- Price -->
                            <div class="price-box mb-3">
                                <small class="label">Base Price</small>
                                <h4 class="price mb-0">
                                    ₦ <?= number_format($s['base_price'], 2) ?>
                                </h4>
                            </div>

                            <!-- Cost & Profit -->
                            <div class="info-box mb-3">
                                <div class="row g-0 text-center">

                                    <div class="col-6 border-end">
                                        <small class="mini-label">Provider Cost</small>
                                        <div class="value">
                                            ₦ <?= number_format($s['provider_cost'], 2) ?>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <small class="mini-label">Admin Profit</small>
                                        <div class="value text-primary">
                                            ₦ <?= number_format($s['admin_profit'], 2) ?>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Stock & Rate -->
                            <div class="limit-box">
                                <div class="row g-0 text-center">

                                    <div class="col-6 border-end">
                                        <small class="mini-label">Available</small>
                                        <div class="value">
                                            <?= number_format($s['count']) ?>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <small class="mini-label">Success Rate</small>
                                        <div class="value text-success">
                                            <?= number_format($s['rate'], 2) ?>%
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <style>
                    /* ===== CARD ===== */
                    .service-card {
                        border-radius: 18px;
                        background: #ffffff;
                        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
                        transition: all .35s cubic-bezier(.25, .8, .25, 1);
                        overflow: hidden;
                    }

                    .service-card:hover {
                        transform: translateY(-8px) scale(1.02);
                        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
                    }

                    /* ===== ICON ===== */
                    .icon-box {
                        width: 46px;
                        height: 46px;
                        background: linear-gradient(135deg, #eef5ff, #f5f9ff);
                        border-radius: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 18px;
                    }

                    /* ===== TITLE ===== */
                    .service-title {
                        font-weight: 700;
                        color: #1e293b;
                        line-height: 1.5;
                    }

                    /* ===== PRICE ===== */
                    .price-box .label {
                        font-size: 11px;
                        color: #94a3b8;
                        text-transform: uppercase;
                    }

                    .price-box .price {
                        font-weight: 800;
                        color: #16a34a;
                    }

                    /* ===== INFO BOX ===== */
                    .info-box,
                    .limit-box {
                        background: #f8fafc;
                        border-radius: 12px;
                        padding: 10px;
                    }

                    .mini-label {
                        font-size: 10px;
                        color: #94a3b8;
                        text-transform: uppercase;
                    }

                    .value {
                        font-weight: 700;
                        color: #334155;
                        font-size: 14px;
                    }
                </style>


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
