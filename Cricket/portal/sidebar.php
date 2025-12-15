<html>
    <link rel="stylesheet" href="../assets/css/home-style.css">
    <style>
        /* Active state styling */
        .nav-link.active {
            background-color: #f0f0f0;
            border-left: 4px solid #ff8c42;
            font-weight: 600;
            color: #ff8c42;
        }
        
        .nav-link.active .nav-icon {
            color: #ff8c42;
        }
        
        .nav-link {
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background-color: #f5f5f5;
        }
    </style>
    
    <aside class="sidebar">
        <div class="sidebar-section">
            <h3 class="sidebar-title">Main Menu</h3>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link" data-page="index.php">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tournament.php" class="nav-link" data-page="tournament.php">
                        <i class="fas fa-trophy nav-icon"></i>
                        <span>Tournaments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sea-auction.php" class="nav-link" data-page="sea-auction.php">
                        <i class="fas fa-gavel nav-icon"></i>
                        <span>Auctions & Seasons</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="players.php" class="nav-link" data-page="players.php">
                        <i class="fas fa-users nav-icon"></i>
                        <span>Players</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="schedule.php" class="nav-link" data-page="schedule.php">
                        <i class="fas fa-calendar nav-icon"></i>
                        <span>Schedule</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-section">
            <h3 class="sidebar-title">Settings</h3>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="settings.php" class="nav-link" data-page="settings.php">
                        <i class="fas fa-cog nav-icon"></i>
                        <span>Profile & Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="support.php" class="nav-link" data-page="support.php">
                        <i class="fas fa-question-circle nav-icon"></i>
                        <span>Help & Support</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link" data-page="logout.php">
                        <i class="fas fa-sign-out-alt nav-icon"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    
    <script>
        // Get current page filename
        const currentPage = window.location.pathname.split('/').pop();
        
        // Get all nav links
        const navLinks = document.querySelectorAll('.nav-link');
        
        // Add active class to current page link
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('data-page');
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
            
            // Optional: Add click event to store active state
            link.addEventListener('click', function() {
                // Remove active class from all links
                navLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked link
                this.classList.add('active');
            });
        });
    </script>
</html>