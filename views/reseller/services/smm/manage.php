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

require_once __DIR__ . '/../../../../controllers/services/smm/get-services.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage SMM Services | Allsmsverify</title>
    <link rel="shortcut icon" href="<?php echo $WEBSITE_URL; ?>/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>
    <div class="main-wrapper p-3">

        <?php include __DIR__ . '/../../components/header.php'; ?>

        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4" style="animation: fadeInDown 0.8s ease-out;">
                <h5 class="mb-0" style="font-weight: 700; font-size: 1.25rem; color: #afbac5; letter-spacing: -0.5px;">
                    <i class="fa-solid fa-layer-group text-primary me-2" style="filter: drop-shadow(0 2px 4px rgba(13, 110, 253, 0.3));"></i>
                    Available Services
                </h5>
                <div class="badge bg-soft-primary text-primary px-3 py-2" style="background-color: #e7f1ff; border-radius: 8px; font-weight: 600;">
                    Total: <?= count($services) ?>
                </div>
            </div>

            <div class="row g-4">
                <?php if (empty($services)): ?>
                    <div class="col-12 text-center py-5" style="animation: fadeIn 1s;">
                        <div class="mb-3">
                            <i class="fa-solid fa-box-open fa-3x text-light"></i>
                        </div>
                        <h6 class="text-muted fw-light">No services available right now.</h6>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $index => $s):
                        $resellerPrice = $s['price'];
                        $adminIncome = $s['base_price'] - $s['api_price'];
                        $resellerIncome = $resellerPrice - $s['base_price'];
                        $delay = $index * 0.1;
                    ?>
                        <div class="col-md-6 col-lg-4" style="animation: fadeInUp 0.6s ease forwards; animation-delay: <?= $delay ?>s; opacity: 0;">
                            <div class="card h-100 border-0" style="border-radius: 15px; background: #ffffff; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; overflow: hidden;"
                                onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.1)';"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.05)';">

                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-dark text-truncate" style="font-size: 1rem; border-left: 4px solid #0d6efd; padding-left: 10px;">
                                        <?= htmlspecialchars($s['name']) ?>
                                    </h6>

                                    <div class="mb-4 p-3" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); border-radius: 12px; color: white;">
                                        <small style="opacity: 0.9; font-size: 0.75rem; text-transform: uppercase;">Your Selling Price</small>
                                        <div class="fs-4 fw-bold">₦ <?= number_format($resellerPrice, 2) ?></div>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div style="font-size: 0.75rem; color: #6c757d;">Min Order</div>
                                            <div class="fw-semibold text-dark"><?= number_format($s['min']) ?></div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div style="font-size: 0.75rem; color: #6c757d;">Max Order</div>
                                            <div class="fw-semibold text-dark"><?= number_format($s['max']) ?></div>
                                        </div>
                                    </div>

                                    <hr style="border-top: 1px dashed #dee2e6; margin: 15px 0;">

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex flex-column w-100">

                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small" style="color: #6c757d;">
                                                    <i class="fa-solid fa-tag me-1" style="color: #6c757d;"></i> Base Price:
                                                </span>
                                                <span class="small fw-bold text-dark">
                                                    ₦ <?= number_format($s['base_price'], 2) ?>
                                                </span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center p-2 mt-1" style="background-color: #f8fff9; border-radius: 6px; border: 1px solid #e1f5e5;">
                                                <span class="small" style="color: #6c757d;">
                                                    <i class="fa-solid fa-chart-line me-1 text-success"></i> Your Profit:
                                                </span>
                                                <span class="small fw-bold text-success">
                                                    + ₦ <?= number_format($resellerIncome, 2) ?>
                                                </span>
                                            </div>

                                        </div>
                                    </div>


                                </div>
                                <div class="card-footer bg-light border-0 p-3">
                                    <button type="button"
                                        class="btn btn-primary btn-sm w-100 py-2 set-custom-price-btn"
                                        data-service-id="<?= $s['id'] ?>"
                                        data-service-name="<?= htmlspecialchars($s['name']) ?>"
                                        data-base-price="<?= $s['base_price'] ?>"
                                        data-reseller-price="<?= $resellerPrice ?>"
                                        style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Set Custom Price
                                    </button>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <style>
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
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




        <!-- Custom Price Modal -->
        <div class="modal fade" id="customPriceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-success fw-bold">
                            <i class="bi bi-currency-exchange me-2 "></i>
                            Set Custom Price
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-dark">Service Name</label>
                            <input type="text" id="modalServiceName" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Base Price</label>
                            <input type="text" id="modalBasePrice" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Current Reseller Price</label>
                            <input type="text" id="modalResellerPrice" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Set New Price <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="0.01" id="modalCustomPrice" class="form-control" placeholder="Enter custom price">
                            <div id="modalPriceError" class="text-danger small mt-1"></div>
                        </div>

                    </div>

                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="saveCustomPrice" class="btn btn-success btn-create px-5">
                            <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Set</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>



        <?php
        $active = 'smm';
        include __DIR__ . '/../../components/bottom-nav.php';
        ?>


        <script src="/js/reseller/manage-smm-service.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>
