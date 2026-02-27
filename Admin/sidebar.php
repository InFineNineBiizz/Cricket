<!-- Sidenav Menu Start -->
<div class="sidenav-menu">
    <!-- Brand Logo -->
    <a href="index.php" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="assets/images/logo.png" alt="logo"></span>
            <span class="logo-sm"><img src="assets/images/logo-sm.png" alt="small logo"></span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg"><img src="assets/images/logo-dark.png" alt="dark logo"></span>
            <span class="logo-sm"><img src="assets/images/logo-sm.png" alt="small logo"></span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-sm-hover">
        <i class="ri-circle-line align-middle"></i>
    </button>

    <!-- Sidebar Menu Toggle Button -->
    <button class="sidenav-toggle-button">
        <i class="ri-menu-5-line fs-24"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-fullsidebar">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div data-simplebar>
        <!-- User -->
        <div class="sidenav-user">
            <div class="dropdown-center text-center">
                <a class="topbar-link dropdown-toggle text-reset drop-arrow-none px-2" data-bs-toggle="dropdown"
                    type="button" aria-haspopup="false" aria-expanded="false">
                    <img src="assets/images/users/avatar-1.jpg" width="46" class="rounded-circle" alt="user-image">
                    <span class="d-flex justify-content-center gap-1 sidenav-user-name my-2">
                        <span>
                            <h6><?php echo isset($_SESSION['name']) ? ucfirst($_SESSION['name']) : 'Guest'; ?></h6>
                            <p class="my-0 fs-13 text-muted">Admin</p>
                        </span>
                        <i class="ri-arrow-down-s-line d-block sidenav-user-arrow align-middle"></i>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome !</h6>
                    </div>

                    <!-- item-->
                    <a href="" class="dropdown-item">
                        <i class="ri-account-circle-line me-1 fs-16 align-middle"></i>
                        <span class="align-middle">My Account</span>
                    </a>

                    <div class="dropdown-divider"></div>

                    <!-- item-->
                    <a href="auth-logout.php" class="dropdown-item active fw-semibold text-danger">
                        <i class="ri-logout-box-line me-1 fs-16 align-middle"></i>
                        <span class="align-middle">Sign Out</span>
                    </a>
                </div>
            </div>
        </div>

        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <li class="side-nav-item">
                <a href="index.php" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>                
            </li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPagesTour" aria-expanded="false"
                    aria-controls="sidebarPagesTour" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-trophy"></i></span>
                    <span class="menu-text"> Tounaments </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPagesTour">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="manage_tournaments.php" class="side-nav-link">
                                <span class="menu-text">Manage Tournaments</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPagesSeason" aria-expanded="false"
                    aria-controls="sidebarPagesSeason" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-cricket"></i></span>
                    <span class="menu-text"> Seasons </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPagesSeason">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="manage_seasons.php" class="side-nav-link">
                                <span class="menu-text">Manage Seasons</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_organizer.php" class="side-nav-link">
                                <span class="menu-text">Manage Organizer</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_sponsor.php" class="side-nav-link">
                                <span class="menu-text">Manage Sponsor</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_season_organizer.php" class="side-nav-link">
                                <span class="menu-text">Manage Season Organizer</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_season_sponsor.php" class="side-nav-link">
                                <span class="menu-text">Manage Season Sponsor</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_season_players.php" class="side-nav-link">
                                <span class="menu-text">Manage Season Players</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPagesAuc" aria-expanded="false"
                    aria-controls="sidebarPagesAuc" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-cricket"></i></span>
                    <span class="menu-text"> Auctions </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPagesAuc">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="manage_auctions.php" class="side-nav-link">
                                <span class="menu-text">Manage Auctions</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="auction_manager.php" class="side-nav-link">
                                <span class="menu-text">Auction Manager</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="lead_auctioner.php" class="side-nav-link">
                                <span class="menu-text">Lead Auctioner</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_grp_auc.php" class="side-nav-link">
                                <span class="menu-text">Group Wise Auction</span>
                            </a>
                        </li>                                                
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPagesTeam" aria-expanded="false"
                    aria-controls="sidebarPagesTeam" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-trophy"></i></span>
                    <span class="menu-text"> Teams </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPagesTeam">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="manage_team.php" class="side-nav-link">
                                <span class="menu-text">Manage Teams</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_players.php" class="side-nav-link">
                                <span class="menu-text">Manage Players</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="manage_team_players.php" class="side-nav-link">
                                <span class="menu-text">Manage Sold Players</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="side-nav-title mt-2">Custom</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPagesAuth" aria-expanded="false"
                    aria-controls="sidebarPagesAuth" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-user-shield"></i></span>
                    <span class="menu-text"> Authentication </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPagesAuth">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="auth-login.php" class="side-nav-link">
                                <span class="menu-text">Login</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="auth-register.php" class="side-nav-link">
                                <span class="menu-text">Register</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="auth-logout.php" class="side-nav-link">
                                <span class="menu-text">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>         
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<!-- Sidenav Menu End -->