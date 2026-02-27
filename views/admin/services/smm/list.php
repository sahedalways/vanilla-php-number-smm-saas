<?php
require_once 'helpers/session.php';
require_once 'include/config.php';

if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: /");
    exit;
}

$userName = $_SESSION['name'] ?? 'Admin';
authOnly();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin | All SMM Services</title>
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">
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
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            height: 100%;
        }

        .skeleton-icon {
            width: 46px;
            height: 46px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .skeleton-badge {
            width: 80px;
            height: 24px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 20px;
            margin-bottom: 15px;
        }

        .skeleton-title {
            height: 46px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .skeleton-price {
            height: 45px;
            width: 60%;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .skeleton-info {
            height: 80px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .skeleton-limits {
            height: 60px;
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
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            border: none;
            padding: 12px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
        }

        .load-more-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
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
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .stats-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-icon {
            width: 40px;
            height: 40px;
            background: #ffffff;
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
</head>

<body>
    <div class="container my-4">
        <?php include __DIR__ . '/../../components/header.php'; ?>

        <div class="d-flex align-items-center mb-4">
            <div style="width: 5px; height: 30px; background: #0d6efd; border-radius: 10px; margin-right: 15px;"></div>
            <h4 class="mb-0" style="font-weight: 800; color: #1e293b;">All SMM Services</h4>
        </div>

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
                <button class="btn btn-primary" onclick="location.reload()" style="border-radius: 12px; padding: 12px 25px;">
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
                        <div class="skeleton-icon"></div>
                        <div class="skeleton-badge"></div>
                        <div class="skeleton-title"></div>
                        <div class="skeleton-price"></div>
                        <div class="skeleton-info"></div>
                        <div class="skeleton-limits"></div>
                    </div>
                </div>
            <?php endfor; ?>
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
                <i class="fa-regular fa-circle-check me-2"></i>You've viewed all services
            </div>
        </div>
    </div>

    <?php
    $active = 'smm';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>

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
                url: '/controllers/admin/services/smm/get-services',
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
            const delay = index * 0.05;
            const profit = service.base_price - service.api_price;
            const profitPercent = service.api_price > 0 ? ((profit / service.api_price) * 100).toFixed(2) : 0;
            const isLowProfit = profitPercent < 5;

            return `
                <div class="col-md-6 col-lg-4 service-item"
                    data-name="${service.name.toLowerCase()}"
                    data-category="${service.category ? service.category.toLowerCase() : ''}"
                    data-type="${service.type ? service.type.toLowerCase() : ''}"
                    style="animation: zoomIn 0.5s ease forwards; animation-delay: ${delay}s; opacity: 0;">

                    <div class="card service-card h-100 border-0">
                        <div class="card-body p-4">
                            <!-- Top Row -->
                            <div class="d-flex justify-content-between align-items-center mb-3">

                                ${isLowProfit ?
                                    '<span class="badge bg-danger-subtle text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Low Profit</span>' :
                                    service.status === 'inactive' ?
                                    '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>' :
                                    '<span class="badge bg-success-subtle text-success">Active</span>'
                                }
                            </div>

                            <!-- Service Name -->
                            <h6 class="service-title fw-bold mb-2">
                                ${escapeHtml(service.name)}
                            </h6>

                            <!-- Badges -->
                            <div class="mb-3 d-flex flex-wrap gap-1">
                                <span class="badge bg-secondary-subtle text-dark">
                                    ${escapeHtml(service.type || 'Default')}
                                </span>
                                <span class="badge bg-info-subtle text-dark">
                                    ${escapeHtml(service.category || 'Uncategorized')}
                                </span>
                                ${service.cancel ?
                                    '<span class="badge bg-warning-subtle text-dark">Cancelable</span>' : ''}
                                ${service.refill ?
                                    '<span class="badge bg-success-subtle text-success">Refill</span>' : ''}
                            </div>

                            <!-- Base Price -->
                            <div class="price-box mb-3">
                                <small class="label">Base Price</small>
                                <h4 class="price mb-0">
                                    ₦ ${parseFloat(service.base_price).toFixed(2)}
                                </h4>
                            </div>

                            <!-- API + Profit -->
                            <div class="info-box mb-3">
                                <div class="row g-0">
                                    <div class="col-6 text-center border-end">
                                        <small class="mini-label">API Price</small>
                                        <div class="value">
                                            ₦ ${parseFloat(service.api_price).toFixed(2)}
                                        </div>
                                    </div>
                                    <div class="col-6 text-center">
                                        <small class="mini-label">Admin Profit</small>
                                        <div class="value" style="color:${profit >= 0 ? '#16a34a' : '#dc2626'};">
                                            ₦ ${profit.toFixed(2)}
                                        </div>
                                        <small class="percent">
                                            ${profitPercent}%
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Min Max -->
                            <div class="limit-box">
                                <div class="row g-0">
                                    <div class="col-6 text-center border-end">
                                        <small class="mini-label">Min</small>
                                        <div class="value">${service.min.toLocaleString()}</div>
                                    </div>
                                    <div class="col-6 text-center">
                                        <small class="mini-label">Max</small>
                                        <div class="value">${service.max.toLocaleString()}</div>
                                    </div>
                                </div>
                            </div>
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
                    const profit = s.base_price - s.api_price;
                    const profitPercent = s.api_price > 0 ? (profit / s.api_price) * 100 : 0;
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
            alert(message);
        }

        // Add refresh on search clear
        $('#serviceSearch').on('search', function() {
            if (this.value === '') {
                $('#showingServices').text(`${allServices.length} of ${totalServicesCount}`);
            }
        });
    </script>
</body>

</html>
