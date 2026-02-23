<?php
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
$referwallet = $wallet->refer_data();

$wallet->closeConnection();
// include 'theam/' . THEAM . '/recharge.php';
?>
<?php
$page_title = "Recharge - " . $site_data['web_name'];
?>


<?php include('partial/header.php'); ?>
<?php
if (isset($_POST['pay'])) {
    $amount = $_POST['amount'];
    if ($amount >= 100) {
        $request = [
            'tx_ref' => 'FCTG' . strtoupper(uniqid()),
            'amount' => $amount,
            'currency' => 'NGN',
            'payment_options' => 'banktransfer',
            'bank_transfer_options' => [
                'expires' => 3600
            ],
            'redirect_url' => WEBSITE_URL . '/success',
            'customer' => [
                'email' => $userdata['email'],
                'name' => $userdata['name']
            ],
            'meta' => [
                'price' => $amount
            ],
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.flutterwave.com/v3/payments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($request),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . SECRET_KEY,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $res = json_decode($response);
        if (isset($res->status) && $res->status == 'success') {
            $url = $res->data->link;
            echo '<script>
                window.location.href = "' . $url . '";
            </script>';
        }
    }
}
?>

<style>
    #checkIcon img {
        margin-top: -40px;
        background: transparent;
        border-radius: 50%;
        display: inline-block;
        width: 75px;
        height: 75px;
        font-size: 64px;
        line-height: 72px;

    }
</style>
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
                <div class="row">
                    <div class="">
                        <input type="hidden" name="tokens" id="tokens" value="<?php echo $_SESSION['auth_token']; ?>">

                        <div class="card" id="radiumsahil">
                            <div class="card-body">
                                <div id="card_page">


                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- modal start -->
                <div class="modal fade ml-3 mr-3" tabindex="-1" role="dialog" id="successModal">
                    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-body text-center">
                                <div id="checkIcon">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7518/7518748.png">
                                </div>
                                <div class="mt-4 py-2">
                                    <p class="px-4 pb-0 mb-2 text-success" style="font-weight:bold">Congratulations</p>
                                    <h4 class="h5" id="model_message">₦10 Added Successfully</h4>
                                </div>
                                <div class="py-1"><button type="button" class="btn  btn-outline-success rounded-pill px-5"
                                        data-bs-dismiss="modal">OK</button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- modal end -->
            </div>
            <!-- Container-fluid Ends-->
        </div>

        <?php include('partial/footer.php'); ?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="assets/js/notiflix-aio-3.2.7.min.js"></script>
<script src="js/confetti.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tsparticles/confetti@3.0.3/tsparticles.confetti.bundle.min.js"></script>

<script>
    back();
    const startit = () => {
        setTimeout(function() {
            confetti.start();
        }, 100);
    };
    const stopit = () => {
        setTimeout(function() {
            confetti.stop();
        }, 3000);
    };
    // startit();


    function copy(text) {
        const el = document.createElement('textarea');
        el.value = text;

        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';

        document.body.appendChild(el);

        el.select();

        document.execCommand('copy');

        document.body.removeChild(el);
        Notiflix.Notify.success("Address Copied");
    }

    function countdownTimer(milliseconds, elementId) {
        const totalMinutes = Math.floor(milliseconds / 60000);
        const element = document.getElementById(elementId);

        if (element) {
            $(element).empty().append('<i class="fa fa-clock-o" style="margin-bottom:1px"></i> 00:00');
            const countdown = setInterval(() => {
                milliseconds -= 1000;

                if (milliseconds >= 0) {
                    const remainingMinutes = String(Math.floor(milliseconds / 60000)).padStart(2, '0');
                    const remainingSeconds = String(Math.floor((milliseconds % 60000) / 1000)).padStart(2, '0');
                    $(element).empty().append('<i class="fa fa-clock-o" style="margin-bottom:1px"></i> ' + remainingMinutes + ":" + remainingSeconds + '');
                } else {
                    clearInterval(countdown);
                    element.textContent = "Time Over";
                }
            }, 1000);
        }
    }

    function back() {
        document.getElementById("card_page").innerHTML = `
    <div class="text-center">
         <p style="font-size:18px; font-weight:bolder; margin-bottom:5px">Fund Wallet</p>
              </div><br>
         <!-- <div onclick="upi()" class="clearfix  shadow-showcase point"
             style="border: 1px solid gray; padding: 5px; margin-bottom: 12px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; cursor:pointer;">
            <div style="display: flex; align-items: center;">
                <img class="rounded-circle user-image"
                    src="https://i.ibb.co/vkTtzFv/1710064521941.png"
                    width="50" alt="">
                <div class="about" style="margin-left: 10px;">
                    <div class="name" style="font-weight:bold; font-size:20px">UPI</div>
                    <div class="status" style="font=size:10px">
                        Phonepe,Paytm,Gpay
                    </div>
                </div>
            </div>
            <div style="margin-left: auto; width: 25px"><i class="fa fa-arrow-right fa-lg"></i></div>
        </div>
        <div onclick="trx()" class="clearfix  shadow-showcase point"
             style="border: 1px solid gray; padding: 5px; margin-bottom: 12px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; cursor:pointer;">
            <div style="display: flex; align-items: center;">
                <img class="rounded-circle user-image"
                    src="https://seeklogo.com/images/T/tron-trx-logo-5AAF496D14-seeklogo.com.png"
                    width="50" alt="">
                <div class="about" style="margin-left: 10px;">
                    <div class="name" style="font-weight:bold; font-size:20px">TRON</div>
                    <div class="status" style="font=size:10px">
                    TRC20
                    </div>
                </div>
            </div>
            <div style="margin-left: auto; width: 25px"><i class="fa fa-arrow-right fa-lg"></i></div>
        </div>
        <div onclick="usdt()" class="clearfix  shadow-showcase point"
             style="border: 1px solid gray; padding: 5px; margin-bottom: 12px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; cursor:pointer;">
            <div style="display: flex; align-items: center;">
                <img class="rounded-circle user-image"
                    src="https://w7.pngwing.com/pngs/840/253/png-transparent-usdt-cryptocurrencies-icon-thumbnail.png"
                    width="50" alt="">
                <div class="about" style="margin-left: 10px;">
                    <div class="name" style="font-weight:bold; font-size:20px">USDT</div>
                    <div class="status" style="font=size:10px">
                    SOL
                    </div>
                </div>
            </div>
            <div style="margin-left: auto; width: 25px"><i class="fa fa-arrow-right fa-lg"></i></div>-->
        </div>
        <div onclick="flutterwave()" class="clearfix  shadow-showcase point"
             style="border: 1px solid gray; padding: 5px; margin-bottom: 12px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; cursor:pointer;">
            <div style="display: flex; align-items: center;">
                <img class="rounded-circle user-image"
                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSL1eJCezNB-KM9Exk7Pwri7EBDcNP0vbDhsw&s"
                    width="50" alt="">
                <div class="about" style="margin-left: 10px;">
                    <div class="name" style="font-weight:bold; font-size:20px">FLUTTERWAVE</div>
                    <div class="status" style="font=size:10px">
                    RAVE
                    </div>
                </div>
            </div>
            <div style="margin-left: auto; width: 25px"><i class="fa fa-arrow-right fa-lg"></i></div>
        </div>    `;
    }

    function upi() {
        document.getElementById("card_page").innerHTML = `
        <div class="card-text space-y-2">
    <div class="d-flex align-items-center">
        <img class="rounded-circle user-image" src="https://i.ibb.co/vkTtzFv/1710064521941.png" width="50" alt="">
        <div class="about ms-3">
            <div class="name fw-bold fs-5">UPI</div>
            <div class="status fs-7">
                Phonepe, Paytm, Gpay
            </div>
        </div>
    </div>
    <div class="text-center">
        <img src="<?php echo $site_data['upi_qr']; ?>" height="200" width="200" style="margin-top: 16px;"><br>
        <span class="badge rounded-pill badge-secondary" style="margin-top:15px; margin-bottom:10px">Minimum Recharge Amount is ₦<?php echo $site_data['upi_min_recharge']; ?></span>
    </div>
    <div class="position-relative mt-2">
        <div class="d-flex" style="border: 1px solid black; border-radius: 5px;">
            <input id="iconRight" style="background:white; border:0px" type="text" value="<?php echo $site_data['upi_id']; ?>" class="form-control "  onfocus="this.style.boxShadow = 'none';" readonly/>
            <div style="background:white; cursor:pointer; text-align:center" onclick="copy('<?php echo $site_data['upi_id']; ?>')" class="d-flex justify-content-center  align-items-center rounded-end px-1 ">
                <i  class="fa fa-copy fs-3"></i>
            </div>
        </div>
        <div class="input-area" >
            <div class="text-center" style="margin-top:20px">
                <label for="name" class="form-label">ENTER UTR NUMBER HERE</label><br>
                <input id="ref_id" type="number" style="height:50px" class="form-control text-center" placeholder="UTR NUMBER"  onfocus="this.style.boxShadow = 'none';" >
            </div>
        </div>
        <div class="">
    <div class="text-center mt-3 d-flex justify-content-between">
        <button id="recharges" onclick="add_upi();" class="btn btn-dark ">
            Add Money
        </button>
        <button id="back" onclick="back();" class="btn btn-danger">
            Back
        </button>
    </div>
</div>

    </div>
</div>
    `;
    }

    function flutterwave() {
        document.getElementById("card_page").innerHTML = `

        <div class="card-text space-y-2">
                                <div class="d-flex align-items-center">
                                            <img class="rounded-circle user-image"
                                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSL1eJCezNB-KM9Exk7Pwri7EBDcNP0vbDhsw&s" width="50"
                                                alt="">
                                            <div class="about ms-3">
                                                <div class="name fw-bold fs-5">FLUTTERWAVE</div>
                                                <div class="status fs-7">
                                                    RAVE
                                                </div>
                                            </div>
                                        </div>
                                        <center>
                                        <span class="badge rounded-pill badge-secondary" style="margin-top:15px; margin-bottom:10px">Minimum Recharge is ₦100</span>
                                    <center>
                                        <form method="post"/>
                                        <div class="input-area">
                                            <div class="text-center" style="margin-top:20px">
                                                <label for="name" class="form-label">ENTER AMOUNT</label><br>
                                                <input name="amount" type="text" style="height:50px"
                                                    class="form-control text-center" placeholder="Amount"
                                                    onfocus="this.style.boxShadow = 'none';">
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="text-center mt-3 d-flex justify-content-between">
                                                <button name="pay" class="btn btn-dark">
                                                    Pay
                                                </button>
                                                </form>
                                                <button id="back" onclick="back();" class="btn btn-danger">
                                                    Back
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

`;
    }

    function trx() {

        Notiflix.Block.standard('#card_page');
        $.ajax({
            type: "GET",
            url: "api/recharge/crypto/getPrice",
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure("Connection Failed", {
                    showOnlyTheLastOne: true,
                });
                Notiflix.Block.remove('#card_page');
            },
            success: function(data) {
                var json = JSON.parse(data);
                var token = $("#tokens").val();
                var params = {
                    token: token,
                };
                $.ajax({
                    type: "GET",
                    data: params,
                    url: "api/recharge/crypto/getAddress/trx",
                    error: function(e) {
                        console.log(e);
                        Notiflix.Notify.failure("Connection Failed", {
                            showOnlyTheLastOne: true,
                        });
                        Notiflix.Block.remove('#card_page');
                    },
                    success: function(data2) {
                        Notiflix.Block.remove('#card_page');
                        var json2 = JSON.parse(data2);
                        if (json2.ok !== true) {
                            document.getElementById("card_page").innerHTML = `
                            <div class="card-text space-y-2">
                                    <div class="d-flex align-items-center">
                                        <img class="rounded-circle user-image"
                                            src="https://seeklogo.com/images/T/tron-trx-logo-5AAF496D14-seeklogo.com.png"
                                            width="50" alt="">
                                        <div class="about ms-3">
                                            <div class="name fw-bold fs-5">TRX</div>
                                            <div class="status fs-7">
                                                TRC20
                                            </div>

                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <img src="upload/trx.png" height="200"
                                            width="200" style="margin-top: 16px;"><br>
                               <span class="badge rounded-pill badge-secondary" style="margin-top:15px; margin-bottom:10px">Minimum Recharge is <?php echo $site_data['trx_minimum']; ?> TRX (TRC20)</span>
                                    </div>

                                    <div class="position-relative mt-2">
                                        <center><span class="badge rounded-pill badge-primary "
                                                style="font-size:13px; margin-bottom:5px">1 TRX = ₦${json.trx}</span></center>
                                        <div class="d-flex"
                                            style="border: 1px solid black; border-radius: 5px; padding:4px">
                                            <p style="text-align:center; font-weight:bold"> Click On Generate Address to
                                                Get Payment Address</p>
                                        </div>
                                    </div>

                                    <div class="">
                                        <div class="text-center mt-3 d-flex justify-content-between">
                                            <button id="trx_btn" onclick="trx_address();" class="btn btn-dark ">
                                                Generate Address
                                            </button>
                                            <button id="back" onclick="back();" class="btn btn-danger">
                                                Back
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
   `;
                        } else {
                            document.getElementById("card_page").innerHTML = `
                            <div class="card-text space-y-2">
                                    <div class="d-flex align-items-center">
                                        <img class="rounded-circle user-image"
                                            src="https://seeklogo.com/images/T/tron-trx-logo-5AAF496D14-seeklogo.com.png"
                                            width="50" alt="">
                                        <div class="about ms-3">
                                            <div class="name fw-bold fs-5">TRX</div>
                                            <div class="status fs-7">
                                                TRC20
                                            </div>

                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <img src="https://quickchart.io/qr?text=${json2.data.address}&size=200&centerImageUrl=https%3A%2F%2Fseeklogo.com%2Fimages%2FT%2Ftron-trx-logo-5AAF496D14-seeklogo.com.png" height="200"
                                            width="200" style="margin-top: 16px;"><br>
                                            <span class="badge rounded-pill badge-secondary" style="margin-top:15px; margin-bottom:10px">Minimum Recharge is <?php echo $site_data['trx_minimum']; ?> TRX (TRC20)</span>
                                    </div>

                                    <div class="position-relative">
                                    <div class="mb-3 mt-2 text-center">
                  <span id="trx_time" style="padding: 5px 10px; border-radius: 20px; background: #e1e3e5; font-size:16px; "><i class="fa fa-clock-o" style="margin-bottom:1px"></i> 20:00</span>
                    </div>
                                        <center><span class="badge rounded-pill badge-primary "
                                                style="font-size:13px; margin-bottom:5px">1 TRX = ₦${json.trx}</span></center>
                                        <div class="position-relative mt-2">
                                            <div class="d-flex" style="border: 1px solid black; border-radius: 5px;">
                                                <input id="iconRight" style="background:white; border:0px" type="text"
                                                    value="${json2.data.address}" class="form-control "
                                                    onfocus="this.style.boxShadow = 'none';" readonly />
                                                <div style="background:white; cursor:pointer; text-align:center"
                                                    onclick="copy('${json2.data.address}')"
                                                    class="d-flex justify-content-center  align-items-center rounded-end px-1 ">
                                                    <i class="fa fa-copy fs-3"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="">
                                            <div class="text-center mt-3 d-flex justify-content-between">
                                                <button id="cancle_btn_trx" onclick="trx_cancle_address();" class="btn btn-danger ">
                                                    Delete Address
                                                </button>
                                                <button id="back" onclick="back();" class="btn btn-dark">
                                                    Back
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
   `;
                            countdownTimer(json2.data.left_time, 'trx_time');
                        }
                    }
                });
            }
        });

    }


    function usdt() {
        Notiflix.Block.standard('#card_page');
        $.ajax({
            type: "GET",
            url: "api/recharge/crypto/getPrice",
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure("Connection Failed", {
                    showOnlyTheLastOne: true,
                });
                Notiflix.Block.remove('#card_page');
            },
            success: function(data) {
                var json = JSON.parse(data);
                var token = $("#tokens").val();
                var params = {
                    token: token,
                };
                $.ajax({
                    type: "GET",
                    data: params,
                    url: "api/recharge/crypto/getAddress/usdt",
                    error: function(e) {
                        console.log(e);
                        Notiflix.Notify.failure("Connection Failed", {
                            showOnlyTheLastOne: true,
                        });
                        Notiflix.Block.remove('#card_page');
                    },
                    success: function(data2) {
                        Notiflix.Block.remove('#card_page');
                        var json2 = JSON.parse(data2);
                        if (json2.ok !== true) {
                            document.getElementById("card_page").innerHTML = `
      <div class="card-text space-y-2">
                                    <div class="d-flex align-items-center">
                                        <img class="rounded-circle user-image"
                                            src="https://w7.pngwing.com/pngs/840/253/png-transparent-usdt-cryptocurrencies-icon-thumbnail.png"
                                            width="50" alt="">
                                        <div class="about ms-3">
                                            <div class="name fw-bold fs-5">USDT</div>
                                            <div class="status fs-7">
                                                SOL
                                            </div>

                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <img src="upload/usdt.png"
                                            width="200" style="margin-top: 16px;"> <br>
                                            <span class="badge rounded-pill badge-secondary" style="margin-top:15px; margin-bottom:10px">Minimum Recharge is <?php echo $site_data['usdt_minimum']; ?> USDT (SOL)</span>
                                    </div>

                                    <div class="position-relative mt-2">
                                        <center><span class="badge rounded-pill badge-primary "
                                                style="font-size:13px; margin-bottom:5px">1 USDT = ₦${json.usdt}</span></center>
                                        <div class="d-flex"
                                            style="border: 1px solid black; border-radius: 5px; padding:4px">
                                            <p style="text-align:center; font-weight:bold"> Click On Generate Address to
                                                Get Payment Address</p>
                                        </div>
                                    </div>

                                    <div class="">
                                        <div class="text-center mt-3 d-flex justify-content-between">
                                            <button id="usdt_btn" onclick="usdt_address();" class="btn btn-dark ">
                                                Generate Address
                                            </button>
                                            <button id="back" onclick="back();" class="btn btn-danger">
                                                Back
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
  `;
                        } else {
                            document.getElementById("card_page").innerHTML = `
<div class="card-text space-y-2">
                                    <div class="d-flex align-items-center">
                                        <img class="rounded-circle user-image"
                                            src="https://w7.pngwing.com/pngs/840/253/png-transparent-usdt-cryptocurrencies-icon-thumbnail.png"
                                            width="50" alt="">
                                        <div class="about ms-3">
                                            <div class="name fw-bold fs-5">USDT</div>
                                            <div class="status fs-7">
                                                SOL
                                            </div>

                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <img src="https://quickchart.io/qr?text=${json2.data.address}&size=200&centerImageUrl=https://w7.pngwing.com/pngs/840/253/png-transparent-usdt-cryptocurrencies-icon-thumbnail.png" height="200"
                                            width="200" style="margin-top: 16px;"><br>
                                            <span class="badge rounded-pill badge-secondary" style="margin-top:15px; margin-bottom:10px">Minimum Recharge is <?php echo $site_data['usdt_minimum']; ?> USDT (SOL)</span>
                                    </div>

                                    <div class="position-relative">
                                    <div class="mb-3 mt-2 text-center">
                  <span id="usdt_time" style="padding: 5px 10px; border-radius: 20px; background: #e1e3e5; font-size:16px; "><i class="fa fa-clock-o" style="margin-bottom:1px"></i> 20:00</span>
                    </div>
                                        <center><span class="badge rounded-pill badge-primary "
                                                style="font-size:13px; margin-bottom:5px">1 USDT = ₦${json.usdt}</span></center>
                                        <div class="position-relative mt-2">
                                            <div class="d-flex" style="border: 1px solid black; border-radius: 5px;">
                                                <input id="iconRight" style="background:white; border:0px" type="text"
                                                    value="${json2.data.address}" class="form-control "
                                                    onfocus="this.style.boxShadow = 'none';" readonly />
                                                <div style="background:white; cursor:pointer; text-align:center"
                                                    onclick="copy('${json2.data.address}')"
                                                    class="d-flex justify-content-center  align-items-center rounded-end px-1 ">
                                                    <i class="fa fa-copy fs-3"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="">
                                            <div class="text-center mt-3 d-flex justify-content-between">
                                                <button id="cancle_btn_usdt" onclick="usdt_cancle_address();" class="btn btn-danger ">
                                                    Delete Address
                                                </button>
                                                <button id="back" onclick="back();" class="btn btn-dark">
                                                    Back
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
  `;
                            countdownTimer(json2.data.left_time, 'usdt_time');
                        }
                    }
                });
            }
        });
    }

    function promo() {
        document.getElementById("card_page").innerHTML = `

        <div class="card-text space-y-2">
                                <div class="d-flex align-items-center">
                                            <img class="rounded-circle user-image"
                                                src="https://cdn-icons-png.flaticon.com/512/6664/6664427.png" width="50"
                                                alt="">
                                            <div class="about ms-3">
                                                <div class="name fw-bold fs-5">PROMOCODE</div>
                                                <div class="status fs-7">
                                                    Giveaway
                                                </div>
                                            </div>
                                        </div>
                                        <center><div class="justify-content-center">
<lottie-player src="build/lotties/gift.json" background="transparent" speed="1" style="width: 250px; height: 250px" direction="1" mode="normal" loop autoplay></lottie-player>
                                        </div><center>

                                        <div class="input-area">
                                            <div class="text-center" style="margin-top:20px">
                                                <label for="name" class="form-label">ENTER PROMOCODE HERE</label><br>
                                                <input id="redeem_id" type="text" style="height:50px"
                                                    class="form-control text-center" placeholder="XXXX-XXXX-XXXX"
                                                    onfocus="this.style.boxShadow = 'none';">
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="text-center mt-3 d-flex justify-content-between">
                                                <button id="recharges1" onclick="add_promo();" class="btn btn-dark">
                                                    Redeem
                                                </button>
                                                <button id="back" onclick="back();" class="btn btn-danger">
                                                    Back
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

`;
    }

    function done_promo(msg) {
        document.getElementById("card_page").innerHTML = `

        <div class="card-text space-y-1 mt-0">
                                        <div class="d-flex align-items-center">
                                            <img class="rounded-circle user-image"
                                                src="https://cdn-icons-png.flaticon.com/512/6664/6664427.png" width="50"
                                                alt="">
                                            <div class="about ms-3">
                                                <div class="name fw-bold fs-5">PROMOCODE</div>
                                                <div class="status fs-7">
                                                    Giveaway
                                                </div>
                                            </div>
                                        </div>
                                        <center>
                                            <div class="justify-content-center" style="">
                                                <lottie-player
                                                    src="https://lottie.host/cc92a297-5dc6-4e22-8b9f-e671eaafc4f6/lXq5HgDG4U.json"
                                                    background="transparent" speed="1" style="width: 250px; margin:0"
                                                    direction="1" mode="normal" autoplay></lottie-player>
                                            </div>
                                            <p style="color:#90EE90; font-size:20px; font-weight:bold; padding:0"
                                                id="tq-msg">${msg}</p>
                                            <center>
                                                <div class="">
                                                    <div class="text-center mt-3 d-flex justify-content-center">
                                                        <button id="back" onclick="back();" class="btn btn-dark w-50">
                                                            Back
                                                        </button>
                                                    </div>
                                                </div>

                                    </div>
                                </div>


`;
    }

    function add_upi() {
        var txn_id = $("#ref_id").val();
        var token = $("#tokens").val();
        if (txn_id === '') {
            Notiflix.Notify.failure("PLEASE ENTER UTR", {
                showOnlyTheLastOne: true,
            });
            return;
        }



        $('#recharges').prop("disabled", true);
        $('#recharges').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking....');

        var params = {
            token: token,
            txn_id: txn_id,
        };
        $.ajax({
            type: "GET",
            url: "api/recharge/upi",
            data: params,
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure("Connection Failed", {
                    showOnlyTheLastOne: true,
                });
                $('#recharges').html("Add Money");
                $('#recharges').prop("disabled", false);
            },
            success: function(data) {
                $('#recharges').html("Add Money");
                $('#recharges').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.status === "200") {
                    var params = {
                        token: token
                    };
                    startit();
                    stopit();
                    document.getElementById("model_message").textContent = json.message;
                    $('#successModal').modal('show');
                    $.ajax({
                        type: "POST",
                        url: "api/auth/session",
                        data: params,
                        error: function(e) {
                            console.log(e);
                        },
                        success: function(data) {
                            var jsons = JSON.parse(data);
                            var spanElement = document.getElementById("current_balance");

                            spanElement.textContent = "₦" + jsons.balance;

                        }
                    });
                    Notiflix.Notify.success(json.message, {
                        showOnlyTheLastOne: true,
                    });
                } else {
                    Notiflix.Notify.failure(json.message, {
                        showOnlyTheLastOne: true,
                    });

                }
            }
        });
    }

    function trx_address() {
        var token = $("#tokens").val();


        $('#trx_btn').prop("disabled", true);
        $('#trx_btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generate Address');

        var params = {
            token: token,
        };
        $.ajax({
            type: "GET",
            url: "api/recharge/crypto/generateAddress/trx",
            data: params,
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure("Connection Failed", {
                    showOnlyTheLastOne: true,
                });
                $('#trx_btn').html("Generate Address");
                $('#trx_btn').prop("disabled", false);
            },
            success: function(data) {
                $('#trx_btn').html("Generate Address");
                $('#trx_btn').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.ok === true) {
                    trx();
                } else {
                    Notiflix.Notify.failure(json.message, {
                        showOnlyTheLastOne: true,
                    });
                }
            }
        });
    }

    function trx_cancle_address() {
        var token = $("#tokens").val();


        $('#cancle_btn_trx').prop("disabled", true);
        $('#cancle_btn_trx').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Delete Address');

        var params = {
            token: token,
        };
        $.ajax({
            type: "GET",
            url: "api/recharge/crypto/cancelAddress/trx",
            data: params,
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure("Connection Failed", {
                    showOnlyTheLastOne: true,
                });
                $('#cancle_btn_trx').html("Delete Address");
                $('#cancle_btn_trx').prop("disabled", false);
            },
            success: function(data) {
                $('#cancle_btn_trx').html("Delete Address");
                $('#cancle_btn_trx').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.ok === true) {
                    trx();
                } else {
                    Notiflix.Notify.failure(json.message, {
                        showOnlyTheLastOne: true,
                    });
                }
            }
        });
    }

    function usdt_address() {
        var token = $("#tokens").val();


        $('#usdt_btn').prop("disabled", true);
        $('#usdt_btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generate Address');

        var params = {
            token: token,
        };
        $.ajax({
            type: "GET",
            url: "api/recharge/crypto/generateAddress/usdt",
            data: params,
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure("Connection Failed", {
                    showOnlyTheLastOne: true,
                });
                $('#usdt_btn').html("Generate Address");
                $('#usdt_btn').prop("disabled", false);
            },
            success: function(data) {
                $('#usdt_btn').html("Generate Address");
                $('#usdt_btn').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.ok === true) {
                    usdt();
                } else {
                    Notiflix.Notify.failure(json.message, {
                        showOnlyTheLastOne: true,
                    });
                }
            }
        });
    }

    function usdt_cancle_address() {
        var token = $("#tokens").val();


        $('#cancle_btn_usdt').prop("disabled", true);
        $('#cancle_btn_usdt').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Delete Address');

        var params = {
            token: token,
        };
        $.ajax({
            type: "GET",
            url: "api/recharge/crypto/cancelAddress/usdt",
            data: params,
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure("Connection Failed", {
                    showOnlyTheLastOne: true,
                });
                $('#cancle_btn_usdt').html("Delete Address");
                $('#cancle_btn_usdt').prop("disabled", false);
            },
            success: function(data) {
                $('#cancle_btn_usdt').html("Delete Address");
                $('#cancle_btn_usdt').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.ok === true) {
                    usdt();
                } else {
                    Notiflix.Notify.failure(json.message, {
                        showOnlyTheLastOne: true,
                    });
                }
            }
        });
    }

    function add_promo() {
        var txn_id = $("#redeem_id").val();
        var token = $("#tokens").val();
        if (txn_id === '') {
            Notiflix.Notify.failure('Please Enter Promocode.', {
                showOnlyTheLastOne: true,
            });
            return;
        }



        $('#recharges1').prop("disabled", true);
        $('#recharges1').html('<span class="animate-spin border-2 border-white border-l-transparent rounded-full w-4 h-4 ltr:mr-1 rtl:ml-1 inline-block align-middle"></span>Checking....');

        var params = {
            token: token,
            code: txn_id,
        };
        $.ajax({
            type: "POST",
            url: "api/recharge/promocode",
            data: params,
            error: function(e) {
                console.log(e);
                Notiflix.Notify.failure('An error occurred during Connection.');
                $('#recharges1').html("Redeem");
                $('#recharges1').prop("disabled", false);
            },
            success: function(data) {
                $('#recharges1').html("Redeem");
                $('#recharges1').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.status === "200") {
                    // document.getElementById("model_message").textContent = json.message;
                    // $('#successModal').modal('show');
                    done_promo(json.message);
                    const end = Date.now() + 2 * 1000;
                    const colors = ["#bb0000", "#ffffff"];

                    (function frame() {
                        confetti({
                            particleCount: 2,
                            angle: 60,
                            spread: 55,
                            origin: {
                                x: 0
                            },
                            colors: colors,
                        });

                        confetti({
                            particleCount: 2,
                            angle: 120,
                            spread: 55,
                            origin: {
                                x: 1
                            },
                            colors: colors,
                        });

                        if (Date.now() < end) {
                            requestAnimationFrame(frame);
                        }
                    })();
                    var params = {
                        token: token
                    };

                    $.ajax({
                        type: "POST",
                        url: "api/auth/session",
                        data: params,
                        error: function(e) {
                            console.log(e);
                        },
                        success: function(data) {
                            var jsons = JSON.parse(data);
                            var spanElement = document.getElementById("current_balance");

                            spanElement.textContent = "₦" + jsons.balance;

                        }
                    });
                    Notiflix.Notify.success(json.message);
                } else {
                    Notiflix.Notify.failure(json.message);
                }
            }
        });
    }
</script>

<?php include('partial/footer-end.php'); ?>
