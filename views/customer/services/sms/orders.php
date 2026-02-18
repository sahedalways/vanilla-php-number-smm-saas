<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

authOnly();

$userId = $_SESSION['user_id'] ?? null;

// Fetch orders from sms_orders
$stmt = $conn->prepare("
    SELECT o.id, o.service_id, o.user_id, o.reseller_id, o.cost, o.admin_profit, o.reseller_profit,o.order_id,o.otp,
           o.country, o.operator, o.phone_no, o.service, o.expiry_time, o.status, o.created_at, o.updated_at
    FROM sms_orders o
    WHERE o.user_id = ?
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
    <title>My SMS Orders | Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../../css/customer_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body>
    <div class="main-wrapper p-3">

        <?php include __DIR__ . '/../../components/header.php'; ?>
        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">


        <h5 class="mb-3">My Purchased SMS Orders</h5>

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
                            <th>OTP IN</th>
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
                                    <span class="expiry-timer"
                                        data-expiry="<?= strtotime($order['expiry_time']) ?>">
                                        <!-- JS will fill countdown here -->
                                    </span>
                                </td>

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
            <div class="alert alert-info">You have not purchased any SMS services yet.</div>
        <?php endif; ?>

    </div>

    <?php
    $active = 'orders';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>












    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function formatTime(seconds) {
            if (seconds <= 0) return "Expired";
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return `${h>0?h+'h ':''}${m>0?m+'m ':''}${s}s`;
        }


        function updateTimers() {
            const csrfToken = $('#csrf_token').val();

            $('.expiry-timer').each(function() {
                const $el = $(this);
                const expiry = parseInt($el.data('expiry'));
                const orderId = $el.closest('tr').find('td:first').text();
                const now = Math.floor(Date.now() / 1000);
                const remaining = expiry - now;

                if (remaining > 0) {
                    $el.text(formatTime(remaining));

                    if (!$el.data('ajaxScheduled')) {
                        $el.data('ajaxScheduled', true);

                        const ajaxInterval = setInterval(function() {

                            const currentRemaining = expiry - Math.floor(Date.now() / 1000);
                            if (currentRemaining <= 0) {
                                clearInterval(ajaxInterval);
                                $el.text('Expired');
                                return;
                            }

                            $.ajax({
                                url: '/controllers/customer/services/sms/check-status',
                                method: 'POST',
                                data: {
                                    order_id: orderId,
                                    csrf_token: csrfToken
                                },
                                dataType: 'json',
                                success: function(res) {
                                    location.reload();
                                }
                            });
                        }, 5 * 60 * 1000);
                    }

                } else {
                    $el.text('Expired');

                    $el.data('ajaxScheduled', true);
                }
            });
        }

        // Run every second to update countdown
        setInterval(updateTimers, 1000);

        function formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return m + 'm ' + s + 's';
        }
    </script>

</body>

</html>
