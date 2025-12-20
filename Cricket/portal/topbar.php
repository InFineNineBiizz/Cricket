<?php     
    include "connection.php";
    
    // Get user initials
    $user_initials = 'U'; // Default
    if(isset($_SESSION['user_name'])) {
        $name = $_SESSION['user_name'];
        $words = explode(' ', trim($name));
        
        if(count($words) >= 2) {
            // If full name (First Last), get first letter of each
            $user_initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            // If single name, get first letter only
            $user_initials = strtoupper(substr($name, 0, 1));
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/home-style.css">
</head>
<body>
    <nav class="top-nav">
       <a href="../index.php" class="brand">
            <div class="brand-logo">
                <i class="fas fa-cricket"></i>
            </div>
            <div class="brand-text">
                <h1>CRICKFOLIO</h1>
                <p>Cricket Portal</p>
            </div>
        </a>
        <div class="top-nav-right">            
            <div class="user-menu">
                <div class="user-avatar"><?php echo $user_initials; ?></div>
                <div class="user-info">
                    <h4><?php if(isset($_SESSION['user_name'])){ echo ucfirst($_SESSION['user_name']);}?></h4>
                    <p>Administrator</p>
                </div>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </nav>
</body>
</html>