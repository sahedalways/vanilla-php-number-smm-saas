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



$stmt = $conn->prepare("
    SELECT id, name, username, email, phone
    FROM user_data
    WHERE type = ?
");

$type = 'customer';
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);

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
    <title>Manage Customers | Allsmsverify</title>
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
        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="d-flex justify-content-between mb-3 mt-5">
            <h4>Customers List</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                + Add New Customer
            </button>

        </div>

        <div class="container mt-4">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Phone</th>

                            <th>Actions</th> <!-- New column for buttons -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($customers) : ?>
                            <?php $index = 1; ?>
                            <?php foreach ($customers as $row) : ?>
                                <tr>
                                    <td><?= $index++; ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td><?= htmlspecialchars($row['phone']); ?></td>

                                    <td>
                                        <!-- Edit Button -->
                                        <a href="javascript:void(0);"
                                            class="btn btn-sm btn-primary me-1 edit-customer-btn"
                                            data-id="<?= $row['id']; ?>"
                                            data-name="<?= htmlspecialchars($row['name']); ?>"
                                            data-email="<?= htmlspecialchars($row['email']); ?>"
                                            data-phone="<?= htmlspecialchars($row['phone']); ?>"
                                            data-username="<?= htmlspecialchars($row['username']); ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        <!-- Delete Button -->
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['id']; ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 fw-semibold text-muted">
                                    No Customers Found Here
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>



                </table>
            </div>


        </div>






        <div class="modal fade" id="addCustomerModal"
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
                                Add New Customer
                            </h5>
                            <small class="text-muted">Fill in the details to register a new customer account.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">

                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label class="form-label-premium">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" id="name" name="name"
                                        class="form-control form-control-premium border-start-0"
                                        placeholder="John Doe">
                                </div>
                                <div id="error-name" class="text-danger small mt-1"></div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label-premium">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" id="email" name="email"
                                        class="form-control form-control-premium border-start-0"
                                        placeholder="email@example.com">
                                </div>
                                <div id="error-email" class="text-danger small mt-1"></div>
                            </div>



                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label-premium">
                                    Phone Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input type="tel" id="phone" name="phone"
                                        class="form-control form-control-premium border-start-0"
                                        placeholder="0123456789"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div id="error-phone" class="text-danger small mt-1"></div>
                            </div>

                            <!-- Divider -->
                            <div class="col-12 mt-4">
                                <hr class="text-muted opacity-25">
                                <h6 class="fw-bold mb-3 text-dark">Security & Access</h6>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label-premium">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" class="form-control form-control-premium" id="password" placeholder="Enter your password" required minlength="6">
                                    <i class="bi bi-eye-slash toggle-password position-absolute top-50 end-0 translate-middle-y me-3" id="togglePassword" style="cursor:pointer;"></i>
                                </div>
                                <div id="error-password" class="text-danger small mt-1"></div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label-premium">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" class="form-control form-control-premium" id="confirm_password" placeholder="Confirm your password" required minlength="6">
                                    <i class="bi bi-eye-slash toggle-password position-absolute top-50 end-0 translate-middle-y me-3" id="toggleConfirmPassword" style="cursor:pointer;"></i>
                                </div>
                                <div id="error-confirm_password" class="text-danger small mt-1"></div>
                            </div>


                        </div>
                    </div>


                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="register" class="btn btn-success btn-create px-5">
                            <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Create Customer Account</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>


                </div>
            </div>
        </div>









        <div class="modal fade" id="editCustomerModal"
            tabindex="-1"
            aria-hidden="true"
            data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content modal-content-premium">

                    <div class="modal-header modal-header-premium">
                        <h5 class="modal-title fw-bold mb-0 text-success">
                            <i class="bi bi-person-fill me-2 text-success"></i>
                            Edit Customer
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <input type="hidden" id="customer_id">
                        <div class="row g-4">

                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label class="form-label-premium">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" id="edit_name" class="form-control form-control-premium border-start-0" placeholder="John Doe">
                                </div>
                                <div id="error-edit_name" class="text-danger small mt-1"></div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label-premium">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" id="edit_email" class="form-control form-control-premium border-start-0" placeholder="email@example.com">
                                </div>
                                <div id="error-edit_email" class="text-danger small mt-1"></div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label-premium">Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input type="tel" id="edit_phone" class="form-control form-control-premium border-start-0" placeholder="0123456789" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <div id="error-edit_phone" class="text-danger small mt-1"></div>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label class="form-label-premium">Password (leave blank to keep current)</label>
                                <div class="position-relative">
                                    <input type="password" id="edit_password" class="form-control form-control-premium" placeholder="Enter new password" minlength="6">
                                    <i class="bi bi-eye-slash toggle-password position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;"></i>
                                </div>
                                <div id="error-edit_password" class="text-danger small mt-1"></div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label class="form-label-premium">Confirm Password</label>
                                <div class="position-relative">
                                    <input type="password" id="edit_confirm_password" class="form-control form-control-premium" placeholder="Confirm new password" minlength="6">
                                    <i class="bi bi-eye-slash toggle-password position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;"></i>
                                </div>
                                <div id="error-edit_confirm_password" class="text-danger small mt-1"></div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="updateCustomer" class="btn btn-success btn-create px-5">
                            <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Update Customer</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>




        <?php
        $active = 'customers';
        include __DIR__ . '/../components/bottom-nav.php';
        ?>

    </div>

    <script src="/js/admin/manage-customer.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>
