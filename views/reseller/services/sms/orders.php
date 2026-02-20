<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Admin';

// Fetch orders from sms_orders
$stmt = $conn->prepare("
    SELECT o.id, o.service_id, o.user_id, o.reseller_id, o.cost, o.admin_profit, o.reseller_profit,o.order_id,o.otp,
           o.country, o.operator, o.phone_no, o.service, o.expiry_time, o.status, o.created_at, o.updated_at
    FROM sms_orders o
    WHERE o.reseller_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$orders = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();


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
    <title>Customer SMS Orders | Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../../css/reseller_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="main-wrapper p-3">

        <?php include __DIR__ . '/../../components/header.php'; ?>
        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">


        <h5 class="mb-3">Customer Purchased SMS Orders</h5>

        <?php if ($orders): ?>
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Operator</th>
                            <th>Cost (₦)</th>
                            <th>Status</th>
                            <th>OTP</th>
                            <th>Ordered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['service']) ?></td>
                                <td><?= htmlspecialchars($order['phone_no'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($order['country']) ?></td>
                                <td><?= htmlspecialchars($order['operator']) ?></td>
                                <td><?= number_format($order['cost'], 2) ?></td>



                                <td>
                                    <?php
                                    $statusColors = [
                                        'PENDING'   => 'text-warning',
                                        'RECEIVED'  => 'text-primary',
                                        'CANCELED'  => 'text-danger',
                                        'TIMEOUT'   => 'text-secondary',
                                        'FINISHED'  => 'text-success',
                                        'BANNED'    => 'text-danger',
                                    ];
                                    $statusClass = $statusColors[$order['status']] ?? 'text-dark';
                                    ?>
                                    <span class="<?= $statusClass ?>"><?= ucfirst(strtolower($order['status'])) ?></span>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($order['otp'])) {
                                        echo htmlspecialchars($order['otp']);
                                    } elseif ($order['status'] === 'PENDING' || $order['status'] === 'RECEIVED') {
                                        echo 'OTP in...';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td><?= $order['created_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No purchased any SMS services yet.</div>
        <?php endif; ?>

    </div>

    <?php
    $active = 'sms';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>

</body>

</html>
