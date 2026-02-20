<style>
  .radium {
    height: 29%;
    width: auto;

  }

  .sidebar-list:hover,
  .sidebar-link.active {
    background-color: #FFCC99 !important;
  }
</style>
<div class="sidebar-wrapper" sidebar-layout="stroke-svg">
  <div>
    <div class="logo-wrapper"><a href="#" style="display: inline-block;">
        <img class="img-fluid" src="./images/logo-png.png" width="35" alt="">
        <span style="color:orange; font-size: 18px; font-weight: bold; vertical-align: middle;"><?php echo $site_data['web_name']; ?></span>
      </a>

      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="#"><img class="img-fluid" src="<?php echo $site_data['logo_url']; ?>" width="40" alt=""></a></div>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn"><a href="#"><img class="img-fluid" src="assets/images/logo/logo-icon.png" alt=""></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
          <!-- <li class="sidebar-main-title">
            <div>
              <h6 class="lan-1">General</h6>
            </div>
          </li> --><br>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="dashboard">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-home"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-home"></use>
              </svg><span>Dashboard</span></a>
          </li>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="buy-number">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-chat"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-chat"></use>
              </svg>
              <span>Buy Numbers</span></a>
          </li>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="recharge">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-learning"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-learning"></use>
              </svg>
              <span> Fund Wallet</span></a>
          </li>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="numbers">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-form"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-form"></use>
              </svg>
              <span>Numbers History</span></a>
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
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="api-tool">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-internationalization"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-internationalization"></use>
              </svg>
              <span>Api Documents</span></a>
          </li>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="https://t.me/Foreign smsteam" target="_blank">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-support-tickets"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-support-tickets"></use>
              </svg>
              <span>Support</span></a>
          </li>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="https://t.me/+70vBucP3nkNmZDI0" target="_blank">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-button"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-button"></use>
              </svg>
              <span>Telegram Channel</span></a>
          </li>
          <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="http://www.instagram.com/Foreign sms" target="_blank">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-button"></use>
              </svg>
              <svg class="fill-icon">
                <use href="assets/svg/icon-sprite.svg#fill-button"></use>
              </svg>
              <span>Follow On instagram</span></a>
          </li>
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>
<!-- this website is design by @radiumsahil -->
