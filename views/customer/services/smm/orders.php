<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'User';


$stmt = $conn->prepare("
    SELECT o.id, s.name as service_name, o.quantity, o.cost, o.status, o.remains, o.created_at
    FROM smm_orders o
    LEFT JOIN services s ON o.service_id = s.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$orders = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My SMM Orders | Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../../css/customer_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="main-wrapper p-3">

        <?php include __DIR__ . '/../../components/header.php'; ?>

        <h5 class="mb-3">My Purchased SMM Orders</h5>

        <?php if ($orders): ?>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Quantity</th>
                            <th>Cost (₦)</th>
                            <th>Status</th>
                            <th>Remains</th>
                            <th>Ordered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td><?php echo number_format($order['cost'], 2); ?></td>
                                <?php

                                $statusColors = [
                                    'processing' => 'text-primary',
                                    'success'    => 'text-success',
                                    'failed'     => 'text-danger',
                                    'partial'    => 'text-warning',
                                    'rejected'   => 'text-secondary'
                                ];
                                ?>

                                <td class="<?php echo $statusColors[strtolower($order['status'])] ?? 'text-dark'; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </td>

                                <td><?php echo intval($order['remains']); ?></td>
                                <td><?php echo $order['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="alert alert-info">You have not purchased any SMM services yet.</div>
        <?php endif; ?>

    </div>

    <?php
    $active = 'orders';
    include __DIR__ . '/../../components/bottom-nav.php';
    ?>
</body>

</html>
