<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CrickFolio Portal</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <link rel="stylesheet" href="../assets/css/home-style.css">
</head>
<body>
    <!-- Top Navigation -->
    <?php
        include 'topbar.php';
    ?>

    <!-- Sidebar -->
    <?php
        include 'sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="main-wrapper">
        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-content">
                <h1 class="welcome-title">Welcome back, Jitu! 👋</h1>
                <p class="welcome-subtitle">Here's what's happening with your auctions today</p>
                <div class="quick-actions">
                    <button class="action-btn action-btn-primary">
                        <i class="fas fa-plus"></i> Create New Auction
                    </button>
                    <button class="action-btn action-btn-secondary">
                        <i class="fas fa-chart-line"></i> View Analytics
                    </button>
                </div>
            </div>
        </section>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-gavel stat-icon"></i>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> 12%
                    </div>
                </div>
                <div class="stat-content">
                    <h4>Total Auctions</h4>
                    <div class="stat-value">24</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-trophy stat-icon"></i>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> 8%
                    </div>
                </div>
                <div class="stat-content">
                    <h4>Active Tournaments</h4>
                    <div class="stat-value">8</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> 24%
                    </div>
                </div>
                <div class="stat-content">
                    <h4>Registered Players</h4>
                    <div class="stat-value">432</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar stat-icon"></i>
                    </div>
                    <div class="stat-trend down">
                        <i class="fas fa-arrow-down"></i> 3%
                    </div>
                </div>
                <div class="stat-content">
                    <h4>Upcoming Matches</h4>
                    <div class="stat-value">15</div>
                </div>
            </div>
        </div>

        <!-- Auctions Section -->
        <div class="section-header">
            <div class="section-title-group">
                <h2>Your Active Auctions</h2>
                <p>Manage and monitor your ongoing cricket auctions</p>
            </div>
            <a href="#" class="view-all-btn">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="auction-grid">
            <!-- Auction Card 1 -->
            <div class="auction-card">
                <div class="auction-banner">
                    <div class="auction-badge">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <div class="auction-body">
                    <div class="auction-header-text">
                        <h3 class="auction-title">Dream Classes Premier League</h3>
                        <span class="auction-league">
                            <i class="fas fa-star"></i> Dream Class
                        </span>
                    </div>
                    <div class="auction-meta">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>raj corner</span>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <span>17/11/2025 at 10:40 AM</span>
                        </div>
                    </div>
                    <div class="auction-footer">
                        <button class="manage-btn">
                            <i class="fas fa-edit"></i> Manage
                        </button>
                        <button class="details-btn">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auction Card 2 -->
            <div class="auction-card">
                <div class="auction-banner">
                    <div class="auction-badge">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="auction-body">
                    <div class="auction-header-text">
                        <h3 class="auction-title">Champions Trophy 2025</h3>
                        <span class="auction-league">
                            <i class="fas fa-star"></i> Elite League
                        </span>
                    </div>
                    <div class="auction-meta">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>Mumbai Cricket Ground</span>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <span>25/11/2025 at 02:00 PM</span>
                        </div>
                    </div>
                    <div class="auction-footer">
                        <button class="manage-btn">
                            <i class="fas fa-edit"></i> Manage
                        </button>
                        <button class="details-btn">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auction Card 3 -->
            <div class="auction-card">
                <div class="auction-banner">
                    <div class="auction-badge">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
                <div class="auction-body">
                    <div class="auction-header-text">
                        <h3 class="auction-title">Super League Season 5</h3>
                        <span class="auction-league">
                            <i class="fas fa-star"></i> Super Stars
                        </span>
                    </div>
                    <div class="auction-meta">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>Delhi Sports Complex</span>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <span>30/11/2025 at 11:00 AM</span>
                        </div>
                    </div>
                    <div class="auction-footer">
                        <button class="manage-btn">
                            <i class="fas fa-edit"></i> Manage
                        </button>
                        <button class="details-btn">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auction Card 4 -->
            <div class="auction-card">
                <div class="auction-banner">
                    <div class="auction-badge">
                        <i class="fas fa-medal"></i>
                    </div>
                </div>
                <div class="auction-body">
                    <div class="auction-header-text">
                        <h3 class="auction-title">City Cricket Championship</h3>
                        <span class="auction-league">
                            <i class="fas fa-star"></i> City League
                        </span>
                    </div>
                    <div class="auction-meta">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>Bangalore Stadium</span>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <span>05/12/2025 at 03:30 PM</span>
                        </div>
                    </div>
                    <div class="auction-footer">
                        <button class="manage-btn">
                            <i class="fas fa-edit"></i> Manage
                        </button>
                        <button class="details-btn">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>    
</body>
</html>