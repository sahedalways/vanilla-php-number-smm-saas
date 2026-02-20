<style>
    .custom-header {
        background-color: #050a1e;
        border-radius: 20px;
        padding: 15px 25px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 custom-header text-white">
    <div>
        <small class="text-light opacity-75">Welcome back,</small>
        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($userName); ?></h5>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-outline-light border-0 rounded-circle">
            <i class="fa-regular fa-sun"></i>
        </button>
        <button class="btn btn-outline-light border-0 rounded-circle position-relative">
            <i class="fa-regular fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                0
            </span>
        </button>
        <a href="/logout" class="btn btn-danger btn-sm rounded-pill fw-bold ms-2 px-3">Logout</a>
    </div>
</div>
