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
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13606.8196963411!2d74.3235584!3d31.504793599999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m3!3e6!4m0!4m0!5e0!3m2!1sen!2s!4v1564568940172!5m2!1sen!2s" height="450"></iframe>
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
                                <li class="col-md-3">
                                    <i class="fa fa-phone"></i>
                                    <span></span>
                                    <span></span>
                                </li>
                                <li class="col-md-3">
                                    <i class="fas fa-envelope"></i>
                                    <a href="#"></a>
                                    <a href="#"></a>
                                </li>
                                <li class="col-md-3">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <span></span>
                                </li>
                                <li class="col-md-3">
                                    <i class="fa fa-fax"></i>
                                    <a href="#"></a>
                                    <a href="#"></a>
                                </li>
                            </ul>
                        </div>
                        <!--// Fancy Title //-->
                        <div class="ritekhela-fancy-title-two">
                            <h2>Contact Here</h2>
                        </div>
                        <!--// Fancy Title //-->
                        <div class="ritekhela-form">
                            <form>
                                <p>
                                    <input type="text" value="Your Name" onblur="if(this.value == '') { this.value ='Your Name'; }" onfocus="if(this.value =='Your Name') { this.value = ''; }" name="usrname" required=""> </p>
                                <p>
                                    <input type="text" value="Email" onblur="if(this.value == '') { this.value ='Email'; }" onfocus="if(this.value =='Email') { this.value = ''; }" name="usrname" required=""> </p>
                                <p>
                                    <input type="text" value="Website" onblur="if(this.value == '') { this.value ='Website'; }" onfocus="if(this.value =='Website') { this.value = ''; }" name="usrname" required=""> </p>
                                <p class="ritekhela-comment">
                                    <textarea placeholder="Comment"></textarea>
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

    <!--// Search ModalBox //-->
    <div class="loginmodalbox modal fade" id="ritekhelamodalsearch" tabindex="-1">
       <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
             <div class="modal-body ritekhela-bgcolor-two">
                <h5 class="modal-title">Search Here</h5>
                <a href="#" class="close ritekhela-bgcolor-two" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </a>
                <form>
                    <input type="text" value="Search Here Now" onblur="if(this.value == '') { this.value ='Search Here Now'; }" onfocus="if(this.value =='Search Here Now') { this.value = ''; }">
                    <input type="submit" value="Search Now" class="ritekhela-bgcolor">
                </form>
             </div>
          </div>
       </div>
    </div>

    <?php 
        include "scripts.php";
    ?>
</body>
</html>