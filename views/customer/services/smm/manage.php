<?php
require_once 'helpers/session.php';
require_once 'include/config.php';
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'customer') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Customer';



if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


require_once __DIR__ . '/../../../../controllers/customer/services/smm/get-services.php';

$balance = 0.0;

if ($userId) {
    $stmt = $conn->prepare("SELECT balance FROM user_data WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMM Services | Allsmsverify</title>
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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>
    <div class="main-wrapper p-3">

        <?php include __DIR__ . '/../../components/header.php'; ?>

        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="d-flex justify-content-end align-items-center mb-3">
            <span class="badge bg-info text-dark">
                Balance: ₦ <?= number_format($balance ?? 0, 2) ?>
            </span>
        </div>
        <div class="container mt-4">
            <div class="d-flex align-items-center mb-4" style="animation: fadeIn 0.8s ease-out;">
                <div style="width: 5px; height: 30px; background: #0d6efd; border-radius: 10px; margin-right: 15px;"></div>
                <h4 class="mb-0" style="font-weight: 800; color: #c9d0d6; letter-spacing: -0.5px;">Available SMM Services</h4>
            </div>

            <div class="row g-4">
                <?php foreach ($services as $index => $s):
                    $delay = $index * 0.05; // Cards will pop up one by one
                ?>
                    <div class="col-md-6 col-lg-4" style="animation: zoomIn 0.5s ease forwards; animation-delay: <?= $delay ?>s; opacity: 0;">
                        <div class="card h-100 border-0"
                            style="border-radius: 20px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(.25,.8,.25,1); position: relative; overflow: hidden;"
                            onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.1)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)';">

                            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(13, 110, 253, 0.03); border-radius: 50%; z-index: 0;"></div>

                            <div class="card-body p-4" style="position: relative; z-index: 1;">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div style="width: 45px; height: 45px; background: #f0f7ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-bolt-lightning text-primary" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <span style="font-size: 0.7rem; font-weight: 700; color: #0d6efd; background: #e7f1ff; padding: 4px 10px; border-radius: 50px; text-transform: uppercase;">Instant</span>
                                </div>

                                <h6 class="fw-bold mb-3" style="color: #2c3e50; line-height: 1.5; height: 45px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    <?= htmlspecialchars($s['name']) ?>
                                </h6>

                                <div class="mb-4">
                                    <span style="font-size: 0.75rem; color: #95a5a6; display: block; margin-bottom: 2px;">Rate per 1000</span>
                                    <h5 style="font-weight: 800; color: #2ecc71; margin: 0;">₦ <?= number_format($s['price'], 2) ?></h5>
                                </div>

                                <div class="row g-0 py-2 px-3" style="background: #f8f9fa; border-radius: 12px;">
                                    <div class="col-6 border-end text-center">
                                        <small style="color: #7f8c8d; display: block; font-size: 0.65rem; text-transform: uppercase;">Min</small>
                                        <span style="font-weight: 700; color: #34495e; font-size: 0.9rem;"><?= number_format($s['min']) ?></span>
                                    </div>
                                    <div class="col-6 text-center">
                                        <small style="color: #7f8c8d; display: block; font-size: 0.65rem; text-transform: uppercase;">Max</small>
                                        <span style="font-weight: 700; color: #34495e; font-size: 0.9rem;"><?= number_format($s['max'] / 1000, 1) ?>K</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent border-0 p-4 pt-0">
                                <button class="btn btn-primary w-100 buy-service-btn"
                                    style="border-radius: 12px; padding: 12px; font-weight: 700; background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%); border: none; box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2); transition: all 0.3s;"
                                    data-service-id="<?= $s['id'] ?>"
                                    data-service-name="<?= htmlspecialchars($s['name']) ?>"
                                    data-service-price="<?= $s['price'] ?>"
                                    data-service-min="<?= $s['min'] ?>"
                                    data-service-max="<?= $s['max'] ?>"
                                    onmouseover="this.style.boxShadow='0 8px 20px rgba(13, 110, 253, 0.4)';"
                                    onmouseout="this.style.boxShadow='0 4px 15px rgba(13, 110, 253, 0.2)';">
                                    <i class="fa-solid fa-basket-shopping me-2"></i> Order Now
                                </button>
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

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }
        </style>

        <input type="hidden" id="userBalanceHidden" value="<?= floatval($balance ?? 0) ?>">


        <!-- Buy Modal -->
        <div class="modal fade" id="buyServiceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.15); overflow: hidden;">

                    <div class="modal-header border-0 pb-0" style="background: linear-gradient(to right, #ffffff, #f8f9fa);">
                        <h5 class="modal-title d-flex align-items-center" style="font-weight: 700; color: #1a1a1a; letter-spacing: -0.5px;">
                            <div style="width: 35px; height: 35px; background: #e8f5e9; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                <i class="fa-solid fa-cart-shopping text-success" style="font-size: 0.9rem;"></i>
                            </div>
                            Confirm Purchase
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem; opacity: 0.5;"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #95a5a6; font-weight: 600;">Selected Service</label>
                            <h6 id="buyServiceName" style="font-weight: 600; color: #2c3e50; margin-top: 4px;">---</h6>
                        </div>

                        <input type="hidden" id="buyServiceId" value="">


                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div style="background: #fdfefe; border: 1px solid #edf2f7; border-radius: 12px; padding: 12px;">
                                    <small style="color: #64748b; display: block; font-size: 0.7rem;">Unit Price</small>
                                    <span style="font-weight: 700; color: #0d6efd;">₦ <span id="buyServicePrice">0.00</span></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="background: #fdfefe; border: 1px solid #edf2f7; border-radius: 12px; padding: 12px;">
                                    <small style="color: #64748b; display: block; font-size: 0.7rem;">Limits (Min - Max)</small>
                                    <span style="font-weight: 600; color: #334155;"><span id="buyServiceMin">0</span> - <span id="buyServiceMax">0</span></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 p-3" style="background: #fff9f0; border-radius: 12px; border: 1px dashed #ffd580;">
                            <span style="font-size: 0.85rem; color: #856404; font-weight: 500;"><i class="fa-solid fa-wallet me-2"></i> Your Balance</span>
                            <span style="font-weight: 700; color: #856404;">₦ <span id="userBalance">0.00</span></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; color: #1a1a1a; font-size: 0.9rem;">Enter Quantity</label>
                            <div class="input-group" style="box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                                <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-layer-group text-muted"></i></span>
                                <input type="number" id="buyQuantity" class="form-control border-start-0" value="100" style="border-radius: 0 10px 10px 0; height: 48px; font-weight: 600; font-size: 1rem;">
                            </div>
                            <div id="buyError" class="text-danger small mt-2 d-flex align-items-center" style="font-weight: 500;"></div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; height: 48px; color: #64748b; background: #f1f5f9;">
                            Cancel
                        </button>
                        <button type="button" id="buyServiceBtn" class="btn btn-success flex-grow-1" style="border-radius: 12px; font-weight: 600; height: 48px; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); border: none; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);">
                            <span class="btn-text d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-circle-check me-2"></i> Confirm & Purchase
                            </span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $active = 'smm';
        include __DIR__ . '/../../components/bottom-nav.php';
        ?>

    </div>

    <script src="/js/customer/manage-smm-services.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
