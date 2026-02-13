<?php 
    session_start();
?>
<html>
<head>
    <title>Contact Us | CrickFolio</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
    <?php
        include 'header.php';
    ?>    
<body>    
    <!--// SubHeader //-->
    <div class="ritekhela-subheader">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Contact Us</h1>
                    <ul class="ritekhela-breadcrumb">
                        <li><a href="index.php">Home</a></li>
                        <li>Contact Us</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--// SubHeader //-->

    <!--// Content //-->
    <div class="ritekhela-main-content">

        <!--// Main Section //-->
        <div class="ritekhela-main-section ritekhela-contact-map-full">
            <div class="container-fluid">
                <div class="row">
                    
                    <!--// Full Section //-->
                    <div class="ritekhela-contact-map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d119065.02905823676!2d72.73989544621746!3d21.159464837393114!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be04e59411d1563%3A0xfe4558290938b042!2sSurat%2C%20Gujarat!5e0!3m2!1sen!2sin!4v1234567890123!5m2!1sen!2sin" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <!--// Full Section //-->

                </div>
            </div>
        </div>
        <!--// Main Section //-->

        <!--// Main Section //-->
        <div class="ritekhela-main-section ritekhela-fixture-list-full">
            <div class="container">
                <div class="row">                    
                    <!--// Full Section //-->
                    <div class="col-md-12">
                        <div class="ritekhela-fancy-title-two">
                            <h2>Contact Information</h2>
                        </div>
                        <div class="ritekhela-contact-list">
                            <ul class="row">
                                <li class="col-md-4">
                                    <i class="fa fa-phone"></i>
                                    <span style="font-weight: bold; font-size: x-large;">Call Us</span>
                                    <a href="tel:+917862855031">+91 78628 55031</a>
                                    <a href="tel:+917862855031">+91 78628 55031</a>
                                </li>
                                <li class="col-md-4">
                                    <i class="fas fa-envelope"></i>
                                    <span style="font-weight: bold; font-size: x-large;">Email</span>
                                    <a href="mailto:crickfolio31@gmail.com">crickfolio31@gmail.com</a>                                    
                                </li>
                                <li class="col-md-4">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <span style="font-weight: bold; font-size: x-large;">Location</span>
                                    <span></span>
                                </li>                                
                            </ul>
                        </div>
                        <!--// Fancy Title //-->
                        <div class="ritekhela-fancy-title-two">
                            <h2>Contact Us</h2>
                        </div>
                        <!--// Fancy Title //-->
                        <div class="ritekhela-form">
                            <form>
                                <p>
                                    <input type="text" placeholder="Your Name *" onblur="if(this.value == '') { this.value ='Your Name'; }" onfocus="if(this.value =='Your Name') { this.value = ''; }" name="usrname" required=""> </p>
                                <p>
                                    <input type="text" placeholder="Your Email *" onblur="if(this.value == '') { this.value ='Email'; }" onfocus="if(this.value =='Email') { this.value = ''; }" name="usrname" required=""> </p>
                                <p>
                                    <input type="text" placeholder="Your Phone Number *" onblur="if(this.value == '') { this.value ='Website'; }" onfocus="if(this.value =='Website') { this.value = ''; }" name="usrname" required=""> </p>
                                <p class="ritekhela-comment">
                                    <textarea placeholder="Enquiry *"></textarea>
                                </p>
                                <p class="ritekhela-submit">
                                    <input type="submit" value="Send Now" class="ritekhela-bgcolor"> </p>
                            </form>
                        </div>
                        <!--// Fancy Title //-->
                        <!--// Full Section //-->
                </div>
            </div>
        </div>
        <!--// Main Section //-->
    </div>
    <!--// Content //-->

    <?php
        include 'footer.php';
    ?>    

    <?php 
        include "scripts.php";
    ?>

    <script>
        // Email link functionality - Open Gmail compose in new tab
        document.addEventListener('DOMContentLoaded', function() {
            // Get all email links
            const emailLinks = document.querySelectorAll('a[href^="mailto:"]');
            
            emailLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default mailto behavior
                    
                    // Extract email from href
                    const email = this.getAttribute('href').replace('mailto:', '');
                    
                    // Open Gmail compose in new tab
                    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}`;
                    window.open(gmailUrl, '_blank');
                    
                    // Also show a fallback message
                    console.log('Opening Gmail for: ' + email);
                });
            });
        });
    </script>
</body>
</html>