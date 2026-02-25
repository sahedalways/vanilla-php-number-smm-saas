<style>
  .sidebar-wrapper {
    background-color: #050a1e !important;
    border-right: 1px solid rgba(61, 110, 253, 0.2);
  }


  .logo-wrapper span {
    color: #3d6efd !important;

  }


  .sidebar-link span {
    color: #ffffff !important;
    opacity: 0.8;
  }

  .stroke-icon {
    stroke: #3d6efd !important;
  }


  .sidebar-list:hover .sidebar-link,
  .sidebar-link.active {
    background-color: rgba(61, 110, 253, 0.15) !important;

    border-radius: 8px;
    margin: 0 10px;
    transition: all 0.3s ease;
  }

  .sidebar-list:hover span,
  .sidebar-link.active span {
    color: #3d6efd !important;
    font-weight: 600;
    opacity: 1;
  }


  #simple-bar::-webkit-scrollbar {
    width: 5px;
  }

  #simple-bar::-webkit-scrollbar-thumb {
    background: #3d6efd;
    border-radius: 10px;
  }
</style>

@php


session_start();
require_once 'helpers/session.php';
require_once 'include/config.php';
authOnly();


@endphp


<div class="sidebar-wrapper" sidebar-layout="stroke-svg">
  <div>
    <div class="logo-wrapper"><a href="#" style="display: inline-block;">
        <img class="img-fluid" src="./images/logo-png.png" width="35" alt="">
        <span style="color:orange; font-size: 18px; font-weight: bold; vertical-align: middle;"><?php echo $site_data['web_name']; ?></span>
      </a>

      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle text-light" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="#"><img class="img-fluid" src="<?php echo $site_data['logo_url']; ?>" width="40" alt=""></a></div>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn"><a href="#"><img class="img-fluid" src="assets/images/logo/logo-icon.png" alt=""></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
          <br>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="dashboard">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-home"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-home"></use>
              </svg><span>Dashboard</span></a>
          </li>

          <?php if ($userType === 'reseller'): ?>

            <li class="sidebar-list">
              <a class="sidebar-link sidebar-title link-nav" href="/views/reseller/services/smm/manage">
                <!-- SMM icon -->
                <svg class="stroke-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16v16H4z"></path> <!-- Replace with your SMM path -->
                </svg>
                <span>Manage SMM Services</span>
              </a>
            </li>

            <li class="sidebar-list">
              <a class="sidebar-link sidebar-title link-nav" href="/views/reseller/services/sms/manage">
                <!-- SMS icon -->
                <svg class="stroke-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 2H2v20l4-4h16V2z"></path>
                </svg>
                <span>Manage SMS Services</span>
              </a>
            </li>

          <?php elseif ($userType === 'customer'): ?>

            <li class="sidebar-list">
              <a class="sidebar-link sidebar-title link-nav" href="/views/customer/services/smm/manage">
                <!-- SMM icon -->
                <svg class="stroke-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16v16H4z"></path>
                </svg>
                <span>SMM Services</span>
              </a>
            </li>

            <li class="sidebar-list">
              <a class="sidebar-link sidebar-title link-nav" href="/views/customer/services/sms/list">
                <!-- SMS icon -->
                <svg class="stroke-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 2H2v20l4-4h16V2z"></path>
                </svg>
                <span>SMS Services</span>
              </a>
            </li>

          <?php endif; ?>


          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="recharge">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-learning"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-learning"></use>
              </svg>
              <span> Fund Wallet</span></a>
          </li>

          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="transactions">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-task"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-task"></use>
              </svg>
              <span>Transaction History</span></a>
          </li>

          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="https://t.me/foreignsmshub" target="_blank">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-button"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-button"></use>
              </svg>
              <span>Telegram Channel</span></a>
          </li>
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>
<!-- this website is design by @radiumsahil -->
