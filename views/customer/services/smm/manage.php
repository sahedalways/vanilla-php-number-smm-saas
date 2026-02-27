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
    <title>SMM Services | Foreign sms</title>
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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        /* Loading animation styles */
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            width: 100%;
        }

        .spinner-ring {
            display: inline-block;
            width: 60px;
            height: 60px;
            border: 4px solid rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            border-top-color: #0d6efd;
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }

        .loading-dots:after {
            content: '.';
            animation: dots 1.5s steps(5, end) infinite;
        }

        @keyframes dots {

            0%,
            20% {
                content: '.';
            }

            40% {
                content: '..';
            }

            60% {
                content: '...';
            }

            80%,
            100% {
                content: '';
            }
        }

        /* Skeleton loading animation */
        .skeleton-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .skeleton-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .skeleton-title {
            height: 45px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .skeleton-badges {
            height: 25px;
            width: 60%;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 20px;
            margin-bottom: 15px;
        }

        .skeleton-price {
            height: 35px;
            width: 40%;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .skeleton-limits {
            height: 50px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .skeleton-button {
            height: 45px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 12px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Load more button */
        .load-more-container {
            text-align: center;
            margin: 40px 0;
        }

        .load-more-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .load-more-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .load-more-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* No more services */
        .no-more-services {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-weight: 500;
        }

        /* Animation for fade-in */
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
                <h4 class="mb-0" style="font-weight: 800; color: #3c3e3f; letter-spacing: -0.5px;">Available SMM Services</h4>
            </div>

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

            <!-- Loading Spinner (initially visible) -->
            <div id="loadingSpinner" class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="loading-text">Loading services<span class="loading-dots"></span></div>
            </div>

            <!-- Services Container (initially hidden) -->
            <div id="servicesContainer" style="display: none;">
                <div class="row g-4" id="servicesList"></div>
                <div class="load-more-container">
                    <button id="loadMoreBtn" class="load-more-btn" onclick="loadMoreServices()">
                        <i class="fa-solid fa-arrow-down me-2"></i>Load More Services
                    </button>
                </div>
                <div id="noMoreServices" class="no-more-services" style="display: none;">
                    <i class="fa-regular fa-circle-check me-2"></i>No more services to load
                </div>
            </div>

            <!-- Hidden input for user balance -->
            <input type="hidden" id="userBalanceHidden" value="<?= floatval($balance ?? 0) ?>">
        </div>

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

                        <div class="mb-3" id="typeSpecificFields"></div>

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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        // Global variables for pagination
        let currentPage = 1;
        let totalPages = 1;
        let isLoading = false;
        let allServices = [];

        // Load initial services
        $(document).ready(function() {
            loadServices(1);
        });

        function loadServices(page) {
            if (isLoading) return;

            isLoading = true;

            // Show loading spinner if it's the first page
            if (page === 1) {
                $('#loadingSpinner').show();
                $('#servicesContainer').hide();
            }

            $.ajax({
                url: '/controllers/customer/services/smm/get-services',
                type: 'GET',
                data: {
                    page: page,
                    limit: 12
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (page === 1) {
                            allServices = response.services;
                            totalPages = response.total_pages;
                            renderServices(response.services);

                            // Hide loading spinner and show services container
                            $('#loadingSpinner').hide();
                            $('#servicesContainer').show();
                        } else {
                            allServices = [...allServices, ...response.services];
                            appendServices(response.services);
                        }

                        currentPage = response.current_page;

                        // Handle load more button visibility
                        if (currentPage >= totalPages) {
                            $('#loadMoreBtn').hide();
                            $('#noMoreServices').show();
                        } else {
                            $('#loadMoreBtn').show();
                            $('#noMoreServices').hide();
                        }
                    } else {
                        showError('Failed to load services');
                        if (page === 1) {
                            $('#loadingSpinner').hide();
                            $('#servicesContainer').show();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showError('Failed to load services. Please try again.');
                    if (page === 1) {
                        $('#loadingSpinner').hide();
                        $('#servicesContainer').show();
                    }
                },
                complete: function() {
                    isLoading = false;
                }
            });
        }

        function loadMoreServices() {
            if (currentPage < totalPages) {
                loadServices(currentPage + 1);
            }
        }

        function renderServices(services) {
            const $servicesList = $('#servicesList');
            $servicesList.empty();

            services.forEach((service, index) => {
                $servicesList.append(createServiceCard(service, index));
            });

            // Re-attach search functionality
            attachSearchListener();
        }

        function appendServices(services) {
            const $servicesList = $('#servicesList');
            const startIndex = allServices.length - services.length;

            services.forEach((service, index) => {
                $servicesList.append(createServiceCard(service, startIndex + index));
            });

            // Re-attach search functionality
            attachSearchListener();
        }

        function createServiceCard(service, index) {
            const delay = index * 0.05;
            const maxDisplay = service.max >= 1000 ? (service.max / 1000).toFixed(1) + 'K' : service.max;

            return `
                <div class="col-md-6 col-lg-4 service-item"
                    data-name="${service.name.toLowerCase()}"
                    data-category="${service.category.toLowerCase()}"
                    data-type="${service.type.toLowerCase()}"
                    style="animation: zoomIn 0.5s ease forwards; animation-delay: ${delay}s; opacity: 0;">
                    <div class="card h-100 border-0"
                        style="border-radius: 20px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(.25,.8,.25,1); position: relative; overflow: hidden;"
                        onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.1)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)';">

                        <div class="card-body p-4" style="position: relative; z-index: 1;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div style="width: 45px; height: 45px; background: #f0f7ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-bolt-lightning text-primary" style="font-size: 1.2rem;"></i>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-2" style="color: #2c3e50; line-height: 1.5; height: 45px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                ${escapeHtml(service.name)}
                            </h6>

                            <div class="mb-3">
                                <span class="badge bg-secondary me-1">${escapeHtml(service.type)}</span>
                                <span class="badge bg-info text-dark me-1">${escapeHtml(service.category)}</span>
                                ${service.cancel ? '<span class="badge bg-warning text-dark me-1">Cancelable</span>' : ''}
                                ${service.refill ? '<span class="badge bg-success">Refill</span>' : ''}
                            </div>

                            <div class="mb-4">
                                <span style="font-size: 0.75rem; color: #95a5a6; display: block; margin-bottom: 2px;">Rate per</span>
                                <h5 style="font-weight: 800; color: #2ecc71; margin: 0;">₦ ${parseFloat(service.price).toFixed(2)}</h5>
                            </div>

                            <div class="row g-0 py-2 px-3" style="background: #f8f9fa; border-radius: 12px;">
                                <div class="col-6 border-end text-center">
                                    <small style="color: #7f8c8d; display: block; font-size: 0.65rem; text-transform: uppercase;">Min</small>
                                    <span style="font-weight: 700; color: #34495e; font-size: 0.9rem;">${service.min}</span>
                                </div>
                                <div class="col-6 text-center">
                                    <small style="color: #7f8c8d; display: block; font-size: 0.65rem; text-transform: uppercase;">Max</small>
                                    <span style="font-weight: 700; color: #34495e; font-size: 0.9rem;">${maxDisplay}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-0 p-4 pt-0">
                            <button class="btn btn-primary w-100 buy-service-btn"
                                style="border-radius: 12px; padding: 12px; font-weight: 700; background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%); border: none; box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2); transition: all 0.3s;"
                                data-service-id="${service.id}"
                                data-service-name="${escapeHtml(service.name)}"
                                data-service-price="${service.price}"
                                data-service-min="${service.min}"
                                data-service-max="${service.max}"
                                data-service-type="${escapeHtml(service.type)}"
                                onmouseover="this.style.boxShadow='0 8px 20px rgba(13, 110, 253, 0.4)';"
                                onmouseout="this.style.boxShadow='0 4px 15px rgba(13, 110, 253, 0.2)';">
                                <i class="fa-solid fa-basket-shopping me-2"></i> Order Now
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function attachSearchListener() {
            $('#serviceSearch').off('input').on('input', function() {
                const keyword = this.value.toLowerCase().trim();
                $('.service-item').each(function() {
                    const name = $(this).data('name') || '';
                    const category = $(this).data('category') || '';
                    const type = $(this).data('type') || '';

                    const match = name.includes(keyword) ||
                        category.includes(keyword) ||
                        type.includes(keyword);

                    $(this).toggle(match);
                });
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showError(message) {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#dc3545",
                className: "toastify-custom"
            }).showToast();
        }

        // Initialize modal functionality
        $(document).on('click', '.buy-service-btn', function() {
            const serviceId = $(this).data('service-id');
            const serviceName = $(this).data('service-name');
            const servicePrice = $(this).data('service-price');
            const serviceMin = $(this).data('service-min');
            const serviceMax = $(this).data('service-max');
            const serviceType = $(this).data('service-type');
            const userBalance = parseFloat($('#userBalanceHidden').val()) || 0;

            $('#buyServiceId').val(serviceId);
            $('#buyServiceName').text(serviceName);
            $('#buyServicePrice').text(parseFloat(servicePrice).toFixed(2));
            $('#buyServiceMin').text(serviceMin);
            $('#buyServiceMax').text(serviceMax);
            $('#userBalance').text(userBalance.toFixed(2));

            // Set quantity min/max
            $('#buyQuantity').attr('min', serviceMin);
            $('#buyQuantity').attr('max', serviceMax);
            $('#buyQuantity').val(serviceMin);

            // Clear previous error
            $('#buyError').empty().hide();

            const modal = new bootstrap.Modal(document.getElementById('buyServiceModal'));
            modal.show();
        });

        // Quantity validation
        $('#buyQuantity').on('input', function() {
            const quantity = parseInt($(this).val()) || 0;
            const min = parseInt($('#buyServiceMin').text()) || 0;
            const max = parseInt($('#buyServiceMax').text()) || 0;
            const price = parseFloat($('#buyServicePrice').text()) || 0;
            const balance = parseFloat($('#userBalance').text()) || 0;
            const totalCost = quantity * price;

            if (quantity < min) {
                $('#buyError').html('<i class="fa-solid fa-circle-exclamation me-1"></i>Minimum quantity is ' + min).show();
                $('#buyServiceBtn').prop('disabled', true);
            } else if (quantity > max) {
                $('#buyError').html('<i class="fa-solid fa-circle-exclamation me-1"></i>Maximum quantity is ' + max).show();
                $('#buyServiceBtn').prop('disabled', true);
            } else if (totalCost > balance) {
                $('#buyError').html('<i class="fa-solid fa-circle-exclamation me-1"></i>Insufficient balance. Required: ₦' + totalCost.toFixed(2)).show();
                $('#buyServiceBtn').prop('disabled', true);
            } else {
                $('#buyError').empty().hide();
                $('#buyServiceBtn').prop('disabled', false);
            }
        });


        // Reset modal on close
        $('#buyServiceModal').on('hidden.bs.modal', function() {
            $('#buyServiceBtn').prop('disabled', false);
            $('#buyServiceBtn').find('.btn-text').removeClass('d-none');
            $('#buyServiceBtn').find('.spinner-border').addClass('d-none');
            $('#buyError').empty().hide();
        });
    </script>
</body>

</html>
