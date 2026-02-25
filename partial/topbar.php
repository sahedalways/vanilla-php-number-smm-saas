<div class="page-header">
    <div class="header-wrapper row m-0">
        <form class="form-inline search-full col" action="#" method="get">
            <div class="form-group w-100">
                <div class="Typeahead Typeahead--twitterUsers">
                    <div class="u-posRelative">
                        <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search Cuba .." name="q" title="" autofocus>
                        <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...</span></div><i class="close-search" data-feather="x"></i>
                    </div>
                    <div class="Typeahead-menu"></div>
                </div>
            </div>
        </form>
        <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"><a href="index.html"><img class="img-fluid" src="assets/images/logo/logo.png" alt=""></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
        </div>
        <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
            <div class="notification-slider">

            </div>
        </div>
        <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">

                <li>
                    <div style="border: 2px solid orange; border-radius: 20px; padding: 5px; ">
                        <img src="https://cdn-icons-png.flaticon.com/512/2704/2704312.png" class="mr-1" width="21" height="21" alt="Rupee icon">
                        <span class="font-bold text-sm" style="font-weight:bold; font-size:15px" id="current_balance">
                            ₦<?php echo isset($userwallet['balance']) ? $userwallet['balance'] : '0.00'; ?>
                        </span>
                    </div>
                </li>




                <?php
                if ($userdata['image_url'] == "") {
                    $img_url = 'https://Foreign sms.com/images/dpt1.png';
                } else {
                    $img_url = $userdata['image_url'];
                }

                ?>
                <li class="profile-nav onhover-dropdown pe-0 py-0">
                    <div class="media profile-media">
                        <div class="avatar"><img class="img-40 rounded-circle" id="profile-imgs" src="https://img.freepik.com/free-vector/blue-circle-with-white-user_78370-4707.jpg?semt=ais_user_personalization&w=740&q=80" width="40" height="40" alt="#"></div>
                        <div class="media-body"><span><?php echo $userdata['name']; ?></span>
                            <p class="mb-0 font-roboto"><?php echo $userdata['type']; ?><i class="middle fa fa-angle-down"></i></p>
                        </div>
                    </div>

                </li>
            </ul>
        </div>
        <script class="result-template" type="text/x-handlebars-template">
            <div class="ProfileCard u-cf">
            <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
            <div class="ProfileCard-details">
            <div class="ProfileCard-realName">{{name}}</div>
            </div>
            </div>
          </script>
        <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
    </div>
</div>
