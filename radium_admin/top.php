<?php
include ("auth.php");
if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $radium_token = $_COOKIE['remember_me'];
        $_SESSION['token'] = $radium_token;
    } else {
        header('location: login');
        exit;
    }
}
$admin_sql = mysqli_query($conn, "SELECT * FROM login_token WHERE token='" . $_SESSION['token'] . "'");
if (mysqli_num_rows($admin_sql) == 0) {
    header('location: login');
    exit;
} else {
    $admin_data = mysqli_fetch_array($admin_sql);
    $admin_sql2 = mysqli_query($conn, "SELECT * FROM user_data WHERE  id='" . $admin_data['user_id'] . "' AND status='1'");
    $final_admin = mysqli_fetch_array($admin_sql2);
    if ($final_admin['type'] == "admin") {
        $today_start = date('Y-m-d 00:00:00');

        $sql = "
    SELECT server_id, service_id, COUNT(*) as service_count
    FROM active_number
    WHERE status = 1 AND buy_time >= '$today_start'
    GROUP BY server_id, service_id
    ORDER BY service_count DESC
";

        $result = $conn->query($sql);

        if ($result === false) {
            die("Error: " . $conn->error);
        }

        $sql9 = "
    SELECT user_id, SUM(service_price) as total_amount, COUNT(*) as service_count
    FROM active_number
    WHERE status = 1 AND buy_time >= '$today_start'
    GROUP BY user_id
    ORDER BY total_amount DESC
";

        $result1 = $conn->query($sql9);

        if ($result1 === false) {
            die("Error: " . $conn->error);
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <title>Top - @getallscripts</title>
            <?php include ("include/head.php"); ?>
            <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

        </head>
        <script>
            $(document).ready(function () {
                // Remove "active" class from all <a> elements
                $('#dashboard').removeClass("active");

                // Add "active" class to the specific element with ID "faq"
                $("#top").addClass("active");
            });
        </script>

        <body id="page-top">
            <div id="wrapper">
                <!-- Sidebar -->
                <?php include ("include/slidebar.php"); ?>
                <!-- Sidebar -->
                <div id="content-wrapper" class="d-flex flex-column">
                    <div id="content">
                        <!-- TopBar -->
                        <?php include ("include/topbar.php"); ?>
                        <!-- Topbar -->

                        <!-- Container Fluid-->
                        <div class="container-fluid" id="container-wrapper">
                            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Top</li>
                                </ol>
                            </div>

                            <!---Container Fluid-->
                            <!-- Row -->
                            <div class="row">
                                <!-- Datatables -->
                                <div class="col">
                                    <div class="card mb-4">
                                        <div
                                            class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                            <h6 class="m-0 font-weight-bold text-primary">Today Service Sell History</h6>
                                        </div>
                                        <div class="table-responsive p-3">
                                            <table class="table align-items-center table-flush" id="dataTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Logo</th>
                                                        <th>Server</th>
                                                        <th>Service</th>
                                                        <th>Count</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $i = 1;
                                                    while ($data = mysqli_fetch_array($result)) {
                                                        $sql2 = mysqli_query($conn, "SELECT * FROM service_icon WHERE short_code='" . $data['service_id'] . "'");
                                                        if (mysqli_num_rows($sql2) == 0) {
                                                            $img = "https://i.ibb.co/ySRhxqh/default.png";
                                                        } else {
                                                            $sql3 = mysqli_fetch_assoc($sql2);
                                                            $img = $sql3['img_url'];
                                                        }
                                                        $sql4 = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $data['server_id'] . "'");
                                                        if (mysqli_num_rows($sql4) == 0) {
                                                            $server_name = "Deleted Server";
                                                        } else {
                                                            $sql5 = mysqli_fetch_assoc($sql4);
                                                            $server_name = $sql5['server_name'];
                                                        }
                                                        $sql6 = mysqli_query($conn, "SELECT * FROM service WHERE server_id='" . $data['server_id'] . "' AND service_id='" . $data['service_id'] . "'");
                                                        if (mysqli_num_rows($sql6) == 0) {
                                                            $service_name = "Deleted Service";
                                                        } else {
                                                            $sql7 = mysqli_fetch_assoc($sql6);
                                                            $service_name = $sql7['service_name'];
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td><img class="img-profile rounded-circle" src="<?php echo $img; ?>"
                                                                    style="width: 30px"> </td>
                                                            <td><?php echo $server_name ?>(<?php echo $data['server_id']; ?>)</td>
                                                            <td><?php echo $service_name ?>(<?php echo $data['service_id']; ?>)</td>
                                                            <td><?php echo $data['service_count']; ?></td>
                                                        </tr>
                                                        <?php
                                                        $i++;
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <!-- Datatables -->
                                <div class="col">
                                    <div class="card mb-4">
                                        <div
                                            class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                            <h6 class="m-0 font-weight-bold text-primary">Today Active User</h6>
                                        </div>
                                        <div class="table-responsive p-3">
                                            <table class="table align-items-center table-flush" id="dataTable2">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Email</th>
                                                        <th>Today Purchased</th>
                                                        <th>Today Otp</th>
                                                        <th>Balance</th>
                                                        <th>Total Recharge</th>
                                                        <th>Total Otp</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $today = date('Y-m-d');

                                                    $i = 1;
                                                    while ($data = mysqli_fetch_array($result1)) {
                                                        $user_id = $data['user_id'];
                                                        $sql10 = mysqli_query($conn, "SELECT * FROM user_data WHERE id='" . $user_id . "'");
                                                        $sql11 = mysqli_fetch_assoc($sql10);
                                                        $sql12 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $user_id . "'");
                                                        $sql13 = mysqli_fetch_assoc($sql12);
                                                        $sql14 = mysqli_query($conn, "SELECT COUNT(*) AS count FROM active_number WHERE user_id = '$user_id' AND DATE(buy_time) = '$today' AND status ='1'");
                                                        $sql15 = mysqli_fetch_assoc($sql14);
                                                        if ($sql11['status'] == "1") {
                                                            $status = "badge badge-success";
                                                            $status1 = "Active";
                                                        } else {
                                                            $status = "badge badge-danger";
                                                            $status1 = "Blocked";
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $sql11['email']; ?></td>
                                                            <td>₦<?php echo $data['total_amount']; ?></td>
                                                            <td><?php echo $sql15['count']; ?></td>
                                                            <td>₦<?php echo $sql13['balance']; ?></td>
                                                            <td>₦<?php echo $sql13['total_recharge']; ?></td>
                                                            <td><?php echo $sql13['total_otp']; ?></td>
                                                            <td><span class="<?php echo $status; ?>"><?php echo $status1; ?></span></td>
                                                            <td><a href="edit_user?user_id=<?php echo $user_id; ?>" class="btn btn-sm btn-primary">Edit</a></td>
                                                        </tr>
                                                        <?php
                                                        $i++;
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- Footer -->
                            <!-- <?php include ("include/copyright.php"); ?> -->
                            <!-- Footer -->
                        </div>
                    </div>

                    <!-- Scroll to top -->
                    <a class="scroll-to-top rounded" href="#page-top">
                        <i class="fas fa-angle-up"></i>
                    </a>
                    <?php include ("include/script.php"); ?>
                    <!-- Page level plugins -->
                    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
                    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
                    <script>
                        $(document).ready(function () {
                            $('#dataTable').DataTable({
                                ordering: false
                            });
                            $('#dataTable2').DataTable({
                                ordering: false
                            });
                            $('#dataTableHover').DataTable();
                        });
                    </script>
        </body>

        </html>
        <?php
    } else {
        header('location: login');
        exit;
    }
}
mysqli_close($conn);
?>