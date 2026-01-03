<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us | CrickFolio</title>
    <?php include "links.php"; ?>
    <style>
        /* Critical Fixes */
        body {
            font-family: 'Roboto', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Remove any wrapper that might be causing issues */
        .ritekhela-main-content,
        .main-content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
        }

        /* About Hero Section - FIXED */
        .about-hero {
            background: linear-gradient(135deg, rgba(62, 69, 76, 0.95), rgba(42, 46, 51, 0.95)),
                url('https://source.unsplash.com/1920x600/?cricket,stadium');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 150px 0 100px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
            margin: 0;
            width: 100%;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 220, 17, 0.1) 0%, transparent 70%);
            animation: pulse-bg 15s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes pulse-bg {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(-10%, -10%) scale(1.1);
            }
        }

        .about-hero .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .about-hero h1 {
            font-size: 56px;
            font-weight: 700;
            margin: 0 0 20px 0;
            color: #fff;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .about-hero .highlight {
            color: #ffdc11;
            text-shadow: 0 0 20px rgba(255, 220, 17, 0.5);
        }

        .about-hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
            color: #f0f0f0;
        }

        /* Statistics Section */
        .about-stats {
            background: #3e454c;
            padding: 60px 0;
            margin: -50px 0 0 0;
            position: relative;
            z-index: 10;
        }

        .about-stats .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .stat-box {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255, 220, 17, 0.1);
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            margin-bottom: 20px;
        }

        .stat-box:hover {
            transform: translateY(-10px);
            background: rgba(255, 220, 17, 0.2);
            border-color: #ffdc11;
            box-shadow: 0 10px 30px rgba(255, 220, 17, 0.3);
        }

        .stat-box i {
            font-size: 48px;
            color: #ffdc11;
            margin-bottom: 20px;
            display: block;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 700;
            color: #ffdc11;
            display: block;
            margin-bottom: 10px;
            line-height: 1;
        }

        .stat-label {
            font-size: 16px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        /* Story Section */
        .about-story {
            padding: 80px 0;
            background: #f5f5f5;
            margin: 0;
        }

        .about-story .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: 700;
            color: #3e454c;
            text-transform: uppercase;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
            margin: 0;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: #ffdc11;
        }

        .section-title h2 .highlight {
            color: #ffdc11;
        }

        .story-content {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-bottom: 30px;
        }

        .story-image {
            flex: 1;
            position: relative;
        }

        .story-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: block;
            max-width: 100%;
            height: auto;
        }

        .story-image::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100%;
            height: 100%;
            border: 3px solid #ffdc11;
            border-radius: 10px;
            z-index: -1;
        }

        .story-text {
            flex: 1;
        }

        .story-text h3 {
            font-size: 32px;
            color: #3e454c;
            margin: 0 0 20px 0;
            font-weight: 600;
        }

        .story-text p {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
            margin: 0 0 15px 0;
        }

        /* Mission & Vision Section */
        .mission-vision {
            padding: 80px 0;
            background: #3e454c;
            margin: 0;
        }

        .mission-vision .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .mv-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid rgba(255, 220, 17, 0.2);
            transition: all 0.3s ease;
            height: 100%;
            margin-bottom: 20px;
        }

        .mv-card:hover {
            transform: translateY(-10px);
            border-color: #ffdc11;
            background: rgba(255, 220, 17, 0.1);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .mv-card i {
            font-size: 60px;
            color: #ffdc11;
            margin-bottom: 25px;
            display: block;
        }

        .mv-card h3 {
            font-size: 28px;
            color: #fff;
            margin: 0 0 20px 0;
            text-transform: uppercase;
            font-weight: 600;
        }

        .mv-card p {
            font-size: 16px;
            color: #ddd;
            line-height: 1.8;
            margin: 0;
        }

        /* Team Section */
        .team-section {
            padding: 80px 0;
            background: #f5f5f5;
            margin: 0;
        }

        .team-section .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .team-member {
            text-align: center;
            margin-bottom: 40px;
        }

        .team-member-img {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .team-member-img img {
            width: 100%;
            transition: transform 0.3s ease;
            display: block;
            max-width: 100%;
            height: auto;
        }

        .team-member:hover .team-member-img img {
            transform: scale(1.1);
        }

        .team-member-img::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent 0%, rgba(255, 220, 17, 0.8) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .team-member:hover .team-member-img::before {
            opacity: 1;
        }

        .team-social {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .team-member:hover .team-social {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .team-social a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #fff;
            color: #3e454c;
            line-height: 40px;
            border-radius: 50%;
            margin: 0 5px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .team-social a:hover {
            background: #3e454c;
            color: #ffdc11;
            transform: translateY(-5px);
        }

        .team-member h4 {
            font-size: 22px;
            color: #3e454c;
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .team-member span {
            font-size: 14px;
            color: #ffdc11;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        /* Values Section */
        .values-section {
            padding: 80px 0;
            background: #fff;
            margin: 0;
        }

        .values-section .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .value-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 40px;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 10px;
            border-left: 4px solid #ffdc11;
            transition: all 0.3s ease;
        }

        .value-item:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .value-icon {
            width: 70px;
            height: 70px;
            background: #ffdc11;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 25px;
            flex-shrink: 0;
        }

        .value-icon i {
            font-size: 32px;
            color: #3e454c;
        }

        .value-content h4 {
            font-size: 22px;
            color: #3e454c;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .value-content p {
            font-size: 15px;
            color: #666;
            line-height: 1.7;
            margin: 0;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #3e454c 0%, #2a2e33 100%);
            padding: 80px 0;
            text-align: center;
            margin: 0;
        }

        .cta-section .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .cta-section h2 {
            font-size: 42px;
            color: #fff;
            margin: 0 0 20px 0;
            font-weight: 700;
        }

        .cta-section p {
            font-size: 18px;
            color: #ddd;
            margin: 0 auto 40px;
            max-width: 700px;
            line-height: 1.8;
        }

        .cta-button {
            display: inline-block;
            padding: 18px 50px;
            background: #ffdc11;
            color: #3e454c;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .cta-button:hover {
            background: #fff;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 220, 17, 0.4);
            color: #3e454c;
            text-decoration: none;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .about-hero {
                padding: 120px 0 80px;
            }

            .about-hero h1 {
                font-size: 42px;
            }

            .story-content {
                gap: 30px;
            }

            .section-title h2 {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .about-hero {
                padding: 100px 20px 60px;
                background-attachment: scroll;
            }

            .about-hero h1 {
                font-size: 32px;
            }

            .about-hero p {
                font-size: 16px;
            }

            .story-content {
                flex-direction: column;
                gap: 30px;
            }

            .story-image::before {
                top: -10px;
                left: -10px;
            }

            .section-title h2 {
                font-size: 28px;
            }

            .stat-box {
                margin-bottom: 20px;
            }

            .mv-card {
                margin-bottom: 20px;
            }

            .value-item {
                flex-direction: column;
                text-align: center;
            }

            .value-icon {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .cta-section h2 {
                font-size: 32px;
            }

            .cta-section p {
                font-size: 16px;
            }

            .cta-button {
                padding: 15px 40px;
                font-size: 16px;
            }
        }

        @media (max-width: 576px) {
            .about-hero {
                padding: 80px 15px 50px;
            }

            .about-hero h1 {
                font-size: 28px;
            }

            .about-hero p {
                font-size: 14px;
            }

            .section-title h2 {
                font-size: 24px;
            }

            .stat-number {
                font-size: 32px;
            }

            .stat-label {
                font-size: 14px;
            }

            .story-text h3 {
                font-size: 24px;
            }

            .story-text p {
                font-size: 14px;
            }

            .mv-card h3 {
                font-size: 22px;
            }

            .mv-card p {
                font-size: 14px;
            }

            .value-content h4 {
                font-size: 18px;
            }

            .value-content p {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="ritekhela-subheader">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>About-US</h1>
                    <ul class="ritekhela-breadcrumb">
                        <li><a href="index.php">Home</a></li>
                        <li>About US</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--// Hero Section //-->
    <section class="about-hero">
        <div class="container">
            <h1>About <span class="highlight">CrickFolio</span></h1>
            <p>Pioneering the future of cricket through innovative technology, passionate community building, and unforgettable experiences.</p>
        </div>
    </section>

    <!--// Stats Section //-->
    <section class="about-stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <i class="fas fa-users"></i>
                        <span class="stat-number">50K+</span>
                        <span class="stat-label">Active Users</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <i class="fas fa-trophy"></i>
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Tournaments</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <i class="fas fa-users"></i>
                        <span class="stat-number">30+</span>
                        <span class="stat-label">Teams</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <i class="fas fa-user"></i>
                        <span class="stat-number">100+</span>
                        <span class="stat-label">Players</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--// Story Section //-->
    <section class="about-story">
        <div class="container">
            <div class="section-title">
                <h2>Our <span class="highlight">Story</span></h2>
            </div>
            <div class="story-content">
                <div class="story-image">
                    <img src="https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=600&h=400&fit=crop" alt="Our Story">
                </div>
                <div class="story-text">
                    <h3>Where Passion Meets Innovation</h3>
                    <p>Founded in 2018, CrickFolio began with a simple vision: to revolutionize how cricket fans engage with the sport they love. What started as a small project by cricket enthusiasts has grown into a thriving platform serving thousands of users worldwide.</p>
                    <p>Our journey has been marked by continuous innovation, from introducing real-time auction systems to creating comprehensive player portfolios. We've built more than just a platform – we've created a community where cricket lovers can connect, compete, and celebrate the game together.</p>
                    <p>Today, CrickFolio stands as a testament to what can be achieved when passion, technology, and community come together. We're proud to be at the forefront of digital cricket experiences.</p>
                </div>
            </div>
        </div>
    </section>

    <!--// Mission & Vision //-->
    <section class="mission-vision">
        <div class="container">
            <div class="section-title">
                <h2 style="color: #fff;">Mission & <span class="highlight">Vision</span></h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mv-card">
                        <i class="fas fa-bullseye"></i>
                        <h3>Our Mission</h3>
                        <p>To create the most engaging and innovative cricket platform that brings fans closer to the game, empowers players, and builds a vibrant community united by their love for cricket.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mv-card">
                        <i class="fas fa-eye"></i>
                        <h3>Our Vision</h3>
                        <p>To be the world's leading digital cricket ecosystem where fans can experience the thrill of cricket through cutting-edge technology, interactive features, and meaningful connections.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--// Values Section //-->
    <section class="values-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Core <span class="highlight">Values</span></h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="value-content">
                            <h4>Passion for Cricket</h4>
                            <p>We live and breathe cricket. Our love for the game drives everything we do, from feature development to community engagement.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="value-content">
                            <h4>Innovation</h4>
                            <p>We constantly push boundaries, embracing new technologies and ideas to deliver cutting-edge cricket experiences.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="value-content">
                            <h4>Community First</h4>
                            <p>Our users are at the heart of everything. We build features based on your needs and feedback.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="value-content">
                            <h4>Integrity</h4>
                            <p>We maintain the highest standards of fairness, transparency, and ethical conduct in all our operations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--// CTA Section //-->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Join the CrickFolio Family?</h2>
            <p>Experience cricket like never before. Create your account today and become part of our growing community of passionate cricket fans.</p>
            <a href="signup.php" class="cta-button">Get Started Now</a>
        </div>
    </section>

    <!--// Footer //-->
    <?php include 'footer.php'; ?>

    <?php include "scripts.php"; ?>

    <script>
        // Smooth scroll for back to top
        $(document).ready(function() {
            $('.ritekhela-back-top').click(function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 800);
            });

            // Counter animation on scroll
            var counted = false;
            $(window).scroll(function() {
                var statsTop = $('.about-stats').offset().top;
                var scrollTop = $(window).scrollTop();
                var windowHeight = $(window).height();

                if (scrollTop > statsTop - windowHeight + 200 && !counted) {
                    $('.stat-number').each(function() {
                        var $this = $(this);
                        var countTo = $this.text().replace(/[^0-9]/g, '');
                        var suffix = $this.text().replace(/[0-9]/g, '');

                        $({
                            countNum: 0
                        }).animate({
                            countNum: countTo
                        }, {
                            duration: 2000,
                            easing: 'linear',
                            step: function() {
                                $this.text(Math.floor(this.countNum) + suffix);
                            },
                            complete: function() {
                                $this.text(this.countNum + suffix);
                            }
                        });
                    });
                    counted = true;
                }
            });
        });
    </script>
</body>

</html>