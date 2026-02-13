<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/customer_dashboard.css">
</head>

<body>
    <div class="main-wrapper p-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <small class="text-light">Welcome back,</small>
                <h5 class="fw-bold mb-0">Md Sariful Islam</h5>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary border-0 rounded-circle text-white">
                    <i class="fa-regular fa-sun"></i>
                </button>
                <button class="btn btn-outline-secondary border-0 rounded-circle text-white position-relative">
                    <i class="fa-regular fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">6</span>
                </button>
            </div>
        </div>

        <div class="wallet-card mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-wallet me-2"></i> Main Balance</span>
                <div class="bg-dark bg-opacity-50 rounded-pill p-1 d-flex gap-1" style="font-size: 0.8rem;">

                    <span class="px-3 py-1 text-white">NGN</span>
                </div>
            </div>
            <div class="balance-amount">₦0.00</div>
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-light w-100 py-2 fw-bold text-primary"><i class="fa-solid fa-plus me-1"></i> Top Up</button>
                </div>
                <div class="col-6">
                    <button class="btn btn-primary w-100 py-2 border-0" style="background: rgba(255,255,255,0.2);"><i class="fa-solid fa-clock-rotate-left me-1"></i> History</button>
                </div>
            </div>
        </div>

        <h6 class="fw-bold mb-3">Quick Services</h6>
        <div class="row g-3">
            <div class="col-3 text-center">
                <div class="service-card">
                    <div class="icon-box text-primary"><i class="fa-solid fa-mobile-screen"></i></div>
                    <div style="font-size: 0.75rem;">Airtime</div>
                </div>
            </div>
        </div>
    </div>

    <nav class="bottom-nav mb-2">
        <a href="#" class="nav-item active">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-rocket"></i>
            <span>Boosting</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-mobile-button"></i>
            <span>Buy Number</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-file-lines"></i>
            <span>Logs</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

</body>

</html>
