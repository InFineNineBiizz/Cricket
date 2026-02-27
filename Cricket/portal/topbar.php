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
    <style>
         /* USER DROPDOWN */
        .user-dropdown {
            position: relative;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 10px;
            transition: background 0.3s;
        }

        .user-menu:hover {
            background: #f3f4f6;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #f59e0b;
            color: #fff;
            font-weight: 700;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-info h4 {
            margin: 0;
            font-size: 14px;
        }

        .user-info p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        /* DROPDOWN BOX */
        .user-dropdown-menu {
            position: absolute;
            top: 110%;
            right: 0;
            width: 180px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            display: none;
            z-index: 999;
        }

        .user-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            text-decoration: none;
            font-size: 14px;
            color: #374151;
            transition: background 0.2s;
        }

        .user-dropdown-menu a:hover {
            background: #f9fafb;
        }

        .user-dropdown-menu .logout {
            color: #dc2626;
        }

        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 5px 0;
        }

        /* SHOW DROPDOWN */
        .user-dropdown-menu.show {
            display: block;
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="../index.php" class="ritekhela-logo"><img src="../assets/images/Crickfolio1.png" alt="Logo" height="60px" width="200px"></a>            
        
        <div class="user-dropdown">
            <div class="user-menu" id="userMenu">
                <div class="user-avatar"><?php echo $user_initials; ?></div>
                <div class="user-info">
                    <h4>
                        <?php 
                            if(isset($_SESSION['user_name'])){
                                echo ucfirst($_SESSION['user_name']);
                            }
                        ?>
                    </h4>
                    <p><?php if($_SESSION['user_role'] == "Admin"){ echo 'Admin';}else{ echo 'User';}?></p>
                </div>
                <i class="fas fa-chevron-down"></i>
            </div>

            <!-- Dropdown -->
            <div class="user-dropdown-menu" id="userDropdown">
                <a href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <script>
        const userMenu = document.getElementById('userMenu');
        const dropdown = document.getElementById('userDropdown');

        userMenu.addEventListener('click', () => {
            dropdown.classList.toggle('show');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>