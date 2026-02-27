<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

authOnly();

$userId = $_SESSION['user_id'] ?? null;

// Fetch orders
$stmt = $conn->prepare("
    SELECT id, order_id, user_id, reseller_id, cost, admin_profit, reseller_profit, otp,
           country, operator, phone_no, service, expiry_time, status, created_at, updated_at
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
                                <td>
                                    <?php if (!empty($order['phone_no'])): ?>
                                        <span class="phone-text" style="font-weight: bold; color: #1a73e8; cursor: pointer;" title="Click to copy">
                                            <?= htmlspecialchars($order['phone_no']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($order['country']) ?></td>
                                <td><?= htmlspecialchars($order['operator']) ?></td>
                                <td><?= number_format($order['cost'], 2) ?></td>

                                <span hidden class="expiry-timer" data-order-id="<?= $order['order_id'] ?>" data-status="<?= $order['status'] ?>"></span>

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
                                        <span id="otp-<?= $order['id'] ?>" class="otp-text"
                                            style="font-weight: bold; color: #1a73e8; cursor: pointer;"
                                            title="Click to copy">
                                            <?= htmlspecialchars($order['otp']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (in_array($order['status'], ['PENDING', 'RECEIVED'])): ?>
                                        <span class="expiry-timer"
                                            data-order-id="<?= htmlspecialchars($order['order_id']) ?>"
                                            data-expiry="<?= strtotime($order['expiry_time']) ?>"
                                            data-status="<?= $order['status'] ?>">
                                            Calculating...
                                        </span>
                                    <?php else: ?>
                                        ---
                                    <?php endif; ?>
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
        const statusColors = {
            'PENDING': 'text-warning',
            'RECEIVED': 'text-primary',
            'CANCELED': 'text-danger',
            'TIMEOUT': 'text-secondary',
            'FINISHED': 'text-success',
            'BANNED': 'text-danger'
        };

        function startOrderIntervals() {
            const csrfToken = $('#csrf_token').val();

            $('.expiry-timer').each(function() {
                const $el = $(this);
                const orderId = $el.data('order-id');
                let orderStatus = $el.data('status').toUpperCase();
                const expiryTimestamp = parseInt($el.data('expiry'));
                const $otpColumn = $('#otp-col-' + orderId);
                const $statusColumn = $('#status-col-' + orderId);

                if (!['PENDING', 'RECEIVED'].includes(orderStatus)) {
                    $el.text('---');
                    return;
                }

                if ($el.data('intervalId')) return; // already running

                const intervalId = setInterval(() => {
                    const now = Math.floor(Date.now() / 1000);
                    const remaining = expiryTimestamp - now;

                    // Countdown timer
                    if (remaining <= 0) {
                        $el.text('Expired').removeClass('text-success text-warning').addClass('text-danger');
                    } else {
                        const hours = Math.floor(remaining / 3600);
                        const minutes = Math.floor((remaining % 3600) / 60);
                        const seconds = remaining % 60;

                        let str = '';
                        if (hours > 0) str += hours + 'h ';
                        if (minutes > 0) str += minutes + 'm ';
                        str += seconds + 's';

                        const textClass = (remaining <= 600) ? 'text-warning' : 'text-success';
                        $el.removeClass('text-success text-warning').addClass(textClass).text(str + ' left');
                    }

                    // Poll AJAX every 10s
                    if (remaining > 0 && ['PENDING', 'RECEIVED'].includes(orderStatus)) {
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

                                if (res.otp) {
                                    $otpColumn.html('<span class="otp-text" style="font-weight:bold;color:#1a73e8;cursor:pointer" title="Click to copy">' + res.otp + '</span>');
                                    attachOtpCopy($otpColumn.find('.otp-text'));
                                }

                                if (res.order_status) {
                                    orderStatus = res.order_status.toUpperCase();
                                    $statusColumn.removeClass().addClass(statusColors[orderStatus] || 'text-dark').text(orderStatus);
                                }

                                if (res.otp || finalStatuses.includes(orderStatus)) {
                                    clearInterval($el.data('intervalId'));
                                    $el.removeData('intervalId');
                                    if (!res.otp && finalStatuses.includes(orderStatus)) {
                                        $otpColumn.text('---');
                                        $el.text('---').removeClass('text-success text-warning').addClass('text-danger');
                                    }
                                }
                            },
                            error: function(err) {
                                console.error('AJAX error for order', orderId, err);
                            }
                        });
                    }

                }, 10000);

                $el.data('intervalId', intervalId);
            });
        }

        // OTP copy handler
        function attachOtpCopy($el) {
            $el.off('click').on('click', function() {
                const otpText = $(this).text().trim();
                if (!otpText) return;
                navigator.clipboard.writeText(otpText).then(() => {
                    Toastify({
                        text: `OTP "${otpText}" copied!`,
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#4CAF50",
                    }).showToast();
                });
            });
        }

        $(document).ready(function() {
            startOrderIntervals();
        });
    </script>


    <script>
        $(document).ready(function() {
            $('.otp-text').on('click', function() {
                const otpText = $(this).text().trim();

                if (!otpText) return;

                // Copy to clipboard
                navigator.clipboard.writeText(otpText).then(() => {
                    // Optional: show toast / alert
                    Toastify({
                        text: `OTP "${otpText}" copied!`,
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#4CAF50",
                    }).showToast();
                }).catch(err => {
                    console.error('Failed to copy OTP:', err);
                });
            });



            $('.phone-text').on('click', function() {
                const phoneText = $(this).text().trim();
                if (!phoneText) return;

                navigator.clipboard.writeText(phoneText).then(() => {
                    Toastify({
                        text: `Phone number "${phoneText}" copied!`,
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#2196F3",
                    }).showToast();
                });
            });
        });
    </script>





    <script>
        function startExpiryCountdowns() {
            $('.expiry-timer').each(function() {
                const $el = $(this);
                const expiryTimestamp = parseInt($el.data('expiry'));
                const orderStatus = $el.data('status');

                // Update every second
                const timerInterval = setInterval(() => {
                    const now = Math.floor(Date.now() / 1000);
                    let remaining = expiryTimestamp - now;

                    if (remaining <= 0) {
                        $el.text('Expired').removeClass('text-success text-warning').addClass('text-danger');
                        clearInterval(timerInterval);
                        return;
                    }

                    const hours = Math.floor(remaining / 3600);
                    const minutes = Math.floor((remaining % 3600) / 60);
                    const seconds = remaining % 60;

                    let str = '';
                    if (hours > 0) str += hours + 'h ';
                    if (minutes > 0) str += minutes + 'm ';
                    str += seconds + 's';

                    const textClass = (remaining <= 600) ? 'text-warning' : 'text-success';
                    $el.removeClass('text-success text-warning').addClass(textClass).text(str + ' left');
                }, 1000);
            });
        }

        $(document).ready(function() {
            startExpiryCountdowns();
        });
    </script>
</body>

</html>
