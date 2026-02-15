<!-- views/components/bottom-nav.php -->
<nav class="bottom-nav mt-4 mb-2">
    <a href="/views/admin/dashboard" class="nav-item <?= ($active ?? '') === 'dashboard' ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>
    <a href="/views/admin/resellers/manage" class="nav-item <?= ($active ?? '') === 'reseller' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span>Resellers</span>
    </a>

    <a href="/views/admin/customer/manage" class="nav-item <?= ($active ?? '') === 'customers' ? 'active' : ''; ?>">
        <i class="fa-solid fa-user"></i>
        <span>Customers</span>
    </a>
    <a href="/views/admin/profit/manage" class="nav-item <?= ($active ?? '') === 'profit' ? 'active' : ''; ?>">
        <i class="fa-solid fa-coins"></i>
        <span>Profit</span>
    </a>

    <a href="/views/admin/withdraw/manage" class="nav-item <?= ($active ?? '') === 'withdraw' ? 'active' : ''; ?>">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <span>Withdrawals</span>
    </a>


    <a href="/views/admin/services/manage" class="nav-item <?= ($active ?? '') === 'services' ? 'active' : ''; ?>">
        <i class="fa-solid fa-cogs"></i>
        <span>Services</span>
    </a>
    <a href="/views/admin/logs/manage" class="nav-item <?= ($active ?? '') === 'logs' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-lines"></i>
        <span>Logs</span>
    </a>
</nav>
