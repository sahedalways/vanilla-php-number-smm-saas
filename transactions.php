<?php
session_start();


require_once 'helpers/session.php';
require_once 'include/config.php';
require __DIR__ . '/class/class.control.php';
if (!isset($_SESSION['type']) || !in_array($_SESSION['type'], ['customer', 'reseller'])) {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $back");
    exit;
}

authOnly();



$wallet = new radiumsahil();
$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet();

$transactions = $wallet->transaction_history();

$wallet->closeConnection();
// include 'theam/' . THEAM . '/transactions.php';
?>
<?php
$page_title = "Recharge History - " . $site_data['web_name'];
?>
<?php include('partial/header.php'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.min.css">
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<?php include('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php'); ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php'); ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <!-- <?php include('partial/breadcrumb.php'); ?> -->
            <!-- Container-fluid starts-->
            <br><br>
            <div class="container-fluid mt-6">
                <?php
                if (!$transactions) {
                ?>
                    <div class="fixed  inset-0 grid  place-content-center" ">
                                    <center>             <lottie-player
                                                                    src="
                        https://lottie.host/e4335069-53e2-40d0-96cc-bbf637a9230b/0x53YL3QsZ.json" background="transparent"
                        speed="1" style="width: 250px; margin-top:35px" direction="1" mode="normal" loop autoplay>
                        </lottie-player>
                        </center>
                        <p class="text-center" style="font-weight:bold; font-size:20px">Transactions History Empty</p>
                    </div>
                <?php
                } else {
                ?>
                    <div class="card recent-order">
                        <div class="card-header card-no-border">
                            <div class="header-top">
                                <h5 class="m-0">Recharge History</h5>
                                <div class="card-header-right-icon">

                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">

                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="v-pills-shirt" role="tabpanel"
                                    aria-labelledby="v-pills-shirt-tab">
                                    <div class="table-responsive">
                                        <table class="table  nowrap" id="myTable">
                                            <thead>
                                                <tr>
                                                    <!-- <th class="f-light">Logo</th> -->
                                                    <th class="f-light">#</th>
                                                    <th class="f-light">Type</th>
                                                    <th class="f-light">Amount</th>
                                                    <th class="f-light">Date</th>
                                                    <th class="f-light">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($transactions as $transaction) {
                                                    $count++;
                                                    if ($transaction['status'] == 1) {
                                                        $status = '<span class="badge rounded-pill badge-success">Success</span>';
                                                    } else {
                                                        $status = '<span class="badge rounded-pill badge-danger">Failed</span>';
                                                    }
                                                ?>
                                                    <tr class="hr hr-blurry">

                                                        <td class="col"><?php echo $count; ?></td>
                                                        <td><?php echo $transaction['type']; ?></td>
                                                        <td>₦<?php echo $transaction['amount']; ?></td>
                                                        <td><?php echo $transaction['date']; ?></td>
                                                        <td><?php echo $status; ?></td>

                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>





                        <?php
                    }
                        ?>

                        </div>

                        <!-- Container-fluid Ends-->
                    </div>
            </div>
        </div>
        <!-- <?php include('partial/footer.php'); ?> -->
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="assets/js/notiflix-aio-3.2.7.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>
<!-- <script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script> -->
<script>
    $(document).ready(function() {
        $('#myTable').DataTable({
            ordering: false,
            autoWidth: true
        });
    });
</script>



<?php include('partial/footer-end.php'); ?>
