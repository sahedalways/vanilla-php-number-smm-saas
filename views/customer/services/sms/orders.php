<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

authOnly();

$userId = $_SESSION['user_id'] ?? null;

// Fetch orders
$stmt = $conn->prepare("
    SELECT id, order_id, user_id, reseller_id, cost, admin_profit, reseller_profit, otp,
           country, operator, phone_no, service, expiry_time, otp_in_time, status, created_at, updated_at
    FROM sms_orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$orders = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$userName = $_SESSION['name'] ?? 'User';
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
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../../css/customer_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="main-wrapper p-3">
        <?php include __DIR__ . '/../../components/header.php'; ?>
        <input type="hidden" id="csrf_token" value="<?= $csrf_token ?>">

        <h5 class="mb-3">My Purchased SMS Orders</h5>

        <?php if ($orders): ?>
            <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
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
                            <th>Expire Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_id']) ?></td>
                                <td><?= htmlspecialchars($order['service']) ?></td>
                                <td><?= htmlspecialchars($order['phone_no'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($order['country']) ?></td>
                                <td><?= htmlspecialchars($order['operator']) ?></td>
                                <td><?= number_format($order['cost'], 2) ?></td>

                                <td>
                                    <span class="expiry-timer"
                                        data-otp="<?= date('c', strtotime($order['otp_in_time'])) ?>"
                                        data-order-id="<?= htmlspecialchars($order['order_id']) ?>" data-status="<?= $order['status'] ?>">

                                    </span>
                                </td>

                                <td id="status-col-<?= $order['order_id'] ?>">
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

                                <td style="position: relative; min-width: 120px;" id="otp-col-<?= $order['order_id'] ?>">
                                    <?php if (!empty($order['otp'])): ?>
                                        <span id="otp-<?= $order['id'] ?>" class="otp-text" style="font-weight: bold; color: #1a73e8; cursor: pointer;">
                                            <?= htmlspecialchars($order['otp']) ?>
                                        </span>
                                        <button class="btn btn-sm btn-outline-secondary copy-otp-btn"
                                            data-otp-id="otp-<?= $order['id'] ?>"
                                            style="margin-left: 5px; font-size: 0.75rem;">
                                            Copy
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $expiryTimestamp = strtotime($order['expiry_time']);
                                    $now = time();
                                    $remainingSeconds = $expiryTimestamp - $now;

                                    if ($remainingSeconds <= 0) {
                                        // Already expired
                                        echo '<span class="text-danger">Expired</span>';
                                    } else {

                                        $hours = floor($remainingSeconds / 3600);
                                        $minutes = floor(($remainingSeconds % 3600) / 60);
                                        $seconds = $remainingSeconds % 60;


                                        $timeLeftStr = '';
                                        if ($hours > 0) $timeLeftStr .= $hours . 'h ';
                                        if ($minutes > 0) $timeLeftStr .= $minutes . 'm ';
                                        $timeLeftStr .= $seconds . 's';


                                        $textClass = ($remainingSeconds <= 600) ? 'text-warning' : 'text-success';

                                        echo "<span class='{$textClass}'>{$timeLeftStr} left</span>";
                                    }
                                    ?>
                                </td>
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


    <script>
        function formatTime(seconds) {
            if (seconds <= 0) return "---";
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return m + 'm ' + s + 's';
        }

        function startTimers() {
            const csrfToken = $('#csrf_token').val();

            $('.expiry-timer').each(function() {
                const $el = $(this);
                const otpTimeStr = $el.data('otp');
                const orderId = $el.data('order-id');
                const orderStatus = $el.data('status'); // initial status
                const $otpColumn = $('#otp-col-' + orderId); // OTP field td
                const $statusColumn = $('#status-col-' + orderId); // Status field td

                const otpIn = Math.floor(new Date(otpTimeStr).getTime() / 1000);
                if (isNaN(otpIn)) return;

                // Countdown interval
                let timerInterval;
                if (['PENDING', 'RECEIVED'].includes(orderStatus)) {
                    timerInterval = setInterval(() => {
                        const now = Math.floor(Date.now() / 1000);
                        const remainingOtp = otpIn - now;
                        $el.text(remainingOtp > 0 ? formatTime(remainingOtp) : 'Expired');

                        if (remainingOtp <= 0) {
                            clearInterval(timerInterval);
                        }
                    }, 1000);

                    // AJAX interval
                    const ajaxInterval = setInterval(() => {
                        const now = Math.floor(Date.now() / 1000);
                        if (now >= otpIn) {
                            clearInterval(ajaxInterval);
                            clearInterval(timerInterval);
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
                                const finalStatuses = ['CANCELED', 'TIMEOUT', 'BANNED', 'FINISHED'];


                                if (finalStatuses.includes(res.order_status)) {
                                    clearInterval(timerInterval);
                                    clearInterval(ajaxInterval);

                                }

                                if (res.otp) {
                                    clearInterval(timerInterval);
                                    clearInterval(ajaxInterval);

                                    $otpColumn.text(res.otp);
                                    $statusColumn.text(res.order_status);
                                }

                                if (res.order_status) {
                                    $statusColumn.text(res.order_status);
                                }
                            },
                            error: function(err) {
                                console.error('AJAX error for order', orderId, err);
                            }
                        });
                    }, 5000); // every 5 seconds
                }
            });
        }

        $(document).ready(function() {
            startTimers();
        });
    </script>
</body>

</html>
