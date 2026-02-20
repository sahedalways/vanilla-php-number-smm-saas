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

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text"
                                id="serviceSearch"
                                class="form-control"
                                placeholder="Search service, category, type..."
                                style="height:48px; font-weight:500;">
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 service-item"
                    data-name="<?= strtolower(htmlspecialchars($s['name'])) ?>"
                    data-category="<?= strtolower(htmlspecialchars($s['category'])) ?>"
                    data-type="<?= strtolower(htmlspecialchars($s['type'])) ?>"
                    style="animation: zoomIn 0.5s ease forwards; animation-delay: <?= $delay ?>s; opacity: 0;">

                    <div class="card service-card h-100 border-0">

                        <div class="card-body p-4">

                            <!-- Top Row -->
                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div class="icon-box">
                                    <i class="fa-solid fa-bolt-lightning text-primary"></i>
                                </div>

                                <?php if ($profitPercent < 5): ?>
                                    <span class="badge bg-danger-subtle text-danger fw-semibold">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Low Profit
                                    </span>
                                <?php endif; ?>

                            </div>

                            <!-- Service Name -->
                            <h6 class="service-title fw-bold mb-2">
                                <?= htmlspecialchars($s['name']) ?>
                            </h6>

                            <!-- Badges -->
                            <div class="mb-3 d-flex flex-wrap gap-1">
                                <span class="badge bg-secondary-subtle text-dark">
                                    <?= htmlspecialchars($s['type']) ?>
                                </span>
                                <span class="badge bg-info-subtle text-dark">
                                    <?= htmlspecialchars($s['category']) ?>
                                </span>

                                <?php if ($s['cancel']): ?>
                                    <span class="badge bg-warning-subtle text-dark">Cancelable</span>
                                <?php endif; ?>

                                <?php if ($s['refill']): ?>
                                    <span class="badge bg-success-subtle text-success">Refill</span>
                                <?php endif; ?>
                            </div>

                            <!-- Base Price -->
                            <div class="price-box mb-3">
                                <small class="label">Base Price</small>
                                <h4 class="price mb-0">
                                    ₦ <?= number_format($s['base_price'], 2) ?>
                                </h4>
                            </div>

                            <!-- API + Profit -->
                            <div class="info-box mb-3">
                                <div class="row g-0">

                                    <div class="col-6 text-center border-end">
                                        <small class="mini-label">API Price</small>
                                        <div class="value">
                                            ₦ <?= number_format($s['api_price'], 2) ?>
                                        </div>
                                    </div>

                                    <div class="col-6 text-center">
                                        <small class="mini-label">Admin Profit</small>
                                        <div class="value"
                                            style="color:<?= $profit >= 0 ? '#16a34a' : '#dc2626' ?>;">
                                            ₦ <?= number_format($profit, 2) ?>
                                        </div>
                                        <small class="percent">
                                            <?= number_format($profitPercent, 2) ?>%
                                        </small>
                                    </div>

                                </div>
                            </div>

                            <!-- Min Max -->
                            <div class="limit-box">
                                <div class="row g-0">

                                    <div class="col-6 text-center border-end">
                                        <small class="mini-label">Min</small>
                                        <div class="value"><?= number_format($s['min']) ?></div>
                                    </div>

                                    <div class="col-6 text-center">
                                        <small class="mini-label">Max</small>
                                        <div class="value"><?= number_format($s['max']) ?></div>
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
                        position: relative;
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
                        color: #1e293b;
                        line-height: 1.5;
                        height: 46px;
                        overflow: hidden;
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                    }

                    /* ===== PRICE ===== */
                    .price-box .label {
                        font-size: 11px;
                        color: #94a3b8;
                        text-transform: uppercase;
                        letter-spacing: .5px;
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

                    .percent {
                        font-size: 11px;
                        color: #64748b;
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
    $active = 'smm';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>


    <script>
        document.getElementById('serviceSearch').addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.service-item');

            items.forEach(item => {
                const name = item.dataset.name || '';
                const category = item.dataset.category || '';
                const type = item.dataset.type || '';

                const match =
                    name.includes(keyword) ||
                    category.includes(keyword) ||
                    type.includes(keyword);

                item.style.display = match ? '' : 'none';
            });
        });
    </script>
</body>

</html>
