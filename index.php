<?php
// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$loggedIn = isset($_SESSION['auth_token']);
$userName = $_SESSION['name'] ?? '';
$userType = $_SESSION['type'] ?? '';
$dashboardUrl = '/views/customer/dashboard';
if ($userType === 'admin') {
    $dashboardUrl = '/views/admin/dashboard';
}

$userAvatar = '/images/default-avatar.png';

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allsmsverify</title>
    <link rel="shortcut icon" href="./images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>

<body>
    <nav class="top-nav main-padding dflex-between">
        <div class="logo-div">
            <img class="logo-img" src="./images/logo-png.png" alt="Allsmsverify logo">
            <h1 class="logo-text">Allsmsverify</h1>
        </div>
        <div class="right-top-nav">
            <?php if (!$loggedIn): ?>
                <a href="/login" class="btn-clear me-3">Log in</a>
                <a href="/register" class="btn-color">Register</a>
            <?php else: ?>



                <a href="<?php echo $dashboardUrl; ?>" class="btn-clear me-3 d-flex align-items-center">
                    <!-- Avatar Icon -->
                    <i class="fa-solid fa-circle-user fa-lg me-2"></i>
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="avatar" class="rounded-circle me-2" style="width:32px; height:32px;">
                    <span><?php echo htmlspecialchars($userName); ?></span>
                </a>
            <?php endif; ?>
        </div>

    </nav>
    <header class="hero main-margin">
        <div class="hero-left">
            <h1 class="hero-heading">Your No. 1 <br>SMS Verification Service Online</h1>
            <p class="hero-paragraph mt-2">
                At Allsmsverify, we offer top-quality SMS verifications using non-VoIP
                numbers, ensuring as fast, secure, and one-time-use options to protect
                your online identity.
            </p>
            <div class="mt-2 dflex-hero">
                <a class="btn-color mb-2 mb-md-0" href="login">
                    Buy Number Now
                    <i class="bi bi-arrow-right ms-2 fw-bold"></i>
                </a>
                <div class="dflex-a ms-0 ms-md-5">
                    <div class="profile-stack">
                        <img src="./images/dpt1.png" alt="Profile 1" class="profile-img_">
                        <img src="./images/dpt2.png" alt="Profile 2" class="profile-img_">
                        <img src="./images/dpt3.png" alt="Profile 3" class="profile-img_">
                        <img src="./images/dpt4.png" alt="Profile 4" class="profile-img_">
                        <img src="./images/dpt5.png" alt="Profile 5" class="profile-img_ mr-0">
                    </div>
                    <p class="mb-0">Join 2,000+ <br>Happy Customers</p>
                </div>
            </div>
        </div>
        <div class="hero-right mb-4 mb-md-0">
            <img src="./images/hero-image.png" class="hero-img" alt="Allsmsverify Hero Image">
        </div>
    </header>

    <section class="section-1">
        <div class="text-center">
            <h2 class="highlight-header">How it works</h2>
            <h1 class="my-2 general-heading">How Allsmsverify works?</h1>
            <p class="first-sect-p mx-auto text-center">Allsmsverify expands your reach with SMS verification in 50+ countries,
                compatible with 900+ services, ensuring global connectivity and security.
            </p>
        </div>
        <div class="sect-divs">
            <div class="vertical-equal-spacing w-100">
                <img class="order-images" src="./images/01-email-login-2.gif" alt="">
                <h3 class="my-3 small-heading">Create an Account</h3>
                <p class="order-paragraph">After creating an account, log in to your account.</p>
            </div>
            <div class="vertical-equal-spacing w-100">
                <img class="order-images" src="./images/22-purse-1.gif" alt="">
                <h3 class="my-3 small-heading">Fund Wallet</h3>
                <p class="order-paragraph">After you log in into your dashboard, top UP your account with
                    at least 1,000 or $1.
                </p>
            </div>
            <div class="vertical-equal-spacing w-100">
                <img class="order-images" src="./images/09-chat-1.gif" alt="">
                <h3 class="my-3 small-heading">Place Order</h3>
                <p class="order-paragraph">Select the desired country and service. Copy the virtual number and
                    wait for sms code.
                </p>
            </div>
        </div>
    </section>

    <section class="section-2">
        <div class="text-center">
            <h2 class="highlight-header">Our Services</h2>
            <h1 class="my-2 general-heading">Our SMS<br>Verification Services</h1>
        </div>
        <div class="sect2-divs">
            <div class="sect2-sub1-divs">
                <div class="sub1-div">
                    <img class="sect2-imgs" src="./images/flags.jpg" alt="">
                    <div class="div-textarea">
                        <h3 class="smaller-heading">50+ Countries Supported</h3>
                        <p class="small-text">
                            Alternatively supports SMS verification in over 50 countries,
                            ensuring seamless global connectivity and security for
                            your digital needs.
                        </p>
                    </div>
                </div>
                <div class="sub2-div">
                    <img class="sect2-imgs" src="./images/socials.jpg" alt="">
                    <div class="div-textarea">
                        <h3 class="smaller-heading">900+ Apps & Services Supported</h3>
                        <p class="small-text">
                            We currently support a large variety of services including,
                            but not limited to Steam, Tinder, Google, Uber, Discord
                            and even Twitter.
                        </p>
                    </div>
                </div>
            </div>
            <div class="sect2-sub2-divs">
                <div class="sub3-div">
                    <img class="sect2-imgs" src="./images/comms-man.jpg" alt="">
                    <div class="div-textarea">
                        <h3 class="smaller-heading">24/7 Support</h3>
                        <p class="small-text">
                            Our support maintains it's accessibilty through our
                            WhatsApp, LIVE chat and also through emails. Never feel left out.
                        </p>
                    </div>
                </div>
                <div class="sub4-div text-center">
                    <div class="dps-container">
                        <div class="profile-stack">
                            <img src="./images/dpt1.png" alt="Profile 1" class="profile-img">
                            <img src="./images/dpt2.png" alt="Profile 2" class="profile-img">
                            <img src="./images/dpt3.png" alt="Profile 3" class="profile-img">
                            <img src="./images/dpt4.png" alt="Profile 3" class="profile-img">
                            <img src="./images/dpt5.png" alt="Profile 3" class="profile-img">
                        </div>
                        <p class="order-paragraph mt-2">Don't take our word for it</p>
                    </div>
                    <h1 class="general-heading w-100 w-md-50">Trusted by <br>+10K users</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="section-3">
        <div class="text-center">
            <h2 class="highlight-header">Testimonials</h2>
            <h1 class="my-2 general-heading">Why our customers loves us</h1>
            <p class="first-sect-p mx-auto text-center">We're trusted by +1500 companies in +30 countries.
                Explain what results you've gained for your customers.
            </p>
        </div>
        <div class="sect2-sub1-divs mt-60">
            <div class="review-div">
                <p class="small-text">
                    "This is hands down the best tool for bypassing 2-factor OTP codes.
                    Reliable numbers and amazing support team!"
                </p>
                <div class="review-dp-container">
                    <img class="review-dp" src="./images/dp1.jpg" alt="">
                    <div class="review-text">
                        <h3 class="smaller-heading mb-0">Mark John</h3>
                        <p class="smaller-text mb-0">UX/UI Designer, TechVista</p>
                    </div>
                </div>
            </div>
            <div class="review-div">
                <p class="small-text">
                    "Literally took me 2 minutes to create a bunch of anonymous accounts
                    for Twitter & Discord using your temporary phone verification service.
                    Good job guys."
                </p>
                <div class="review-dp-container">
                    <img class="review-dp" src="./images/dp2.jpg" alt="">
                    <div class="review-text">
                        <h3 class="smaller-heading mb-0">Sarah Thompson</h3>
                        <p class="smaller-text mb-0">Creative Director, PixelCraft</p>
                    </div>
                </div>
            </div>
            <div class="review-div">
                <p class="small-text">
                    "By far the bet service to bypass 2-factor OTP codes. Their numbers
                    always work and the support team is fantastic"
                </p>
                <div class="review-dp-container">
                    <img class="review-dp" src="./images/dp3.jpg" alt="">
                    <div class="review-text">
                        <h3 class="smaller-heading mb-0">Megan Carter</h3>
                        <p class="smaller-text mb-0">Founder, WebVisio Studios</p>
                    </div>
                </div>
            </div>
            <div class="review-div">
                <p class="small-text">
                    "Unmatched service for bypassing 2-factor OTP codes. The numbers
                    are dependable, and the support is excellent"
                </p>
                <div class="review-dp-container">
                    <img class="review-dp" src="./images/dp4.jpg" alt="">
                    <div class="review-text">
                        <h3 class="smaller-heading mb-0">Jessica Walker</h3>
                        <p class="smaller-text mb-0">Product Manager, InnovateHub</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-4">
        <div class="faq-div">
            <div class="faq-left">
                <div class="faq-top">
                    <h1 class="general-heading">Frequently Asked Questions</h1>
                </div>
                <div class="faq-bottom">
                    <h2 class="small-heading">More questions?</h2>
                    <p class="order-paragraph">We're always ready to help you out</p>
                    <div class="dflex-between mt-1">
                        <div class="profile-stack">
                            <img src="./images/dpx1.png" alt="Profile 1" class="profile-img">
                            <img src="./images/dpx2.png" alt="Profile 2" class="profile-img">
                            <img src="./images/dpx3.png" alt="Profile 3" class="profile-img">
                            <img src="./images/dpx4.png" alt="Profile 3" class="profile-img">
                        </div>
                        <a class="btn-color" href="https://t.me/allsmsverifyteam">Contact support</a>
                    </div>
                </div>
            </div>
            <div class="faq-right">
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item custom-accordion">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed smaller-heading" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                Is all number Real Sim Card or Virtual Number?
                            </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <h3 class="smaller-heading">Is all number Real Sim Card or Virtual Number?</h3>
                                <br>
                                <p class=" order-paragraph">All numbers are Virtual numbers.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item  custom-accordion">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed smaller-heading" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                How do I buy the numbers and get the code?
                            </button>
                        </h2>
                        <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <h3 class="smaller-heading">How do I buy the numbers and get the code?</h3>
                                <br>
                                <ul class="order-paragraph">
                                    <li>Select a country of your choice, select a service
                                        like (Twitter, Telegram or WhatsApp, etc), click on the "place orders"
                                        button, and a number will be generated. You can search for a service
                                        usingthe search box provided.
                                    </li>
                                    <li>Copy the number generated and paste it on the
                                        platform of the services (Twitter, Telegram or WhatsApp, etc.)
                                    </li>
                                    <li>After some time, normally btw 30 seconds to 7 minutes,
                                        an OTP code from the service provider will be generated for you.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item custom-accordion">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed smaller-heading" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                How many times can I use the number for verification?
                            </button>
                        </h2>
                        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <h3 class="smaller-heading">How many times can I use the number for verification?</h3>
                                <br>
                                <p class=" order-paragraph">You can only make use of the number for only one service.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item custom-accordion">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed smaller-heading" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                                What to do if the verification code does not come?
                            </button>
                        </h2>
                        <div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <h3 class="smaller-heading">What to do if the verification code does not come?</h3>
                                <br>
                                <p class=" order-paragraph">We only deduct money if you have received the verification code.
                                    So, if you haven't received the code, try and buy a new number.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item custom-accordion_">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed smaller-heading" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
                                How can I Top-Up?
                            </button>
                        </h2>
                        <div id="flush-collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <h3 class="smaller-heading">How can I Top-Up?</h3>
                                <br>
                                <p class=" order-paragraph">With the help of Paystack, you can add funds via Bank Transfer,
                                    Visa/MasterCard, and USSD. Once this is done, the system adds the fund automatically to
                                    your account.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-center">
        <div class="dflex-between_ mb-5">
            <div class="logo-div-mini_ mb-5 mb-md-0">
                <img class="logo-img-mini" src="./images/logo-png.png" alt="Allsmsverify logo">
                <h1 class="logo-text-mini">Allsmsverify</h1>
            </div>
            <div class="dflex-g">
                <i class="bi bi-facebook"></i>
                <i class="bi bi-twitter"></i>
                <i class="bi bi-instagram"></i>
                <i class="bi bi-youtube"></i>
                <i class="bi bi-linkedin"></i>
                <i class="bi bi-tiktok"></i>
            </div>
        </div>
        <small class="copy">Copyright &copy; 2024 Allsmsverify</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/index.js"></script>
</body>

</html>
