<?php 
    session_start();
?>
<html>
<head>
    <title>Upcoming Auctions | CrickFolio</title>
    <link rel="stylesheet" href="assets/css/upauction.css">
</head>
<?php 
    include 'header.php'; 
?>
<body>
    <!-- SubHeader -->
    <div class="ritekhela-subheader">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Cricket Auctions</h1>
                    <ul class="ritekhela-breadcrumb">
                        <li><a href="index.php">Home</a></li>
                        <li>Auctions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ritekhela-main-content" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #f1f5f9 100%); position: relative;">        
        <div class="ritekhela-main-section" style="position: relative; z-index: 1;">
            <div class="container">
                
                <!-- Upcoming Auctions Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="ritekhela-fancy-title" style="margin-bottom: 50px; text-align: center;">
                            <div class="ritekhela-fancy-title-inner" style="border-color: #fbbf24;">
                                <h2 style="color: #f59e0b; font-size: 40px; font-weight: bold; text-transform: uppercase;">Upcoming Auctions</h2>
                                <span style="color: #64748b;">🌟 Don't miss these exciting cricket auctions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="upcomingGrid">
                    <!-- Upcoming auctions will be loaded here -->
                </div>

                <div class="row" id="upcomingViewMore" style="display: none; margin-top: 20px; margin-bottom: 80px;">
                    <div class="col-md-12" style="text-align: center;">
                        <a href="javascript:void(0);" onclick="loadMoreUpcoming()" class="view-more-btn">
                            Load More Auctions
                        </a>
                    </div>
                </div>

                <!-- Completed Auctions Section -->
                <div class="row" style="margin-top: 80px;">
                    <div class="col-md-12">
                        <div class="ritekhela-fancy-title" style="margin-bottom: 50px; text-align: center;">
                            <div class="ritekhela-fancy-title-inner" style="border-color: #22c55e;">
                                <h2 style="color: #16a34a; font-size: 40px; font-weight: bold; text-transform: uppercase;">Completed Auctions</h2>
                                <span style="color: #64748b;">✅ Browse past auction results</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="completedGrid">
                    <!-- Completed auctions will be loaded here -->
                </div>

                <div class="row" id="completedViewMore" style="display: none; margin-top: 20px; margin-bottom: 50px;">
                    <div class="col-md-12" style="text-align: center;">
                        <a href="javascript:void(0);" onclick="loadMoreCompleted()" class="view-more-btn completed-btn">
                            Load More Results
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        // Auction data
        const upcomingAuctions = [
            {
                league: "WPL",
                title: "Wonder Cement Premier League",
                location: "Wonder House, Udaipur",
                date: "29 Nov 2025",
                time: "2:00 PM"
            },
            {
                league: "SBSPL",
                title: "Samasta Bhavsar Samaj Premier",
                location: "Navsari",
                date: "30 Nov 2025",
                time: "5:00 PM"
            },
            {
                league: "TCL",
                title: "Travel Cricket League",
                location: "To Be Decided",
                date: "30 Nov 2025",
                time: "5:00 PM"
            },
            {
                league: "MCT",
                title: "Muslim Champion Trophy",
                location: "To Be Decided",
                date: "30 Nov 2025",
                time: "7:00 PM"
            },
            {
                league: "VPL",
                title: "VPL 2026 Season 3",
                location: "The Farm House",
                date: "21 Dec 2025",
                time: "3:30 PM"
            }
        ];

        const completedAuctions = [
            { league: "IPL", title: "IPL Mega Auction 2024", location: "Dubai", date: "19 Dec 2023", time: "3:00 PM" },
            { league: "BBL", title: "BBL Auction 2024", location: "Melbourne", date: "15 Aug 2024", time: "10:00 AM" },
            { league: "PSL", title: "PSL Draft 2024", location: "Karachi", date: "15 Nov 2024", time: "4:00 PM" },
            { league: "CPL", title: "CPL Auction 2024", location: "Port of Spain", date: "20 Jul 2024", time: "6:00 PM" },
            { league: "BPL", title: "BPL Auction 2024", location: "Dhaka", date: "10 Oct 2024", time: "2:00 PM" },
            { league: "LPL", title: "Lanka Premier League Auction", location: "Colombo", date: "5 Sep 2024", time: "11:00 AM" },
            { league: "SA20", title: "SA20 Auction 2024", location: "Cape Town", date: "25 Jun 2024", time: "5:00 PM" },
            { league: "ILT20", title: "ILT20 Auction 2024", location: "Dubai", date: "12 May 2024", time: "6:00 PM" },
            { league: "MLC", title: "Major League Cricket Auction", location: "New York", date: "18 Apr 2024", time: "7:00 PM" },
            { league: "T20", title: "T20 Blast Auction 2024", location: "London", date: "22 Mar 2024", time: "3:00 PM" },
            { league: "MSL", title: "Mzansi Super League Draft", location: "Johannesburg", date: "8 Feb 2024", time: "4:00 PM" },
            { league: "BBL", title: "Big Bash League Draft 2023", location: "Sydney", date: "30 Dec 2023", time: "12:00 PM" }
        ];

        let upcomingDisplayed = 0;
        let completedDisplayed = 0;
        const upcomingPerLoad = 3;
        const completedPerLoad = 6;

        function createAuctionCard(auction, isCompleted = false) {
            const statusClass = isCompleted ? 'completed' : 'upcoming';
            const cardClass = isCompleted ? 'completed-card' : '';
            const statusText = isCompleted ? 'COMPLETED' : 'UPCOMING';
            
            return `
                <div class="col-md-4">
                    <div class="auction-card-container ${cardClass}">
                        <div class="card-header-section">
                            <span class="status-badge ${statusClass}">${statusText}</span>
                            <div class="league-badge">${auction.league}</div>
                            <h2 class="auction-title">${auction.title}</h2>
                        </div>
                        <div class="card-body-section">
                            <div class="detail-item">
                                <span class="detail-icon">📍</span>
                                <span class="detail-label">Location:</span>
                                <span class="detail-value">${auction.location}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">📅</span>
                                <span class="detail-label">Date:</span>
                                <span class="detail-value">${auction.date}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">⏰</span>
                                <span class="detail-label">Time:</span>
                                <span class="detail-value">${auction.time}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function loadMoreUpcoming() {
            const grid = document.getElementById('upcomingGrid');
            const remaining = upcomingAuctions.length - upcomingDisplayed;
            const toLoad = Math.min(remaining, upcomingPerLoad);
            
            for (let i = 0; i < toLoad; i++) {
                grid.innerHTML += createAuctionCard(upcomingAuctions[upcomingDisplayed + i]);
            }
            
            upcomingDisplayed += toLoad;
            
            if (upcomingDisplayed >= upcomingAuctions.length) {
                document.getElementById('upcomingViewMore').style.display = 'none';
            }
        }

        function loadMoreCompleted() {
            const grid = document.getElementById('completedGrid');
            const remaining = completedAuctions.length - completedDisplayed;
            const toLoad = Math.min(remaining, completedPerLoad);
            
            for (let i = 0; i < toLoad; i++) {
                grid.innerHTML += createAuctionCard(completedAuctions[completedDisplayed + i], true);
            }
            
            completedDisplayed += toLoad;
            
            if (completedDisplayed >= completedAuctions.length) {
                document.getElementById('completedViewMore').style.display = 'none';
            }
        }

        // Initial load
        window.onload = function() {
            loadMoreUpcoming();
            if (upcomingDisplayed < upcomingAuctions.length) {
                document.getElementById('upcomingViewMore').style.display = 'block';
            }
            
            loadMoreCompleted();
            if (completedDisplayed < completedAuctions.length) {
                document.getElementById('completedViewMore').style.display = 'block';
            }
        };
    </script>
    <?php 
        include "scripts.php";
    ?>
</body>
</html>