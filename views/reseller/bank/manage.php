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
    <title>Manage Bank Info | Allsmsverify</title>
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


        <div class="container mt-5">

            <div class="card p-5 shadow-lg rounded-4 border-0">
                <h4 class="fw-bold mb-4 text-center text-primary"> Your Bank Info</h4>


                <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" id="bank_id" value="123">

                <!-- Bank Name -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Bank Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?= htmlspecialchars($bank_name); ?>">
                    <div id="error-bank_name" class="text-danger small mt-1"></div>
                </div>

                <!-- Account Name -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Account Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="account_name" name="account_name" class="form-control" value="<?= htmlspecialchars($account_name); ?>">
                    <div id="error-account_name" class="text-danger small mt-1"></div>
                </div>

                <!-- Account Number -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Account Number (10 Digit) <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="account_number" name="account_number" maxlength="10" class="form-control" value="<?= htmlspecialchars($account_number); ?>">
                    <div id="error-account_number" class="text-danger small mt-1"></div>
                </div>

                <!-- SWIFT Code -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        SWIFT Code
                    </label>
                    <input type="text" id="swift_code" name="swift_code" class="form-control" value="<?= htmlspecialchars($swift_code); ?>">
                    <div id="error-swift_code" class="text-danger small mt-1"></div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">

                    <button type="button" id="updateBankAccount" class="btn btn-success px-5">
                        <span class="btn-text">
                            <i class="bi bi-check-circle me-2"></i>Update Bank Account
                        </span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </div>


            </div>


        </div>


        <?php
        $active = 'bank';
        include __DIR__ . '/../components/bottom-nav.php';
        ?>

    </div>

    <script src="/js/reseller/manage-bank.js"></script>

</body>

</html>
