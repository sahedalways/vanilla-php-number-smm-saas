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
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <title>Manage SMM Services | Foreign sms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        /* Loading animation styles */
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            width: 100%;
            min-height: 400px;
        }

        .spinner-ring {
            display: inline-block;
            width: 70px;
            height: 70px;
            border: 5px solid rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            border-top-color: #0d6efd;
            border-bottom-color: #0d6efd;
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            color: #64748b;
            font-size: 1.1rem;
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
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .skeleton-header {
            height: 30px;
            width: 80%;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .skeleton-price {
            height: 70px;
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
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .skeleton-details {
            height: 100px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .skeleton-button {
            height: 40px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
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
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.2);
        }

        .load-more-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .load-more-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* No more services */
        .no-more-services {
            text-align: center;
            padding: 30px;
            color: #64748b;
            font-weight: 500;
            background: #f8fafc;
            border-radius: 12px;
            margin: 20px 0;
        }

        /* Stats bar */
        .stats-bar {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stats-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #eef5ff, #f5f9ff);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d6efd;
        }

        .stats-info h6 {
            font-size: 0.85rem;
            color: #64748b;
            margin: 0;
        }

        .stats-info span {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Animation for fade-in */
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
</head>

<body>
    <div class="main-wrapper p-3">
        <?php include __DIR__ . '/../../components/header.php'; ?>

        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">

        <!-- Stats will be populated by JavaScript -->
        <div class="stats-bar" id="statsBar" style="display: none;">
            <div class="stats-item">
                <div class="stats-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="stats-info">
                    <h6>Total Services</h6>
                    <span id="totalServices">0</span>
                </div>
            </div>
            <div class="stats-item">
                <div class="stats-icon">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <div class="stats-info">
                    <h6>Showing</h6>
                    <span id="showingServices">0</span>
                </div>
            </div>
            <div class="stats-item">
                <div class="stats-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="stats-info">
                    <h6>Avg. Profit</h6>
                    <span id="avgProfit">0%</span>
                </div>
            </div>
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
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <button class="btn btn-primary" onclick="location.reload()" style="border-radius: 12px; padding: 12px 25px; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); border: none;">
                    <i class="fa-solid fa-rotate me-2"></i>Refresh
                </button>
            </div>
        </div>

        <!-- Loading Spinner (initially visible) -->
        <div id="loadingSpinner" class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="loading-text">Loading services<span class="loading-dots"></span></div>
        </div>

        <!-- Skeleton Loader (shown during loading) -->
        <div id="skeletonLoader" class="row g-4" style="display: none;">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="skeleton-card">
                        <div class="skeleton-header"></div>
                        <div class="skeleton-price"></div>
                        <div class="skeleton-limits"></div>
                        <div class="skeleton-details"></div>
                        <div class="skeleton-button"></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Services Container (initially hidden) -->
        <div id="servicesContainer" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4" style="animation: fadeInDown 0.8s ease-out;">
                <h5 class="mb-0" style="font-weight: 700; font-size: 1.25rem; color: #1b1c1d; letter-spacing: -0.5px;">
                    <i class="fa-solid fa-layer-group text-primary me-2" style="filter: drop-shadow(0 2px 4px rgba(13, 110, 253, 0.3));"></i>
                    Available SMM Services
                </h5>
            </div>

            <div class="row g-4" id="servicesList"></div>

            <div class="load-more-container">
                <button id="loadMoreBtn" class="load-more-btn" onclick="loadMoreServices()">
                    <i class="fa-solid fa-arrow-down me-2"></i>Load More Services
                </button>
            </div>

            <div id="noMoreServices" class="no-more-services" style="display: none;">
                <i class="fa-regular fa-circle-check me-2"></i>You've viewed all services
            </div>
        </div>

        <!-- Custom Price Modal -->
        <div class="modal fade" id="customPriceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #f8f9fa, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <h5 class="modal-title text-success fw-bold">
                            <i class="bi bi-currency-exchange me-2"></i>
                            Set Custom Price
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <input type="hidden" id="modalServiceId" value="">

                        <div class="mb-3">
                            <label class="form-label text-dark fw-semibold">Service Name</label>
                            <input type="text" id="modalServiceName" class="form-control bg-light" readonly style="border-radius: 10px; border: 1px solid #e9ecef;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark fw-semibold">Base Price</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">₦</span>
                                <input type="text" id="modalBasePrice" class="form-control bg-light border-start-0" readonly style="border-radius: 0 10px 10px 0;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark fw-semibold">Current Reseller Price</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">₦</span>
                                <input type="text" id="modalResellerPrice" class="form-control bg-light border-start-0" readonly style="border-radius: 0 10px 10px 0;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark fw-semibold">Set New Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">₦</span>
                                <input type="number" min="0" step="0.01" id="modalCustomPrice" class="form-control border-start-0" placeholder="Enter custom price" style="border-radius: 0 10px 10px 0; height: 48px;">
                            </div>
                            <div id="modalPriceError" class="text-danger small mt-1"></div>
                            <div id="modalPriceInfo" class="text-info small mt-1"></div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; height: 48px;">Cancel</button>
                        <button type="button" id="saveCustomPrice" class="btn btn-success px-5" style="border-radius: 10px; font-weight: 600; height: 48px; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); border: none;">
                            <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Set Price</span>
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
    </div>

    <!-- Load your existing JS file -->
    <script src="/js/reseller/manage-smm-service.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Global variables for pagination
        let currentPage = 1;
        let totalPages = 1;
        let isLoading = false;
        let allServices = [];
        let totalServicesCount = 0;

        // Load initial services
        $(document).ready(function() {
            loadServices(1);
        });

        function loadServices(page) {
            if (isLoading) return;

            isLoading = true;

            // Show loading spinner/skeleton for first page
            if (page === 1) {
                $('#loadingSpinner').show();
                $('#skeletonLoader').hide();
                $('#servicesContainer').hide();
                $('#statsBar').hide();
            } else {
                // Show loading state on button
                $('#loadMoreBtn').prop('disabled', true);
                $('#loadMoreBtn').html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...');
            }

            $.ajax({
                url: '/controllers/reseller/services/smm/get-services',
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
                            totalServicesCount = response.total_rows;
                            renderServices(response.services);
                            updateStats(response);

                            // Hide loading elements and show services
                            $('#loadingSpinner').hide();
                            $('#skeletonLoader').hide();
                            $('#servicesContainer').show();
                            $('#statsBar').show();
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

                        // Update showing count
                        $('#showingServices').text(`${allServices.length} of ${totalServicesCount}`);
                    } else {
                        showError('Failed to load services');
                        if (page === 1) {
                            $('#loadingSpinner').hide();
                            $('#servicesContainer').show();
                            $('#servicesList').html('<div class="col-12 text-center py-5"><i class="fa-regular fa-face-frown fa-3x mb-3" style="color: #94a3b8;"></i><h5>No services found</h5></div>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showError('Failed to load services. Please try again.');
                    if (page === 1) {
                        $('#loadingSpinner').hide();
                        $('#servicesContainer').show();
                        $('#servicesList').html('<div class="col-12 text-center py-5"><i class="fa-regular fa-circle-exclamation fa-3x mb-3" style="color: #dc2626;"></i><h5>Error loading services</h5><p class="text-muted">Please try refreshing the page</p></div>');
                    }
                },
                complete: function() {
                    isLoading = false;
                    if (page > 1) {
                        $('#loadMoreBtn').prop('disabled', false);
                        $('#loadMoreBtn').html('<i class="fa-solid fa-arrow-down me-2"></i>Load More Services');
                    }
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
            const delay = index * 0.1;
            const resellerIncome = service.price - service.base_price;

            return `
                <div class="col-md-6 col-lg-4 service-item"
                    data-name="${service.name.toLowerCase()}"
                    data-category="${service.category ? service.category.toLowerCase() : ''}"
                    data-type="${service.type ? service.type.toLowerCase() : ''}"
                    style="animation: fadeInUp 0.6s ease forwards; animation-delay: ${delay}s; opacity: 0;">

                    <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden"
                        style="transition: transform 0.3s ease, box-shadow 0.3s ease;"
                        onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0 25px rgba(0,0,0,0.12)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0 15px rgba(0,0,0,0.05)';">

                        <!-- Card Header / Service Name -->
                        <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                            <h6 class="fw-bold" style="font-size: 1rem; border-left: 4px solid #0d6efd; padding-left: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${escapeHtml(service.name)}
                            </h6>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-4">

                            <!-- Selling Price -->
                            <div class="mb-4 p-3 rounded-3 text-white" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                                <small class="text-uppercase" style="opacity: 0.85; font-size: 0.75rem;">Your Selling Price</small>
                                <div class="fs-4 fw-bold">₦ ${parseFloat(service.price).toFixed(2)}</div>
                            </div>

                            <!-- Min / Max Order -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="small text-muted">Min Order</div>
                                    <div class="fw-semibold text-dark">${service.min.toLocaleString()}</div>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="small text-muted">Max Order</div>
                                    <div class="fw-semibold text-dark">${service.max.toLocaleString()}</div>
                                </div>
                            </div>

                            <hr class="my-3" style="border-top: 1px dashed #dee2e6;">

                            <!-- Base Price & Profit -->
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">
                                        <i class="fa-solid fa-tag me-1"></i> Base Price
                                    </span>
                                    <span class="small fw-bold text-dark">₦ ${parseFloat(service.base_price).toFixed(2)}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center p-2 rounded-2 border border-success" style="background-color: #f8fff9;">
                                    <span class="small text-success">
                                        <i class="fa-solid fa-chart-line me-1"></i> Your Profit
                                    </span>
                                    <span class="small fw-bold text-success">+ ₦ ${resellerIncome.toFixed(2)}</span>
                                </div>

                                <!-- Additional fields: type, category, cancel, refill -->
                                <div class="d-flex flex-column gap-1 mt-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="small text-muted">Type:</span>
                                        <span class="small fw-bold text-dark">${escapeHtml(service.type || 'Default')}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="small text-muted">Category:</span>
                                        <span class="small fw-bold text-dark">${escapeHtml(service.category || '-')}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="small text-muted">Cancelable:</span>
                                        <span class="small fw-bold text-dark">${service.cancel ? 'Yes' : 'No'}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="small text-muted">Refill:</span>
                                        <span class="small fw-bold text-dark">${service.refill ? 'Yes' : 'No'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer / Button -->
                        <div class="card-footer bg-white border-0 p-3">
                            <button type="button"
                                class="btn btn-primary btn-sm w-100 py-2 set-custom-price-btn"
                                data-service-id="${service.api_service_id}"
                                data-service-name="${escapeHtml(service.name)}"
                                data-base-price="${service.base_price}"
                                data-reseller-price="${service.price}"
                                style="border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); border: none; box-shadow: 0 4px 12px rgba(106, 17, 203, 0.2);">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Set Custom Price
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function updateStats(response) {
            $('#totalServices').text(response.total_rows);
            $('#showingServices').text(`${response.services.length} of ${response.total_rows}`);

            // Calculate average profit
            if (response.services.length > 0) {
                let totalProfitPercent = 0;
                response.services.forEach(s => {
                    const profit = s.price - s.base_price;
                    const profitPercent = s.base_price > 0 ? (profit / s.base_price) * 100 : 0;
                    totalProfitPercent += profitPercent;
                });
                const avgProfit = (totalProfitPercent / response.services.length).toFixed(2);
                $('#avgProfit').text(avgProfit + '%');
            }
        }

        function attachSearchListener() {
            $('#serviceSearch').off('input').on('input', function() {
                const keyword = this.value.toLowerCase().trim();
                let visibleCount = 0;

                $('.service-item').each(function() {
                    const name = $(this).data('name') || '';
                    const category = $(this).data('category') || '';
                    const type = $(this).data('type') || '';

                    const match = name.includes(keyword) ||
                        category.includes(keyword) ||
                        type.includes(keyword);

                    $(this).toggle(match);
                    if (match) visibleCount++;
                });

                // Update showing count based on search
                if (keyword) {
                    $('#showingServices').text(`${visibleCount} of ${allServices.length} (filtered)`);
                } else {
                    $('#showingServices').text(`${allServices.length} of ${totalServicesCount}`);
                }
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
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

        function showSuccess(message) {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",
                className: "toastify-custom"
            }).showToast();
        }

        // Custom price validation (this will work alongside your existing JS)
        $('#modalCustomPrice').on('input', function() {
            const customPrice = parseFloat($(this).val()) || 0;
            const basePrice = parseFloat($('#modalBasePrice').val()) || 0;

            if (customPrice < basePrice) {
                $('#modalPriceError').html('<i class="fa-solid fa-circle-exclamation me-1"></i>Price below base price will reduce your profit');
                $('#modalPriceInfo').html(`Your profit: ₦ ${(customPrice - basePrice).toFixed(2)}`);
            } else if (customPrice > basePrice) {
                $('#modalPriceError').empty();
                $('#modalPriceInfo').html(`<span class="text-success">Your profit: +₦ ${(customPrice - basePrice).toFixed(2)}</span>`);
            } else {
                $('#modalPriceError').empty();
                $('#modalPriceInfo').html('No profit (selling at base price)');
            }
        });

        // Add refresh on search clear
        $('#serviceSearch').on('search', function() {
            if (this.value === '') {
                $('#showingServices').text(`${allServices.length} of ${totalServicesCount}`);
            }
        });
    </script>
</body>

</html>
