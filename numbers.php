<?php
session_start();
include 'include/config.php';
require __DIR__ . '/class/class.control.php';
if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $radium_token = $_COOKIE['remember_me'];
        $_SESSION['token'] = $radium_token;
    } else {
        header('location: login');
        exit;
    }
}
$wallet = new radiumsahil();
$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet();
if ($userdata === false) {
    unset($_SESSION['token']);
    session_destroy();
    if (isset($_COOKIE['remember_me'])) {
        unset($_COOKIE['remember_me']);
        setcookie('remember_me', $token, [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,
            'httponly' => true,
            'samesite' => 'radium'
        ]);


    }
    header('location: login');
    exit;
}
$transactions = $wallet->number_history();
$wallet->closeConnection();
// include 'theam/' . THEAM . '/numbers.php';
?>
<?php
$page_title = "Numbers History - " . $site_data['web_name'];
?>
<?php include ('partial/header.php'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.min.css">
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<?php include ('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include ('partial/topbar.php'); ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include ('partial/sidebar.php'); ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <!-- <?php include ('partial/breadcrumb.php'); ?> -->
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
                        <p class="text-center" style="font-weight:bold; font-size:20px">Number History Empty</p>
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
                                        <table class="table nowrap p-2" id="myTable">
                                            <thead>
                                                <tr>
                                                    <!-- <th class="f-light">Logo</th> -->
                                                    <th class="f-light">Service</th>
                                                    <th class="f-light">Number</th>
                                                    <th class="f-light">Amount</th>
                                                    <th class="f-light">Sms</th>
                                                    <th class="f-light">Date</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($transactions as $transaction) {

                                                    ?>
                                                    <tr class="hr hr-blurry">

                                                        <td class="col"><?php echo $transaction['service_name']; ?></td>
                                                        <td>+<?php echo $transaction['number']; ?></td>
                                                        <td>₦<?php echo $transaction['service_price']; ?></td>
                                                        <td><?php echo $transaction['sms']; ?></td>
                                                        <td><?php echo $transaction['buy_time']; ?></td>

                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
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


        <!-- <?php include ('partial/footer.php'); ?> -->
    </div>
</div>

<?php include ('partial/scripts.php'); ?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="<?php echo WEBSITE_URL; ?>/theam/radiumsahil/assets/js/notiflix-aio-3.2.7.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#myTable').DataTable({
            responsive: true,
            // scrollX: true,
            ordering: false,
            autoWidth: true
        });
    });
</script>



<?php include ('partial/footer-end.php'); ?>