<!-- views/components/bottom-nav.php -->
<nav class="bottom-nav mt-4 mb-2">

    <a href="/views/customer/dashboard" class="nav-item <?= ($active ?? '') === 'dashboard' ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>

    <!-- NEW: Phone Numbers -->
    <a href="/views/customer/numbers/manage" class="nav-item <?= ($active ?? '') === 'numbers' ? 'active' : ''; ?>">
        <i class="fa-solid fa-phone"></i>
        <span>Numbers</span>
    </a>

    <a href="/views/customer/services/smm/manage" class="nav-item <?= ($active ?? '') === 'smm' ? 'active' : ''; ?>">
        <i class="fa-solid fa-cogs"></i>
        <span>Services</span>
    </a>

    <a href="/views/customer/logs/manage" class="nav-item <?= ($active ?? '') === 'logs' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-lines"></i>
        <span>Logs</span>
    </a>

</nav>
