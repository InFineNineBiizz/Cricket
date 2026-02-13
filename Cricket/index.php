<?php
    session_start();
    include "connection.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home | <?php echo $title_name;?></title>
</head>
    <?php
        include 'header.php';
    ?>
<body>        
    <!--// Banner //-->
    <div class="ritekhela-banner-one">
        <div class="ritekhela-banner-one-layer">
            <span class="ritekhela-banner-transparent"></span>
            <img src="assets/extra-images/banner-2.jpg" alt="">
            <div class="ritekhela-banner-caption">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="animated-title"><i class="fas fa-cricket"></i> Welcome to <strong class="ritekhela-color glow-text">CrickFolio</strong> <i class="fas fa-cricket"></i></h1>
                            <div class="clearfix"></div>
                            <p class="animated-subtitle">Innovative Online Player Auction Platform for Cricket Tournament Organizers</p>
                            <div class="clearfix"></div>
                            <a href="aboutus.php" class="ritekhela-banner-btn enhanced-btn pulse-btn">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--// Banner //-->

    <!--// Content //-->
    <div class="ritekhela-main-content">

        <!--// Main Section //-->
        <div class="ritekhela-main-section ritekhela-fixture-slider-full" id="about">
            <div class="container">
                <div class="row">

                    <!--// Left Section //-->
                    <div class="col-md-12">

                        <!--// Fancy Title Two //-->
                        <div class="ritekhela-fancy-title-two enhanced-title">
                            <h2><i class="fas fa-cricket"></i> What is CrickHunt? <i class="fas fa-cricket"></i></h2>
                        </div>
                        <!--// Fancy Title Two //-->

                        <!--// About Content //-->
                        <div class="ritekhela-editor-detail" style="margin-bottom: 50px;">
                            <p style="text-align: center; font-size: 16px; line-height: 32px; margin-bottom: 30px;">
                                CrickHunt is an innovative online player auction platform designed specifically for cricket tournament organizers. It simplifies the entire player auction process, eliminating the need for cumbersome Excel sheets and manual tasks. With CrickHunt, you get access to a fully automated system where you can manage player auctions seamlessly and download all relevant data anytime.
                            </p>
                            <p style="text-align: center; font-size: 16px; line-height: 32px; margin-bottom: 30px;">
                                CrickHunt also enhances the experience for your sponsors by allowing you to showcase their advertisements directly on the auction screen. Additionally, our platform offers real-time updates and live streaming overlays, making your auction process more engaging and professional. Whether you're organizing a local cricket tournament or a larger event, CrickHunt is your all-in-one solution to streamline operations and attract more sponsors.
                            </p>
                        </div>
                        <!--// About Content //-->

                    </div>
                    <!--// Left Section //-->
                    
                </div>
            </div>
        </div>
        <!--// Main Section //-->

        <!--// Steps Section //-->
        <div class="ritekhela-main-section" style="background-color: #f5f5f5; padding: 60px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <!--// Fancy Title Two //-->
                        <div class="ritekhela-fancy-title-two enhanced-title">
                            <h2><i class="fas fa-list-ol"></i> Steps <i class="fas fa-list-ol"></i></h2>
                        </div>
                        <!--// Fancy Title Two //-->

                        <!-- Steps Process -->
                        <div class="row" style="margin-top: 50px;">
                            <!-- Step 01 - Sign Up -->
                            <div class="col-md-6" style="margin-bottom: 40px;">
                                <div class="ritekhela-team-view1-text enhanced-player-text" style="padding: 30px 30px 25px 30px;">
                                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                        <span style="width: 60px; height: 60px; background: linear-gradient(135deg, #d4145a 0%, #9b1b5e 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; margin-right: 20px; flex-shrink: 0;">01</span>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 22px; text-transform: uppercase;">SIGN UP</h2>
                                    </div>
                                    <p style="color: #ffffff; margin-bottom: 0;">You can use your mobile number to sign in to the platform with OTP verification.</p>
                                </div>
                            </div>

                            <!-- Step 02 - Create Auction -->
                            <div class="col-md-6" style="margin-bottom: 40px;">
                                <div class="ritekhela-team-view1-text enhanced-player-text" style="padding: 30px 30px 25px 30px;">
                                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                        <span style="width: 60px; height: 60px; background: linear-gradient(135deg, #9b1b5e 0%, #7a2283 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; margin-right: 20px; flex-shrink: 0;">02</span>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 22px; text-transform: uppercase;">CREATE AUCTION</h2>
                                    </div>
                                    <p style="color: #ffffff; margin-bottom: 0;">Fill a form to provide details and logo for the Auction.</p>
                                </div>
                            </div>

                            <!-- Step 03 - Add Teams -->
                            <div class="col-md-6" style="margin-bottom: 40px;">
                                <div class="ritekhela-team-view1-text enhanced-player-text" style="padding: 30px 30px 25px 30px;">
                                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                        <span style="width: 60px; height: 60px; background: linear-gradient(135deg, #7a2283 0%, #5729a8 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; margin-right: 20px; flex-shrink: 0;">03</span>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 22px; text-transform: uppercase;">ADD TEAMS</h2>
                                    </div>
                                    <p style="color: #ffffff; margin-bottom: 0;">Add all the teams and their logos one by one by filling the form.</p>
                                </div>
                            </div>

                            <!-- Step 04 - Add Players -->
                            <div class="col-md-6" style="margin-bottom: 40px;">
                                <div class="ritekhela-team-view1-text enhanced-player-text" style="padding: 30px 30px 25px 30px;">
                                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                        <span style="width: 60px; height: 60px; background: linear-gradient(135deg, #5729a8 0%, #3b3bcc 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; margin-right: 20px; flex-shrink: 0;">04</span>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 22px; text-transform: uppercase;">ADD PLAYERS</h2>
                                    </div>
                                    <p style="color: #ffffff; margin-bottom: 0;">Share with players the registration form or add them yourself using dashboard.</p>
                                </div>
                            </div>

                            <!-- Step 05 - Auction Dashboard -->
                            <div class="col-md-6" style="margin-bottom: 40px;">
                                <div class="ritekhela-team-view1-text enhanced-player-text" style="padding: 30px 30px 25px 30px;">
                                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                        <span style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b3bcc 0%, #2563eb 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; margin-right: 20px; flex-shrink: 0;">05</span>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 22px; text-transform: uppercase;">AUCTION DASHBOARD</h2>
                                    </div>
                                    <p style="color: #ffffff; margin-bottom: 0;">Fill a form to provide details and logo for the Auction.</p>
                                </div>
                            </div>

                            <!-- Step 06 - Summary Screen -->
                            <div class="col-md-6" style="margin-bottom: 40px;">
                                <div class="ritekhela-team-view1-text enhanced-player-text" style="padding: 30px 30px 25px 30px;">
                                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                        <span style="width: 60px; height: 60px; background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; margin-right: 20px; flex-shrink: 0;">06</span>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 22px; text-transform: uppercase;">SUMMARY SCREEN</h2>
                                    </div>
                                    <p style="color: #ffffff; margin-bottom: 0;">Add all the teams and their logos one by one by filling the form.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Steps Section //-->

        <!--// Features Section //-->
        <div class="ritekhela-main-section" style="padding: 60px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <!--// Fancy Title Two //-->
                        <div class="ritekhela-fancy-title-two enhanced-title">
                            <h2><i class="fa fa-star"></i> Key Features</h2>
                        </div>
                        <!--// Fancy Title Two //-->

                        <!--// Features //-->
                        <div class="ritekhela-team ritekhela-team-view1">
                            <ul class="row">
                                <li class="col-md-4 offset-md player-card-animate">
                                    <div class="ritekhela-team-view1-text enhanced-player-text" style="text-align: center; padding: 40px 25px;">
                                        <i class="fas fa-mobile-alt" style="font-size: 48px; color: #ffdc11; margin-bottom: 20px; display: block;"></i>
                                        <h2 style="color: #ffffff; font-size: 20px; margin-bottom: 15px;"><a href="#" style="color: #ffffff;">Fully Automated</a></h2>
                                        <p class="player-desc" style="color: #ffffff;">Eliminate Excel sheets and manual tasks with our automated system</p>
                                    </div>
                                </li>
                                <li class="col-md-4 offset-md player-card-animate">
                                    <div class="ritekhela-team-view1-text enhanced-player-text" style="text-align: center; padding: 40px 25px;">
                                        <i class="fas fa-chart-line" style="font-size: 48px; color: #ffdc11; margin-bottom: 20px; display: block;"></i>
                                        <h2 style="color: #ffffff; font-size: 20px; margin-bottom: 15px;"><a href="#" style="color: #ffffff;">Real-Time Updates</a></h2>
                                        <p class="player-desc" style="color: #ffffff;">Get live updates and streaming overlays during auctions</p>
                                    </div>
                                </li>
                                <li class="col-md-4 offset-md player-card-animate">
                                    <div class="ritekhela-team-view1-text enhanced-player-text" style="text-align: center; padding: 40px 25px;">
                                        <i class="fas fa-ad" style="font-size: 48px; color: #ffdc11; margin-bottom: 20px; display: block;"></i>
                                        <h2 style="color: #ffffff; font-size: 20px; margin-bottom: 15px;"><a href="#" style="color: #ffffff;">Sponsor Integration</a></h2>
                                        <p class="player-desc" style="color: #ffffff;">Showcase sponsor advertisements directly on auction screens</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!--// Features //-->

                    </div>
                </div>
            </div>
        </div>
        <!--// Features Section //-->

        <!--// Benefits Section //-->
        <div class="ritekhela-main-section" style="background-color: #f5f5f5; padding: 60px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <!--// Fancy Title Two //-->
                        <div class="ritekhela-fancy-title-two enhanced-title">
                            <h2><i class="fa fa-trophy"></i> Why Choose CrickHunt?</h2>
                        </div>
                        <!--// Fancy Title Two //-->

                        <div class="row" style="margin-top: 40px;">
                            <div class="col-md-6" style="margin-bottom: 30px;">
                                <div class="ritekhela-blog-view1-text" style="padding: 25px; background-color: #3e454c; border-radius: 5px;">
                                    <h2 style="color: #ffdc11; font-size: 18px; margin-bottom: 15px;"><i class="fa fa-check-circle"></i> Seamless Player Management</h2>
                                    <p style="color: #ffffff; margin-bottom: 0;">Easily add, edit, and manage player profiles with all necessary details in one place.</p>
                                </div>
                            </div>
                            <div class="col-md-6" style="margin-bottom: 30px;">
                                <div class="ritekhela-blog-view1-text" style="padding: 25px; background-color: #3e454c; border-radius: 5px;">
                                    <h2 style="color: #ffdc11; font-size: 18px; margin-bottom: 15px;"><i class="fa fa-check-circle"></i> Team Budget Tracking</h2>
                                    <p style="color: #ffffff; margin-bottom: 0;">Real-time budget calculations ensure teams stay within their spending limits.</p>
                                </div>
                            </div>
                            <div class="col-md-6" style="margin-bottom: 30px;">
                                <div class="ritekhela-blog-view1-text" style="padding: 25px; background-color: #3e454c; border-radius: 5px;">
                                    <h2 style="color: #ffdc11; font-size: 18px; margin-bottom: 15px;"><i class="fa fa-check-circle"></i> Download Reports Anytime</h2>
                                    <p style="color: #ffffff; margin-bottom: 0;">Export complete auction data and team rosters in various formats for your records.</p>
                                </div>
                            </div>
                            <div class="col-md-6" style="margin-bottom: 30px;">
                                <div class="ritekhela-blog-view1-text" style="padding: 25px; background-color: #3e454c; border-radius: 5px;">
                                    <h2 style="color: #ffdc11; font-size: 18px; margin-bottom: 15px;"><i class="fa fa-check-circle"></i> Professional Presentation</h2>
                                    <p style="color: #ffffff; margin-bottom: 0;">Impress your audience with professional auction displays and sponsor integrations.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Benefits Section //-->

        <!--// Call to Action //-->
        <div class="ritekhela-main-section" style="background: linear-gradient(135deg, #3e454c 0%, #2a2f35 100%); padding: 80px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12" style="text-align: center;">
                        <h2 style="color: #ffffff; font-size: 40px; margin-bottom: 20px; font-weight: bold; text-transform: uppercase;">Ready to Get Started?</h2>
                        <p style="color: #ffffff; font-size: 18px; margin-bottom: 40px; max-width: 800px; margin-left: auto; margin-right: auto;">Join CrickHunt today and revolutionize your cricket tournament auctions. Experience the power of automation and professional auction management.</p>
                        <a href="signup.php" class="ritekhela-banner-btn enhanced-btn pulse-btn" style="display: inline-block; margin-right: 15px; margin-bottom: 10px;">Sign Up Now</a>
                        <a href="contact-us.php" class="ritekhela-banner-btn enhanced-btn pulse-btn" style="display: inline-block; background-color: transparent; color: #ffdc11; border-color: #ffdc11; margin-bottom: 10px;">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
        <!--// Call to Action //-->

    </div>
    <!--// Content //-->

    <?php
        include 'footer.php';
    ?>
    <?php include "scripts.php";?>
</body>
</html>