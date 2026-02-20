<style>
    .bg-navy {
        background-color: #001f3f;

        border-radius: 15px;

    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 bg-navy p-3">
    <div>
        <small class="text-white-50">Welcome back,</small>
        <h5 class="fw-bold mb-0 text-white"><?php echo htmlspecialchars($userName); ?></h5>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-outline-light border-0 rounded-circle">
            <i class="fa-regular fa-sun"></i>
        </button>

        <button class="btn btn-outline-light border-0 rounded-circle position-relative">
            <i class="fa-regular fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">6</span>
        </button>

        <a href="/logout" class="btn btn-danger btn-sm rounded-pill fw-bold ms-2 px-3">Logout</a>
    </div>
</div>
