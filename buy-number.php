<?php
session_start();

include 'include/config.php';
require __DIR__ . '/class/class.control.php';
if(!isset($_SESSION['token'])){
	if(isset($_COOKIE['remember_me'])) {
		$radium_token = $_COOKIE['remember_me'];
		$_SESSION['token'] = $radium_token;
	}else{
		header('location: login');	
		exit;	
	}
}
$wallet = new radiumsahil();
$userdata = $wallet->userdata();

if($userdata===false){
	unset($_SESSION['token']);
	session_destroy();
	if(isset($_COOKIE['remember_me'])) {
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
$userwallet = $wallet->userwallet();
$server = $wallet->all_server();
$wallet->closeConnection();
// include 'theam/' . THEAM . '/buy-number.php';
?>
<?php
$page_title = "Buy Numbers- " . $site_data['web_name'];
?>
<?php include ('partial/header.php'); ?>
<link rel='stylesheet' type='text/css'
    href='assets/css/nice-select2.css?=22'>
    <script src="assets/js/nice-select2.js"></script>
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="card" id="radiumsahil">
                            <div class="card-body">
                                <div class="mb-5">
                                    <div class="col-form-label">Select Server</div>
                                    <input type="hidden" id="server_no" value="">
                                    <input type="hidden" id="service_code" value="">
                                    <input type="hidden" id="token" value="<?php echo $_SESSION['token']; ?>">
                                    <select id="server-id" class="wide">
                                        <option value="" selected disabled>Select Server</option>
                                        <?php
                                        foreach ($server as $servers) {
                                            echo "<option value=" . $servers['id'] . ">" . $servers['server_name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="chat-box">
                                    <!-- Chat left side Start-->
                                    <div class="chat-left-aside" style="">
                                        <div class="people-list" id="people-list">
                                            <div class="search">
                                                <div class="theme-form">
                                                    <div class="mb-3">
                                                        <input class="form-control" type="text" id="search_op"
                                                            placeholder="Search Service" data-bs-original-title=""
                                                            title="" disabled><i class="fa fa-search"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <style>
                                                .chat-box .user-image {
                                                    float: left;
                                                    width: 40px;
                                                    height: 40px;
                                                    margin-right: 5px;
                                                    /* border: 2px solid black; */
                                                }

                                                .point {
                                                    cursor: pointer;
                                                }

                                                .loading-wave {
                                                    width: 300px;
                                                    height: 100px;
                                                    display: flex;
                                                    justify-content: center;
                                                    align-items: flex-end;
                                                }

                                                .loading-bar {
                                                    width: 20px;
                                                    height: 10px;
                                                    margin: 0 5px;
                                                    background-color: #3498db;
                                                    border-radius: 5px;
                                                    animation: loading-wave-animation 1s ease-in-out infinite;
                                                }

                                                .loading-bar:nth-child(2) {
                                                    animation-delay: 0.1s;
                                                }

                                                .loading-bar:nth-child(3) {
                                                    animation-delay: 0.2s;
                                                }

                                                .loading-bar:nth-child(4) {
                                                    animation-delay: 0.3s;
                                                }

                                                @keyframes loading-wave-animation {
                                                    0% {
                                                        height: 10px;
                                                    }

                                                    50% {
                                                        height: 50px;
                                                    }

                                                    100% {
                                                        height: 10px;
                                                    }
                                                }
                                            </style>
                                            <ul id="list" class="list p-2" style="height:2000px">
                                                <div>
                                                    <center>
                                                        <img src="https://cdn-icons-png.flaticon.com/512/10479/10479814.png"
                                                            width="90px">
                                                        <h3>Please Select Server</h3>
                                                    </center>
                                                </div>
                                            </ul>

                                        </div>
                                        <div class="row">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span id="show_name"
                                                        class="text-sm text-slate-500 dark:text-slate-400"
                                                        style="font-weight:bold; font-size:18px">??</span><br>
                                                    <span id="show_amount"
                                                        class="text-lg font-medium text-slate-900 dark:text-white">₦??</span>
                                                </div>
                                                <div class="ml-auto">
                                                    <!-- New div for button, ml-auto will push it to the right -->
                                                    <button id="buy-numbers" type="button" class="btn btn-dark"><span
                                                            class='fa fa-cart-plus'
                                                            style='margin-right: 8px;'></span>Buy Number</button>
                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                    <!-- Chat left side Ends-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="card-container" class="col-md-6">

                    </div>
                </div>


            </div>
            <!-- Container-fluid Ends-->
        </div>

        <?php include ('partial/footer.php'); ?>
    </div>
</div>

<?php include ('partial/scripts.php'); ?>
<script src="assets/js/notiflix-aio-3.2.7.min.js"></script>
<script>
const _0x17fcf4=_0x10b0;(function(_0x21bcbb,_0x39733e){const _0x469aee=_0x10b0,_0x4a9257=_0x21bcbb();while(!![]){try{const _0x3b66f6=-parseInt(_0x469aee(0x153))/0x1*(parseInt(_0x469aee(0x14b))/0x2)+-parseInt(_0x469aee(0x144))/0x3+parseInt(_0x469aee(0x143))/0x4*(parseInt(_0x469aee(0x159))/0x5)+-parseInt(_0x469aee(0x157))/0x6+-parseInt(_0x469aee(0x15c))/0x7*(parseInt(_0x469aee(0x15a))/0x8)+parseInt(_0x469aee(0x154))/0x9+-parseInt(_0x469aee(0x13e))/0xa*(-parseInt(_0x469aee(0x155))/0xb);if(_0x3b66f6===_0x39733e)break;else _0x4a9257['push'](_0x4a9257['shift']());}catch(_0x587e5f){_0x4a9257['push'](_0x4a9257['shift']());}}}(_0x4b6d,0x7fe43));function appendNoResultDiv(){const _0x8f9f3d=_0x10b0,_0x45b0ff=document[_0x8f9f3d(0x14a)]('list'),_0x2edf7b=document[_0x8f9f3d(0x13f)](_0x8f9f3d(0x13b));_0x2edf7b['innerHTML']='\x0a\x20\x20\x20\x20\x20\x20\x20\x20<center>\x0a\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20<img\x20src=\x22https://cdn-icons-png.flaticon.com/512/6357/6357033.png\x22\x20width=\x2290px\x22>\x0a\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20<h3>No\x20Result\x20Found</h3>\x0a\x20\x20\x20\x20\x20\x20\x20\x20</center>\x0a\x20\x20\x20\x20',_0x2edf7b['id']=_0x8f9f3d(0x151),_0x45b0ff[_0x8f9f3d(0x13c)](_0x2edf7b);}function _0x10b0(_0x4c5ff9,_0x27fd10){const _0x4b6d05=_0x4b6d();return _0x10b0=function(_0x10b09f,_0x5d2ac7){_0x10b09f=_0x10b09f-0x13a;let _0x4e6de7=_0x4b6d05[_0x10b09f];return _0x4e6de7;},_0x10b0(_0x4c5ff9,_0x27fd10);}function removeNoResultDiv(){const _0x8618b9=_0x10b0,_0x316315=document['getElementById'](_0x8618b9(0x151));_0x316315&&_0x316315[_0x8618b9(0x156)][_0x8618b9(0x14c)](_0x316315);}const searchInput=document[_0x17fcf4(0x14e)](_0x17fcf4(0x13d));searchInput[_0x17fcf4(0x150)](_0x17fcf4(0x145),function(){const _0x434484=_0x17fcf4,_0x3890eb=this[_0x434484(0x14f)][_0x434484(0x148)](),_0x194c2c=document['querySelectorAll'](_0x434484(0x140));let _0x19d563=![];_0x194c2c[_0x434484(0x152)](_0x28ed33=>{const _0x181bc4=_0x434484,_0x4ef08e=_0x28ed33['querySelector'](_0x181bc4(0x149))['textContent']['toLowerCase'](),_0x23af7f=_0x28ed33[_0x181bc4(0x14e)](_0x181bc4(0x15b))[_0x181bc4(0x158)][_0x181bc4(0x148)]();_0x4ef08e[_0x181bc4(0x146)](_0x3890eb)||_0x23af7f[_0x181bc4(0x146)](_0x3890eb)?(_0x28ed33[_0x181bc4(0x147)][_0x181bc4(0x142)]=_0x181bc4(0x14d),_0x19d563=!![]):_0x28ed33[_0x181bc4(0x147)][_0x181bc4(0x142)]=_0x181bc4(0x141);}),_0x19d563?removeNoResultDiv():(removeNoResultDiv(),appendNoResultDiv(),console[_0x434484(0x13a)]('ok'));});function _0x4b6d(){const _0x59f6e4=['2117070hYTtMe','input','includes','style','toLowerCase','.name','getElementById','151244UjjklK','removeChild','flex','querySelector','value','addEventListener','radiumop','forEach','1uLgqaq','1311768OpYbfR','132brsJjw','parentNode','5899482igzRmK','textContent','281740WXAaoh','8bYzegk','.status','4321422XlrBXN','log','div','appendChild','.search\x20input','1548690sfhSlc','createElement','.list\x20.clearfix','none','display','64pZboAY'];_0x4b6d=function(){return _0x59f6e4;};return _0x4b6d();}
</script>
<script src="js/main.js?v=55448535099999710399822428842426399098585"></script>
<script type="module" src="js/sms.js?v=28988882830099470558899296990003900"></script>
<!-- <script src="js/xy.js?v=22929"></script> -->


<?php include ('partial/footer-end.php'); ?>