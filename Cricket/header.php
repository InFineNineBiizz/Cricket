<?php     
    include "links.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head> 
<div class="ritekhela-wrapper">
    
    <!--// Header //-->
    <header id="ritekhela-header" class="ritekhela-header-one">
    
        <!--// Main Header //-->
        <div class="ritekhela-main-header">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <a href="index.php" class="ritekhela-logo"><img src="assets/images/Crickfolio.png" alt="Logo" height="100px" width="200px"></a>
                        <div class="ritekhela-right-section">
                            <div class="ritekhela-navigation">
                                <span class="ritekhela-menu-link">
                                    <span class="menu-bar"></span>
                                    <span class="menu-bar"></span>
                                    <span class="menu-bar"></span>
                                </span>
                                <nav id="main-nav">
                                    <ul id="main-menu" class="sm sm-blue">
                                        <li><a href="portal/index.php">Portal</a></li>
                                        <li><a href="index.php">Home</a></li>
                                        <li><a href="aboutus.php">About Us</a></li>
                                        <li><a href="leaderboard.php">LeaderBoard</a></li>
                                        <li><a href="upauction.php">Auction</a>
                                            <ul>
                                                <li><a href="auction.php">live Auction</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="contact-us.php">Contact Us</a></li>
                                        <?php if(isset($_SESSION['user_email'])): ?>
                                            <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
                                        <?php else: ?>
                                            <li><a href="login.php"><i class="fa fa-user-alt"></i> Login</a></li>
                                            <li><a href="signup.php"><i class="fa fa-sign-in-alt"></i> Signup</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Main Header //-->
    </header>
    <!--// Header //-->
</div>