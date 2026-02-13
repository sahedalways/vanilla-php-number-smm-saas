<?php
session_start();

include  'include/config.php';
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
$data = $wallet->balancedata();
if($data===false){
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

$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet();
$referwallet = $wallet->refer_data();
$recent_history = $wallet->recent_history();
$top_services = $wallet->top_services();
$wallet->closeConnection();
// include 'theam/' . THEAM . '/dashboard.php';
?>
<?php
$page_title = "Dashboard - " . $site_data['web_name'];
?>
<?php include ('partial/header.php'); ?>
<style>
.btn-orange {
  background-color: orange;
  color: black;
  border: 2px solid orange;
}

.btn-orange:hover {
  background-color: orange;
  color: black;
  border-color: orange;
}
</style>
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
        <div class="row widget-grid">
          <div class="col-xxl-4 col-sm-6 box-col-6">
            <div class="card profile-box">
              <div class="card-body">
                <div class="media">
                  <div class="media-body">
                    <div class="greeting-user">
                      <h4 style="color:black;" class="f-w-600">Welcome to
                        <?php echo $site_data['web_name']; ?>
                      </h4>
                      <p style="color:black;">Get Your Virtual Numbers Today! Buy Now!</p>
                      <div class="whatsnew-btn">
  <a href="buy-number" class="btn btn-orange">Buy Numbers</a>
</div></div>

                  </div>
                  <div>
                    <div class="clockbox">
                      <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve">
                        <circle style="fill:#EDEDED;" cx="256" cy="256" r="256" />
                        <g>
                          <path style="fill:#F33052;" d="M354.72,155.12h-4.24c-4.24-17.92-20.32-31.28-39.52-31.28c-4.64,0-9.12,0.8-13.36,2.24
    C288.16,99.28,262.72,80,232.64,80c-38,0-68.88,30.8-68.88,68.88c0,2.4,0.16,4.72,0.4,7.12C139.2,160.32,120,182.16,120,208.4
    c0,29.28,23.92,53.2,53.2,53.2h181.52c29.28,0,53.2-23.92,53.2-53.2C408,179.04,384,155.12,354.72,155.12z" />
                          <rect x="144" y="308.16" style="fill:#F33052;" width="256" height="16" />
                        </g>
                        <rect x="144" y="340.16" width="216" height="16" />
                        <rect x="144" y="372.16" style="fill:#F33052;" width="168" height="16" />
                        <rect x="144" y="404.16" width="112" height="16" />
                        <g>
                          <path style="fill:#FFFFFF;" d="M181.52,219.44c2.64,1.68,7.28,3.52,11.68,3.52c6.48,0,9.44-3.2,9.44-7.28
    c0-4.24-2.48-6.56-9.04-8.96c-8.8-3.12-12.88-8-12.88-13.84c0-7.84,6.32-14.32,16.8-14.32c4.96,0,9.28,1.44,12,3.04l-2.24,6.48
    c-1.92-1.2-5.44-2.8-10-2.8c-5.28,0-8.16,3.04-8.16,6.64c0,4,2.96,5.84,9.28,8.24c8.48,3.2,12.8,7.44,12.8,14.72
    c0,8.56-6.64,14.64-18.24,14.64c-5.36,0-10.32-1.28-13.68-3.36L181.52,219.44z" />
                          <path style="fill:#FFFFFF;"
                            d="M222.72,192.96c0-5.04-0.08-9.2-0.4-13.2h7.76l0.4,7.84h0.32c2.72-4.64,7.28-8.96,15.36-8.96
    c6.64,0,11.68,4,13.84,9.76h0.24c1.52-2.72,3.44-4.88,5.44-6.32c2.88-2.24,6.16-3.44,10.8-3.44c6.48,0,16,4.24,16,21.2v28.72h-8.64
    v-27.6c0-9.36-3.44-15.04-10.56-15.04c-5.04,0-8.96,3.76-10.48,8.08c-0.4,1.2-0.72,2.8-0.72,4.48v30.16h-8.64v-29.28
    c0-7.76-3.44-13.44-10.16-13.44c-5.52,0-9.6,4.4-10.96,8.88c-0.48,1.28-0.72,2.8-0.72,4.32v29.44h-8.64v-35.6H222.72z" />
                          <path style="fill:#FFFFFF;" d="M305.6,219.44c2.64,1.68,7.28,3.52,11.68,3.52c6.48,0,9.44-3.2,9.44-7.28
    c0-4.24-2.48-6.56-9.04-8.96c-8.8-3.12-12.88-8-12.88-13.84c0-7.84,6.32-14.32,16.8-14.32c4.96,0,9.28,1.44,12,3.04l-2.24,6.48
    c-1.92-1.2-5.44-2.8-10-2.8c-5.28,0-8.16,3.04-8.16,6.64c0,4,2.96,5.84,9.28,8.24c8.48,3.2,12.8,7.44,12.8,14.72
    c0,8.56-6.64,14.64-18.24,14.64c-5.36,0-10.32-1.28-13.68-3.36L305.6,219.44z" />
                        </g>
                      </svg>
                    </div>
                    <div class="badge f-10 p-0" id="txt"></div>
                  </div>
                </div>
                <!-- <div class="cartoon"><img class="img-fluid" src="https://www.svgrepo.com/show/249717/sms.svg"  width="247" height="218" alt="vector women with leptop"></div> -->
              </div>
            </div>
          </div>
          <div class="col-xxl-auto col-xl-3 col-sm-6 box-col-6">
            <div class="row">
              <div class="col-xl-12">
                <div class="card widget-1">
                  <div class="card-body">
                    <div class="widget-content">
                      <div class="widget-round secondary">
                        <div class="bg-round">
                          <svg class="svg-fill">
                            <use href="assets/svg/icon-sprite.svg#cart">
                            </use>
                          </svg>
                          <svg class="half-circle svg-fill">
                            <use
                              href="assets/svg/icon-sprite.svg#halfcircle">
                            </use>
                          </svg>
                        </div>
                      </div>
                      <div>
                        <h4>₦
                          <?php echo $userwallet['balance']; ?>
                        </h4><span class="f-light">Balance</span>
                      </div>
                    </div>
                    <!-- <div class="font-secondary f-w-500"><i class="icon-arrow-up icon-rotate me-1"></i><span>+50%</span></div> -->
                  </div>
                </div>
                <div class="col-xl-12">
                  <div class="card widget-1">
                    <div class="card-body">
                      <div class="widget-content">
                        <div class="widget-round primary">
                          <div class="bg-round">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#tag">
                              </use>
                            </svg>
                            <svg class="half-circle svg-fill">
                              <use
                                href="assets/svg/icon-sprite.svg#halfcircle">
                              </use>
                            </svg>
                          </div>
                        </div>
                        <div>
                          <h4>₦
                            <?php echo $userwallet['total_recharge']; ?>
                          </h4><span class="f-light">Total Recharge</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-auto col-xl-3 col-sm-6 box-col-6">
            <div class="row">
              <div class="col-xl-12">
                <div class="card widget-1">
                  <div class="card-body">
                    <div class="widget-content">
                      <div class="widget-round warning">
                        <div class="bg-round">
                          <svg class="svg-fill">
                            <use
                              href="assets/svg/icon-sprite.svg#return-box">
                            </use>
                          </svg>
                          <svg class="half-circle svg-fill">
                            <use
                              href="assets/svg/icon-sprite.svg#halfcircle">
                            </use>
                          </svg>
                        </div>
                      </div>
                      <div>
                        <h4>
                          <?php echo $userwallet['total_otp']; ?>
                        </h4><span class="f-light">Total Numbers Buy</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-xl-12">
                  <div class="card widget-1">
                    <div class="card-body">
                      <div class="widget-content">
                        <div class="widget-round success">
                          <div class="bg-round">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#rate">
                              </use>
                            </svg>
                            <svg class="half-circle svg-fill">
                              <use
                                href="assets/svg/icon-sprite.svg#halfcircle">
                              </use>
                            </svg>
                          </div>
                        </div>
                        <div>
                          <h4>₦
                            <?php echo $referwallet['balance']; ?>
                          </h4><span class="f-light">Refer Balance</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php
        if ($top_services) {
          ?>
          <div class="card">
            <div class="card-header card-no-border">
              <div class="header-top">
                <h5 class="m-0">Top Services</h5>
                <!-- <div class="card-header-right-icon"><a class="link-only" href="#">View <i data-feather="arrow-right"></i></a></div> -->
              </div>
            </div>
            <div class="card-body pt-0">
              <ul class="lessons-lists">
                <?php
                foreach ($top_services as $top_service) {

                  ?>
                  <li class="service-item">
                    <img class="rounded" src="<?php echo $top_service['service_logo']; ?>" width="40" alt="icon">
                    <div>
                      <h6 class="f-14 f-w-400 mb-0"><?php echo $top_service['service_name']; ?> (₦<?php echo $top_service['service_price']; ?>)</h6>
                      <span class="f-light">In <?php echo $top_service['server_name']; ?></span>
                    </div>
                    <div class="lesson-wrap ms-auto">
                      <div id="lessonChart1"></div>
                    </div>
                    <div class="buy-now-btn">
                      <a href="buy-number" ><button class="btn btn-primary">Buy</button></a>
                    </div>
                  </li>
                  <?php
                }
                ?>
              </ul>
            </div>
          </div>
          <?php
        }
        ?>
      </div>


      <!-- Container-fluid Ends-->
    </div>

    <?php include ('partial/footer.php'); ?>
  </div>
</div>

<?php include ('partial/scripts.php'); ?>
<script src="assets/js/tooltip-init.js"></script>
<?php include ('partial/footer-end.php'); ?>