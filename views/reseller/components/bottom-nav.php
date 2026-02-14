<!-- views/components/bottom-nav.php -->
<nav class="bottom-nav mt-4 mb-2">
    <a href="/views/reseller/dashboard" class="nav-item <?= ($active ?? '') === 'dashboard' ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>
    <a href="/views/reseller/customer/manage" class="nav-item <?= ($active ?? '') === 'customers' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span>Customers</span>
    </a>
    <a href="/views/reseller/profit/manage" class="nav-item <?= ($active ?? '') === 'profit' ? 'active' : ''; ?>">
        <i class="fa-solid fa-coins"></i>
        <span>Profit</span>
    </a>

    <a href="/views/reseller/withdraw/manage" class="nav-item <?= ($active ?? '') === 'withdraw' ? 'active' : ''; ?>">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <span>Withdrawals</span>
    </a>


    <a href="/views/reseller/services/manage" class="nav-item <?= ($active ?? '') === 'services' ? 'active' : ''; ?>">
        <i class="fa-solid fa-cogs"></i>
        <span>Services</span>
    </a>
    <a href="/views/reseller/logs/manage" class="nav-item <?= ($active ?? '') === 'logs' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-lines"></i>
        <span>Logs</span>
    </a>
</nav>
