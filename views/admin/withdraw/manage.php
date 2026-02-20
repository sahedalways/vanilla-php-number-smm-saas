<?php
require_once 'helpers/session.php';
require_once 'include/config.php';
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Admin';



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
    <title>Manage Withdraw Requests | Foreign sms</title>
    <link rel="shortcut icon" href="<?php echo $WEBSITE_URL; ?>/images/logo-png.png" type="image/x-icon">
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
    <link rel="stylesheet" href="/css/manage_reseller.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>
    <div class="main-wrapper p-3">
        <!-- Header -->
        <?php
        include __DIR__ . '/../components/header.php';
        ?>


        <div class="d-flex justify-content-between mb-3 mt-5">
            <h4>Withdraw Requests List</h4>

        </div>

        <div class="container mt-4">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reseller</th>
                            <th>Amount</th>
                            <th>Bank</th>
                            <th>Account Name</th>
                            <th>Account No.</th>
                            <th>Swift Code</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $requests = $conn->query("SELECT r.*, u.name, u.username FROM reseller_withdraw_requests r JOIN user_data u ON r.reseller_id=u.id ORDER BY r.created_at DESC");

                        if ($requests->num_rows > 0):
                            while ($row = $requests->fetch_assoc()):
                        ?>
                                <tr id="request-<?= $row['id'] ?>">
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['username']) ?>)</td>
                                    <td>₦<?= $row['amount'] ?></td>
                                    <td><?= htmlspecialchars($row['bank_name']) ?></td>
                                    <td><?= htmlspecialchars($row['account_name']) ?></td>
                                    <td><?= htmlspecialchars($row['account_number']) ?></td>
                                    <td><?= htmlspecialchars($row['swift_code']) ?></td>
                                    <td id="status-<?= $row['id'] ?>">
                                        <?php
                                        $status = $row['status'];
                                        $badgeClass = match ($status) {
                                            'pending' => 'bg-warning text-dark',
                                            'approved' => 'bg-success text-white',
                                            'rejected' => 'bg-danger text-white',
                                            default => 'bg-secondary text-white'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <button class="btn btn-success btn-sm" onclick="approveRequest(<?= $row['id'] ?>)">Approve</button>
                                            <button class="btn btn-danger btn-sm" onclick="rejectRequest(<?= $row['id'] ?>)">Reject</button>
                                        <?php else: ?>
                                            <span class="text-muted">No action</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No withdraw requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
                <input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            </div>




            <?php
            $active = 'withdraw';
            include __DIR__ . '/../components/bottom-nav.php';
            ?>

        </div>

        <script src="/js/admin/manage-withdraw.js"></script>
</body>

</html>
