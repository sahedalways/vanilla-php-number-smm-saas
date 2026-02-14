<?php
require_once 'helpers/session.php';
require_once 'include/config.php';
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'reseller') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Reseller';



if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$stmt = $conn->prepare("SELECT balance FROM user_data WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$balance = $user['balance'] ?? 0.00;

$stmt = $conn->prepare("
    SELECT id, bank_name, account_name, account_number, swift_code
    FROM reseller_bank_infos
    WHERE reseller_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$bank = $result->fetch_assoc();

$bank_id = $bank['id'] ?? '';
$bank_name = $bank['bank_name'] ?? '';
$account_name = $bank['account_name'] ?? '';
$account_number = $bank['account_number'] ?? '';
$swift_code = $bank['swift_code'] ?? '';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Balance | Allsmsverify</title>
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


        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
            <h4 class="mb-0">Withdraw Requests List</h4>

            <div class="text-center">
                <span class="fw-semibold">Wallet Balance:</span>
                <span class="text-success fw-bold">₦<?= number_format($balance, 2) ?></span>
            </div>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWithdrawRequestModal">
                + Withdraw Request
            </button>
        </div>


        <div class="container mt-4">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Bank Name</th>
                            <th>Account Name</th>
                            <th>Account No.</th>
                            <th>Swift Code</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $requests = $conn->prepare("
    SELECT r.*, u.name, u.username
    FROM reseller_withdraw_requests r
    JOIN user_data u ON r.reseller_id = u.id
    WHERE r.reseller_id = ?
    ORDER BY r.created_at DESC
");
                        $requests->bind_param("i", $userId);
                        $requests->execute();
                        $result = $requests->get_result();

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                                <tr id="request-<?= $row['id'] ?>">
                                    <td><?= $row['id'] ?></td>

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



            <div class="modal fade" id="addWithdrawRequestModal"
                tabindex="-1"
                aria-hidden="true"
                data-bs-backdrop="static"
                data-bs-keyboard="false">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content modal-content-premium">

                        <div class="modal-header modal-header-premium">
                            <div>
                                <h5 class="modal-title fw-bold mb-0 text-success">
                                    <i class="bi bi-person-fill-add me-2 text-success"></i>
                                    New Withdraw Request
                                </h5>

                            </div>
                            <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-4">

                                <!-- Bank Name -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">
                                        Bank Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?= htmlspecialchars($bank_name); ?>">
                                    <div id="error-bank_name" class="text-danger small mt-1"></div>
                                </div>

                                <!-- Account Name -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">
                                        Account Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="account_name" name="account_name" class="form-control" value="<?= htmlspecialchars($account_name); ?>">
                                    <div id="error-account_name" class="text-danger small mt-1"></div>
                                </div>

                                <!-- Account Number -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">
                                        Account Number (10 Digit) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="account_number" name="account_number" maxlength="10" class="form-control" value="<?= htmlspecialchars($account_number); ?>">
                                    <div id="error-account_number" class="text-danger small mt-1"></div>
                                </div>

                                <!-- SWIFT Code -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">
                                        SWIFT Code
                                    </label>
                                    <input type="text" id="swift_code" name="swift_code" class="form-control" value="<?= htmlspecialchars($swift_code); ?>">
                                    <div id="error-swift_code" class="text-danger small mt-1"></div>
                                </div>



                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">
                                        Amount <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" id="withdraw_amount" name="withdraw_amount" class="form-control" step="0.01" min="0">
                                    <div id="error-withdraw_amount" class="text-danger small mt-1"></div>
                                </div>





                            </div>
                        </div>


                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="sendWithdrawRequest" class="btn btn-success btn-create px-5">
                                <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Send Withdraw Request</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                            </button>
                        </div>


                    </div>
                </div>
            </div>

            <?php
            $active = 'withdraw';
            include __DIR__ . '/../components/bottom-nav.php';
            ?>

        </div>

        <script src="/js/reseller/manage-withdraw.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
