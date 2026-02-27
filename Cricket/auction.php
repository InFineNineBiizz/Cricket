<?php
    include "connection.php";
    
    // ================== AUCTIONS & GROUP AUCTIONS ==================
    $auction_sql_base = "SELECT * FROM auctions WHERE status = 1 ORDER BY sdate DESC";
    $auction_res      = mysqli_query($conn, $auction_sql_base);
    $activeAuctionRes = mysqli_query($conn, $auction_sql_base . " LIMIT 1");
    $activeAuctionRow = $activeAuctionRes ? mysqli_fetch_assoc($activeAuctionRes) : null;

    $group_sql = "SELECT * FROM group_auction WHERE status = 1 ORDER BY created_at DESC";
    $group_res = mysqli_query($conn, $group_sql);

    // Build auction settings by season
    $auctionSettings = [];
    $auction_res_all = mysqli_query($conn, $auction_sql_base);
    while ($auc = mysqli_fetch_assoc($auction_res_all)) {
        $auctionSettings[(int)$auc['sea_id']] = [
            'basePrice' => (int)$auc['bprice'] ?: 500,
            'bidStep' => (int)$auc['bidamt'] ?: 500,
            'maxPlayers' => (int)$auc['maxplayer'] ?: 0,
            'budget' => (int)$auc['camt'] ?: 0
        ];
    }

    // Defaults from active auction (if present)
    $defaultBasePrice       = $activeAuctionRow ? ((int)$activeAuctionRow['bprice'] ?: 500) : 500;
    $defaultBidStepSmall    = $activeAuctionRow ? ((int)$activeAuctionRow['bidamt'] ?: 500) : 500;
    $maxPlayersToPurchase   = $activeAuctionRow ? (int)$activeAuctionRow['maxplayer'] : 0;
    $defaultActiveSeasonId  = $activeAuctionRow ? (int)$activeAuctionRow['sea_id']   : 0;

    // ================== SEASONS ==================
    $seasonRows = [];
    $seasons_res = mysqli_query($conn, "SELECT s.*, t.name as tournament_name FROM seasons s LEFT JOIN tournaments t ON s.tid = t.tid WHERE s.status = 1 ORDER BY s.sdate DESC");
    if ($seasons_res && mysqli_num_rows($seasons_res) > 0) {
        while ($s = mysqli_fetch_assoc($seasons_res)) {
            $seasonRows[] = [
                'id'              => (int)$s['id'],
                'tid'             => (int)$s['tid'],
                'name'            => $s['name'],
                'tournament_name' => $s['tournament_name'],
                'logo'            => $s['logo'],
            ];
        }
    }

    // ================== TEAMS ==================
    $colorPalette = [
        '#004BA0','#FDB913','#EC1C24','#3A225D','#E91E63','#9C27B0',
        '#FF5722','#795548','#009688','#00A7E1','#78BE20','#D32F2F',
        '#FFD700','#FFB300','#1ED760','#1E8449','#FF6600','#00A8E1','#0047AB'
    ];
    $colorIndex = 0;

    // Build a lookup of auction budgets by season (sea_id -> camt)
    $auctionBudgets = [];
    $resAuction = mysqli_query($conn, "SELECT sea_id, camt FROM auctions WHERE status = 1");
    while ($a = mysqli_fetch_assoc($resAuction)) {
        $auctionBudgets[(int)$a['sea_id']] = (int)$a['camt'];
    }

    $teams_res = mysqli_query($conn, "SELECT * FROM teams WHERE status = 1 ORDER BY name");
    $teamsBySeason = [];

    while ($row = mysqli_fetch_assoc($teams_res)) {
        $seasonId = (int)$row['season_id'];
        $budget = $auctionBudgets[$seasonId] ?? 0;
        $color = $colorPalette[$colorIndex % count($colorPalette)];
        $colorIndex++;

        // Get remaining directly from teams table
        $remaining = (int)$row['remaining'];
        
        // If remaining is 0 or empty, set it to full budget
        if ($remaining <= 0) {
            $remaining = $budget;
        }

        $teamsBySeason[$seasonId][] = [
            'id'        => (int)$row['id'],
            'season_id' => $seasonId,
            'name'      => $row['name'],
            'logo'      => $row['logo'],
            'budget'    => $budget,
            'remaining' => $remaining,
            'spent'     => $budget - $remaining,
            'color'     => $color,
            'players'   => []
        ];
    }

    // ================== PLAYERS (from DB) - Filter by Season ==================
    $playersForJs = [];
    
    // Get all players that belong to seasons
    $players_sql = "SELECT p.*, sp.season_id 
                    FROM players p
                    INNER JOIN season_players sp ON p.id = sp.player_id
                    WHERE p.status = 1 
                    ORDER BY p.id ASC";
    $players_res = mysqli_query($conn, $players_sql);

    if ($players_res && mysqli_num_rows($players_res) > 0) {
        while ($row = mysqli_fetch_assoc($players_res)) {
            $roleStr = strtolower($row['role']);
            if (strpos($roleStr, 'all') !== false) {
                $category = 'All-rounder';
            } elseif (strpos($roleStr, 'bat') !== false) {
                $category = 'Batsman';
            } elseif (strpos($roleStr, 'bowl') !== false) {
                $category = 'Bowlers';
            } else {
                $category = 'Batsman';
            }

            $playersForJs[] = [
                'id'        => (int)$row['id'],
                'season_id' => (int)$row['season_id'],
                'name'      => trim($row['fname'] . ' ' . $row['lname']),
                'category'  => $category,
                'team'      => $row['tname'],
                'logo'      => $row['logo'],
            ];
        }
    }

    // ================== LOAD ALREADY SOLD PLAYERS ==================
    $soldPlayersFromDB = [];
    $sold_query = "SELECT tp.*, p.fname, p.lname, p.role, p.tname as player_team, p.logo, 
                t.name as team_name, t.season_id, t.id as team_id
                FROM team_player tp
                LEFT JOIN players p ON tp.pid = p.id
                LEFT JOIN teams t ON tp.tid = t.id
                WHERE p.status = 1 AND t.status = 1";
    $sold_result = mysqli_query($conn, $sold_query);

    if ($sold_result && mysqli_num_rows($sold_result) > 0) {
        while ($row = mysqli_fetch_assoc($sold_result)) {
            $roleStr = strtolower($row['role']);
            if (strpos($roleStr, 'all') !== false) {
                $category = 'All-rounder';
            } elseif (strpos($roleStr, 'bat') !== false) {
                $category = 'Batsman';
            } elseif (strpos($roleStr, 'bowl') !== false) {
                $category = 'Bowlers';
            } else {
                $category = 'Batsman';
            }

            $soldPlayersFromDB[] = [
                'player_id' => (int)$row['pid'],
                'team_id' => (int)$row['team_id'],
                'season_id' => (int)$row['season_id'],
                'sold_price' => (int)$row['sold_price'],
                'player_name' => trim($row['fname'] . ' ' . $row['lname']),
                'team_name' => $row['team_name'],
                'category' => $category,
                'player_team' => $row['player_team'],
                'logo' => $row['logo']
            ];
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Season Auction | <?php echo $title_name;?></title>
    
    <!-- Bootstrap Css -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">        
    
    <!-- SweetAlert Js -->
    <script src="assets/script/sweetalert2.js"></script>
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    
    <!-- Custom Css -->
    <link href="assets/css/auctionstyle.css" rel="stylesheet">
</head>
<body>
    <div class="container-main">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="header-section">                
                <div class="header-text">
                    <a href="index.php" class="ritekhela-logo"><img src="assets/images/Crickfolio.png" alt="Logo" height="70px" width="200px"></a>
                </div>
            </div>

            <div class="filter-section">
                <select id="categoryFilter">
                    <option value="All">All</option>
                    <option value="Batsman">Batsman</option>
                    <option value="Bowlers">Bowlers</option>
                    <option value="All-rounder">All-rounders</option>
                </select>
            </div>

            <div class="player-card" id="currentPlayerCard">
                <div class="player-info">
                    <div class="player-name" id="playerName">Loading...</div>
                    <div class="player-number">
                        <div class="color-dots" id="colorDots">
                            <div class="dot gray"></div>
                        </div>
                    </div>
                    <div class="player-base-price" id="playerBasePrice">Base Price: -</div>
                    <div class="player-current-price" id="playerCurrentPrice">Current Price: -</div>
                </div>
            </div>

            <div class="button-group">
                <button class="btn-custom btn-sold" id="soldBtn">
                    <span>📋</span> SOLD
                </button>
                <button class="btn-custom btn-unsold" id="unsoldBtn">
                    <span>📌</span> UNSOLD
                </button>
            </div>

            <div class="button-group">
                <button class="btn-custom btn-auction">
                    <span>🔨</span> PLAYER AUCTION
                </button>
                <button class="btn-custom btn-skip" id="skipBtn">
                    <span>⏭️</span> SKIP CURRENT PLAYER
                </button>
            </div>

            <div class="button-group">
                <button class="btn-custom btn-next" id="nextPlayerBtn">
                    <span>▶️</span> NEXT PLAYER
                </button>
                <button class="btn-custom btn-select" id="selectSpecificBtn">
                    <span>🔍</span> SELECT SPECIFIC NEXT
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-info" id="teamListBtn">
                    <span>📊</span> TEAM LIST SCREEN
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-owner" id="teamOwnerBtn">
                    <span>👤</span> TEAM OWNER LIST
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-balance" id="teamBalanceBtn">
                    <span>⚖️</span> TEAM BALANCE INFO
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-player-info" id="teamPlayerInfoBtn">
                    <span>ℹ️</span> TEAM PLAYER INFO
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-expensive" id="mostExpensiveBtn">
                    <span>💰</span> MOST EXPENSIVE PLAYER
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-top5" id="top5PlayersBtn">
                    <span>⭐</span> TOP 5 SOLD PLAYER
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-unsold-display" id="unsoldDisplayBtn">
                    <span>❌</span> DISPLAY UNSOLD PLAYER
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-remaining" id="reAuctionBtn">
                    <span>♻️</span> RE-AUCTION UNSOLD
                </button>
            </div>

            <div class="button-group full">
                <button class="btn-custom btn-remaining" id="remainingPlayersBtn">
                    <span>👥</span> REMAINING PLAYERS
                </button>
            </div>

            <!-- Auto Next Configuration -->
            <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-top: 20px;">
                <label style="font-size: 12px; color: #333; margin-bottom: 10px; display: block; font-weight: 600;">Auto Next Player After</label>
                <select id="autoNextSelect" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 12px;">
                    <option value="1">1 Second</option>
                    <option value="2">2 Seconds</option>
                    <option value="3">3 Seconds</option>
                    <option value="4">4 Seconds</option>
                    <option value="5">5 Seconds</option>
                    <option value="6">6 Seconds</option>
                    <option value="7">7 Seconds</option>
                    <option value="8">8 Seconds</option>
                    <option value="9">9 Seconds</option>
                    <option value="10">10 Seconds</option>
                </select>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <!-- Season Selection Header -->
            <div class="tournament-header">
                <div class="tournament-select-container">
                    <label for="seasonSelect">
                        <span>🏆</span> Select Season
                    </label>
                    <select id="seasonSelect" name="season" class="tournament-select">
                        <option value="" selected disabled>-- Select Season --</option>
                        <?php foreach ($seasonRows as $s): ?>
                            <option value="<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['tournament_name'] . ' - ' . $s['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>                                         
                </div>
                
                <div class="teams-bidding-container" id="teamsBiddingContainer">
                    <div class="teams-header">
                        <h5>Teams Bidding</h5>
                    </div>
                    <div class="teams-list" style="width: 100%;" id="teamsList">
                        <div class="no-teams-message">Select a season to view teams</div>
                    </div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-box stat-sold">
                    <div class="stat-label">SOLD</div>
                    <div class="stat-value" id="soldCount">0</div>
                </div>
                <div class="stat-box stat-unsold">
                    <div class="stat-label">UNSOLD</div>
                    <div class="stat-value" id="unsoldCount">0</div>
                </div>
                <div class="stat-box stat-available">
                    <div class="stat-label">AVAILABLE</div>
                    <div class="stat-value" id="availableCount">0</div>
                </div>
                <div class="stat-box stat-total">
                    <div class="stat-label">TOTAL PLAYER</div>
                    <div class="stat-value" id="totalCount">0</div>
                </div>
                <div class="stat-box stat-purchase">
                    <div class="stat-label">TO PURCHASE</div>
                    <div class="stat-value" id="purchaseCount">0</div>
                </div>
            </div>

            <!-- Players Display Area -->
            <div class="players-display" id="playersDisplay"></div>        

            <div class="controls">
                <button class="control-btn" id="minusBtn">−</button>
                <button class="control-btn" id="resetBtn" style="background: #666; width: 70px;">Reset</button>
                <button class="control-btn" id="plusBtn">+</button>
                <button class="control-btn" id="minus100Btn">−</button>
                <input type="text" class="control-input" id="bidInput" value="0" readonly>
                <button class="control-btn" id="plus100Btn">+</button>
            </div>
        </div>
    </div>

    <!-- Select Player Modal -->
    <div id="selectPlayerModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Select Player by ID / Name</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="search-box">
                    <input type="text" id="searchPlayerId" placeholder="Search by Player ID or Name..." class="search-input">
                </div>
                <div class="player-list" id="playerList"></div>
            </div>
        </div>
    </div>

    <!-- Team List Screen Modal -->
    <div id="teamListModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>📊 Team List - <span id="teamListSeason"></span></h3>
                <button class="modal-close" id="closeTeamListModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="teamListContent" class="info-display-grid"></div>
            </div>
        </div>
    </div>

    <!-- Team Owner List Modal -->
    <div id="teamOwnerModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>👤 Team Owner List - <span id="teamOwnerSeason"></span></h3>
                <button class="modal-close" id="closeTeamOwnerModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="teamOwnerContent" class="info-display-list"></div>
            </div>
        </div>
    </div>

    <!-- Team Balance Info Modal -->
    <div id="teamBalanceModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>⚖️ Team Balance Info - <span id="teamBalanceSeason"></span></h3>
                <button class="modal-close" id="closeTeamBalanceModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="teamBalanceContent" class="info-display-grid"></div>
            </div>
        </div>
    </div>

    <!-- Team Player Info Modal -->
    <div id="teamPlayerInfoModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>ℹ️ Team Player Info - <span id="teamPlayerSeason"></span></h3>
                <button class="modal-close" id="closeTeamPlayerModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="teamPlayerContent" class="info-display-grid"></div>
            </div>
        </div>
    </div>

    <!-- Most Expensive Player Modal -->
    <div id="mostExpensiveModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h3>💰 Most Expensive Player - <span id="expensiveSeason"></span></h3>
                <button class="modal-close" id="closeMostExpensiveModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="mostExpensiveContent" class="expensive-player-display"></div>
            </div>
        </div>
    </div>

    <!-- Top 5 Players Modal -->
    <div id="top5Modal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>⭐ Top 5 Sold Players - <span id="top5Season"></span></h3>
                <button class="modal-close" id="closeTop5Modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="top5Content" class="top5-display"></div>
            </div>
        </div>
    </div>

    <!-- Unsold Players Modal -->
    <div id="unsoldPlayersModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>❌ Unsold Players</h3>
                <button class="modal-close" id="closeUnsoldModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="unsoldPlayersContent" class="modal-players-display"></div>
            </div>
        </div>
    </div>

    <!-- Remaining Players Modal -->
    <div id="remainingPlayersModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>👥 Remaining Players (Unsold + Skipped + Not Auctioned)</h3>
                <button class="modal-close" id="closeRemainingModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="remaining-stats">
                    <div class="remaining-stat-box">
                        <div class="remaining-stat-label">Total Unsold</div>
                        <div class="remaining-stat-value" id="remainingUnsoldCount">0</div>
                    </div>
                    <div class="remaining-stat-box">
                        <div class="remaining-stat-label">Total Skipped</div>
                        <div class="remaining-stat-value" id="remainingSkippedCount">0</div>
                    </div>
                    <div class="remaining-stat-box">
                        <div class="remaining-stat-label">Not Auctioned Yet</div>
                        <div class="remaining-stat-value" id="remainingNotAuctionedCount">0</div>
                    </div>
                    <div class="remaining-stat-box">
                        <div class="remaining-stat-label">Total Remaining</div>
                        <div class="remaining-stat-value total" id="remainingTotalCount">0</div>
                    </div>
                </div>
                <div id="remainingPlayersContent" class="modal-players-display"></div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ========= PHP → JS DATA ========= */
        const defaultBasePrice        = 500; // This will be updated per season
        const defaultBidStepSmall     = 500; // This will be updated per season
        let maxPlayersToPurchase      = 0;   // This will be updated per season
        const defaultActiveSeasonId   = <?php echo (int)$defaultActiveSeasonId; ?> || 0;

        // Store auction settings by season
        const auctionSettings = <?php echo json_encode($auctionSettings); ?>;

        // Don't set basePrice here - it will be set dynamically
        const playersDatabase = <?php echo json_encode($playersForJs ?: []); ?>;

        const seasons = <?php echo json_encode($seasonRows); ?>;
        const seasonTeams = <?php echo json_encode($teamsBySeason); ?>;
        const soldPlayersFromDB = <?php echo json_encode($soldPlayersFromDB ?: []); ?>;
        
        /* ========= GLOBAL STATE ========= */
        let currentPlayerIndex   = 0;
        let currentBidPrice      = 0;
        let soldPlayers          = [];
        let unsoldPlayers        = [];
        let skippedPlayers       = [];
        let processedPlayerIds   = new Set();
        let displayOrder         = 1;
        let autoNextTimer        = null;
        let autoNextSeconds      = 1;
        let currentCategoryFilter= 'All';
        let filteredPlayers      = [];
        let currentSeason        = '';
        let selectedTeamId       = null;
        let bidClickCountForCurrentPlayer = 0;

        /* ========= UTILS ========= */
        function showNotification(msg) {
            console.log(msg);
        }

        function getCurrentSeasonName() {
            if (!currentSeason) return '';
            const sidNum = parseInt(currentSeason, 10);
            const s = seasons.find(x => x.id === sidNum);
            return s ? (s.tournament_name + ' - ' + s.name) : '';
        }

        function generateColorDots() {
            const colors = ['gray', 'red', 'orange', 'blue', 'green'];
            const dotsContainer = document.getElementById('colorDots');
            dotsContainer.innerHTML = '';
            for (let i = 0; i < 6; i++) {
                const dot = document.createElement('div');
                dot.className = `dot ${colors[Math.floor(Math.random() * colors.length)]}`;
                dotsContainer.appendChild(dot);
            }
        }

        function updateBidDisplay() {
            document.getElementById('bidInput').value = currentBidPrice;
            const currentPriceEl = document.getElementById('playerCurrentPrice');
            if (currentPriceEl) {
                currentPriceEl.textContent = `Current Price: ₹${currentBidPrice.toLocaleString()}`;
            }
        }

        function updateStats() {
            // Get players for current season only
            const seasonPlayers = currentSeason ? 
                playersDatabase.filter(p => p.season_id == currentSeason) : 
                playersDatabase;
            
            const available = seasonPlayers.length - processedPlayerIds.size;
            document.getElementById('soldCount').textContent      = soldPlayers.length;
            document.getElementById('unsoldCount').textContent    = unsoldPlayers.length;
            document.getElementById('availableCount').textContent = available;
            document.getElementById('totalCount').textContent     = seasonPlayers.length;
            document.getElementById('purchaseCount').textContent  = maxPlayersToPurchase;
        }

        /* ========= SEASON AUCTION SETTINGS ========= */
        function updateAuctionSettings(seasonId) {
            const settings = auctionSettings[seasonId];
            if (settings) {
                maxPlayersToPurchase = settings.maxPlayers;
                
                // Update all players' base price for this season
                playersDatabase.forEach(player => {
                    if (player.season_id == seasonId) {
                        player.basePrice = settings.basePrice;
                    }
                });
                
                return settings;
            }
            return null;
        }

        function displayPlayerCard(player, status, team = null) {
            const display = document.getElementById('playersDisplay');
            const card = document.createElement('div');
            const formattedName = formatPlayerName(player.name);
            card.className = 'player-display-card';

            let cardColor = '#667eea';
            let teamInfo  = '';
            let desc      = '';

            if (status === 'sold' && team) {
                cardColor = team.color || '#667eea';
                teamInfo  = `<div class="player-display-team">${team.name}</div>`;
                desc      = `<div class="player-sold-description">🎉 Sold to <strong>${team.name}</strong> for <strong>₹${currentBidPrice.toLocaleString()}</strong></div>`;
            } else {
                teamInfo = `<div class="player-display-team">${player.team || ''}</div>`;
            }

            const statusBadge = status === 'sold'
                ? `<div class="status-badge status-sold"><span class="status-text">SOLD</span></div>`
                : `<div class="status-badge status-unsold"><span class="status-text">UNSOLD</span></div>`;

            card.style.background = `linear-gradient(135deg, ${cardColor} 0%, ${cardColor}dd 100%)`;
            card.innerHTML = `
                <div class="player-number-badge">${displayOrder}</div>
                <div class="player-avatar">👤</div>
                <div class="player-display-name">${formattedName}</div>
                ${teamInfo}
                <div class="player-display-price">₹${currentBidPrice.toLocaleString()}</div>
                ${desc}
                ${statusBadge}
            `;

            display.appendChild(card);
            displayOrder++;
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        /* ========= PLAYER FLOW ========= */
        function applyFilter(category) {
            currentCategoryFilter = category;

            // Check if no season is selected
            if (!currentSeason) {
                document.getElementById('playerName').textContent = 'No Season Selected';
                document.getElementById('playerBasePrice').textContent = '⚠️ Please select a season first';
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
                updateStats();
                return;
            }
            
            // Get players for current season only
            const seasonPlayers = currentSeason ? 
                playersDatabase.filter(p => p.season_id == currentSeason) : 
                playersDatabase;
            
            // Check if season has no players at all
            if (seasonPlayers.length === 0) {
                document.getElementById('playerName').textContent = 'No Players Found';
                document.getElementById('playerBasePrice').textContent = currentSeason ? 
                    '⚠️ No players assigned to this season' : 
                    '⚠️ Please select a season first';
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
                updateStats();
                return;
            }
            
            if (category === 'All') {
                filteredPlayers = [...seasonPlayers];
            } else {
                filteredPlayers = seasonPlayers.filter(p => p.category === category);
            }

            // Check if category filter resulted in no players
            if (filteredPlayers.length === 0) {
                document.getElementById('playerName').textContent = `No ${category} Players`;
                document.getElementById('playerBasePrice').textContent = `⚠️ No ${category} players in this season`;
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
                updateStats();
                return;
            }

            const allProcessed = filteredPlayers.every(p => processedPlayerIds.has(p.id));
            if (allProcessed) {
                document.getElementById('playerName').textContent = '🎉 All Players Processed!';
                document.getElementById('playerBasePrice').textContent = '✅ Auction Completed';
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
                showNotification(`Auction Complete! All ${filteredPlayers.length} players processed.`);
                updateStats();
                return;
            }

            const idx = filteredPlayers.findIndex(p => !processedPlayerIds.has(p.id));
            if (idx === -1) {
                const remaining = seasonPlayers.filter(p => !processedPlayerIds.has(p.id));
                if (remaining.length > 0) {
                    showNotification(`All ${category} players processed! ${remaining.length} remaining in other categories.`);
                }
                document.getElementById('playerName').textContent = `All ${category} Processed!`;
                document.getElementById('playerBasePrice').textContent = remaining.length > 0 ? '⚠️ Select another category' : '✅ Auction Complete';
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
            } else {
                const toLoad = filteredPlayers[idx];
                const realIndex = playersDatabase.findIndex(p => p.id === toLoad.id);
                loadPlayer(realIndex);
            }
            
            updateStats();
        }

        function formatPlayerName(fullName) {
            if (!fullName) return '';
            return fullName
                .split(' ')
                .filter(Boolean)
                .map(part => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase())
                .join(' ');
        }

        function loadPlayer(index) {
            document.getElementById('soldBtn').disabled = false;
            document.getElementById('unsoldBtn').disabled = false;

            // Get players for current season
            const seasonPlayers = currentSeason ? 
                playersDatabase.filter(p => p.season_id == currentSeason) : 
                playersDatabase;

            // Check if season has no players
            if (seasonPlayers.length === 0) {
                document.getElementById('playerName').textContent = 'No Players Found';
                document.getElementById('playerBasePrice').textContent = currentSeason ? 
                    '⚠️ No players assigned to this season' : 
                    '⚠️ Please select a season first';
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
                return;
            }

            const allProcessed = seasonPlayers.every(p => processedPlayerIds.has(p.id));
            if (allProcessed) {
                document.getElementById('playerName').textContent = '🎉 All Players Processed!';
                document.getElementById('playerBasePrice').textContent = '✅ Auction Completed';
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
                return;
            }

            let attempts = 0;
            const maxAttempts = playersDatabase.length;

            while (attempts < maxAttempts) {
                if (index >= playersDatabase.length) index = 0;
                const player = playersDatabase[index];
                const matchesFilter = currentCategoryFilter === 'All' || player.category === currentCategoryFilter;
                const matchesSeason = !currentSeason || player.season_id == currentSeason;

                if (!processedPlayerIds.has(player.id) && matchesFilter && matchesSeason) {
                    currentPlayerIndex = index;
                    const formattedName = formatPlayerName(player.name);
                    document.getElementById('playerName').textContent = `(${index + 1}) ${formattedName}`;
                    
                    // Get the base price for this player's season
                    const settings = auctionSettings[player.season_id];
                    const basePrice = settings ? settings.basePrice : (player.basePrice || 500);
                    
                    document.getElementById('playerBasePrice').textContent = `Base Price: ₹${basePrice.toLocaleString()}`;
                    generateColorDots();
                    currentBidPrice = basePrice;
                    updateBidDisplay();
                    bidClickCountForCurrentPlayer = 0;
                    return;
                }
                index++;
                attempts++;
            }

            const catText = currentCategoryFilter === 'All' ? 'All Players' : `All ${currentCategoryFilter}`;
            document.getElementById('playerName').textContent = `${catText} Processed!`;
            document.getElementById('playerBasePrice').textContent = currentCategoryFilter === 'All' ? '🎉 Auction Complete!' : '⚠️ Select another category or "All"';
            document.getElementById('playerCurrentPrice').textContent = '';
            document.getElementById('colorDots').innerHTML = '';
            document.getElementById('soldBtn').disabled = true;
            document.getElementById('unsoldBtn').disabled = true;
        }

        /* ========= LOAD SOLD PLAYERS FROM DATABASE ========= */
        function loadSoldPlayersFromDB() {
            console.log('Loading sold players from database:', soldPlayersFromDB.length);
            
            soldPlayersFromDB.forEach(soldPlayer => {
                const playerId = soldPlayer.player_id;
                const teamId = soldPlayer.team_id;
                const seasonId = soldPlayer.season_id;
                const soldPrice = soldPlayer.sold_price || 0;
                
                processedPlayerIds.add(playerId);
                
                const teams = seasonTeams[seasonId] || [];
                const team = teams.find(t => t.id === teamId);
                
                if (team) {
                    const player = playersDatabase.find(p => p.id === playerId);
                    
                    if (player) {
                        if (!team.players) team.players = [];
                        
                        const alreadyInTeam = team.players.some(p => p.id === playerId);
                        if (!alreadyInTeam) {
                            team.players.push({
                                ...player,
                                price: soldPrice
                            });
                        }
                        
                        const alreadySold = soldPlayers.some(p => p.id === playerId);
                        if (!alreadySold) {
                            soldPlayers.push({
                                ...player,
                                price: soldPrice,
                                teamName: team.name,
                                teamColor: team.color
                            });
                            
                            currentBidPrice = soldPrice;
                            displayPlayerCard(player, 'sold', team);
                        }
                    }
                }
            });
            
            console.log('Processed players:', processedPlayerIds.size);
            console.log('Sold players:', soldPlayers.length);
            updateStats();
        }

        /* ========= SAVE SOLD PLAYER TO DATABASE ========= */
        function saveSoldPlayerToDB(playerId, teamId, soldPrice, seasonId, callback) {
            fetch('save_sold_player.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `player_id=${playerId}&team_id=${teamId}&sold_price=${soldPrice}&season_id=${seasonId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('✅ Player saved to database:', data.message);
                    if (callback) callback();
                } else {
                    alert('❌ Error saving player: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save player to database. Please check your connection.');
            });
        }

        /* ========= SEASON & TEAMS VIEW ========= */
        function loadSeasonTeams() {
            const teamsList = document.getElementById('teamsList');
            if (!currentSeason) {
                teamsList.innerHTML = '<div class="no-teams-message">Select a season to view teams</div>';
                return;
            }

            const teams = seasonTeams[currentSeason] || [];
            if (!teams.length) {
                teamsList.innerHTML = '<div class="no-teams-message">No teams found for this season.</div>';
                return;
            }

            let html = '';
            teams.forEach(team => {
                const isSelected = selectedTeamId === team.id;
                const budget = team.budget || 0;
                const spent = team.spent || 0;
                const remaining = team.remaining || 0;
                const usedPercent = budget ? (spent / budget) * 100 : 0;

                html += `
                <div class="team-card-compact ${isSelected ? 'team-selected' : ''}"
                    style="border-left:4px solid ${team.color}"
                    onclick="selectTeam(${team.id})">
                    <div class="team-compact-header">
                        <div class="team-compact-name">
                            ${team.name}
                            ${isSelected ? '<span class="selected-badge-small">✓</span>' : ''}
                        </div>                
                    </div>
                    <div class="team-compact-stats">
                        <div class="compact-stat">
                            <span class="compact-label">Budget</span>
                            <span class="compact-value">₹${budget.toLocaleString()}</span>
                        </div>
                        <div class="compact-stat">
                            <span class="compact-label">Left</span>
                            <span class="compact-value remaining">₹${remaining.toLocaleString()}</span>
                        </div>
                        <div class="compact-stat">
                            <span class="compact-label">Players</span>
                            <span class="compact-value players">${(team.players || []).length}</span>
                        </div>
                    </div>
                    <div class="budget-bar">
                        <div class="budget-bar-fill" style="width:${usedPercent}%; background:${team.color}"></div>
                    </div>
                    
                    ${(team.players || []).length > 0 ? `
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                            ${team.players.map(player => `
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px 0; font-size: 11px;">
                                    <span style="color: #fff;">${player.name}</span>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="color: #27ae60; font-weight: 600;">₹${player.price.toLocaleString()}</span>
                                        <button class="btn-icon delete" onclick="event.stopPropagation(); deleteTeamPlayer(${team.id}, ${player.id}, ${player.price});" title="Delete Player">🗑️</button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>`;
            });

            teamsList.innerHTML = html;
        }

        function selectTeam(teamId) {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }

            const player = playersDatabase[currentPlayerIndex];
            if (!player) return;

            if (bidClickCountForCurrentPlayer === 0) {
                bidClickCountForCurrentPlayer = 1;
            } else {
                currentBidPrice += defaultBidStepSmall;
                updateBidDisplay();
                bidClickCountForCurrentPlayer++;
            }

            selectedTeamId = teamId;
            loadSeasonTeams();

            const teams = seasonTeams[currentSeason] || [];
            const team = teams.find(t => t.id === teamId);

            if (team) {
                showNotification(`${team.name} is bidding ₹${currentBidPrice.toLocaleString()} for ${player.name}`);
            }
        }

        /* ========= MODAL: SELECT SPECIFIC PLAYER ========= */
        function openPlayerSelectionModal() {
            const modal = document.getElementById('selectPlayerModal');
            const playerList = document.getElementById('playerList');
            playerList.innerHTML = '';

            // Filter by current season
            const seasonPlayers = currentSeason ? 
                playersDatabase.filter(p => p.season_id == currentSeason) : 
                playersDatabase;

            seasonPlayers.forEach((player, idx) => {
                const processed = processedPlayerIds.has(player.id);
                const item = document.createElement('div');
                item.className = 'player-list-item';
                item.style.opacity = processed ? '0.5' : '1';

                const realIndex = playersDatabase.findIndex(p => p.id === player.id);

                item.innerHTML = `
                    <div class="player-item-info">
                        <div class="player-item-name">${player.name}</div>
                        <div class="player-item-details">
                            <span class="player-item-category">${player.category}</span>
                            <span class="player-item-price">Base: ₹${player.basePrice.toLocaleString()}</span>
                        </div>
                    </div>
                    <div class="player-item-status ${processed ? 'unavailable' : 'available'}">
                        ${processed ? 'Processed' : 'Available'}
                    </div>
                `;

                if (!processed) {
                    item.addEventListener('click', () => {
                        selectPlayerByIndex(realIndex);
                        closePlayerSelectionModal();
                    });
                }
                playerList.appendChild(item);
            });

            modal.style.display = 'flex';
        }

        function closePlayerSelectionModal() {
            document.getElementById('selectPlayerModal').style.display = 'none';
            document.getElementById('searchPlayerId').value = '';
        }

        function selectPlayerByIndex(index) {
            clearTimeout(autoNextTimer);
            loadPlayer(index);
        }

        function filterPlayerList() {
            const term = document.getElementById('searchPlayerId').value.toLowerCase();
            document.querySelectorAll('.player-list-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(term) ? 'flex' : 'none';
            });
        }

        /* ========= POPUP SCREENS ========= */
        function showTeamListScreen() {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }
            const teams = seasonTeams[currentSeason] || [];
            const modal = document.getElementById('teamListModal');
            const content = document.getElementById('teamListContent');
            const titleSpan = document.getElementById('teamListSeason');
            titleSpan.textContent = getCurrentSeasonName();

            if (!teams.length) {
                content.innerHTML = '<div class="no-data-message">No teams found in this season.</div>';
            } else {
                let html = '';
                teams.forEach(team => {
                    const budget = team.budget || 0;
                    const spent = team.spent || 0;
                    const remaining = team.remaining || 0;
                    html += `
                        <div class="info-team-card" style="border-top:4px solid ${team.color}">
                            <div class="info-team-header">
                                <h4>${team.name}</h4>
                                <div class="info-team-badge">${(team.players || []).length} Players</div>
                            </div>
                            <div class="info-team-stats">
                                <div class="info-stat">
                                    <span class="info-stat-label">Budget</span>
                                    <span class="info-stat-value">₹${budget.toLocaleString()}</span>
                                </div>
                                <div class="info-stat">
                                    <span class="info-stat-label">Spent</span>
                                    <span class="info-stat-value spent">₹${spent.toLocaleString()}</span>
                                </div>
                                <div class="info-stat">
                                    <span class="info-stat-label">Remaining</span>
                                    <span class="info-stat-value remaining">₹${remaining.toLocaleString()}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                content.innerHTML = html;
            }

            modal.style.display = 'flex';
        }

        function showTeamOwnerList() {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }
            const teams = seasonTeams[currentSeason] || [];
            const modal = document.getElementById('teamOwnerModal');
            const content = document.getElementById('teamOwnerContent');
            const titleSpan = document.getElementById('teamOwnerSeason');
            titleSpan.textContent = getCurrentSeasonName();

            if (!teams.length) {
                content.innerHTML = '<div class="no-data-message">No teams found in this season.</div>';
            } else {
                let html = '<div class="owner-list">';
                teams.forEach((team, index) => {
                    html += `
                        <div class="owner-item" style="border-left:4px solid ${team.color}">
                            <div class="owner-number">${index + 1}</div>
                            <div class="owner-info">
                                <div class="owner-team">${team.name}</div>
                                <div class="owner-name">👤 Owner Info Not In DB</div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                content.innerHTML = html;
            }

            modal.style.display = 'flex';
        }

        function showTeamBalanceInfo() {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }
            const teams = seasonTeams[currentSeason] || [];
            const modal = document.getElementById('teamBalanceModal');
            const content = document.getElementById('teamBalanceContent');
            const titleSpan = document.getElementById('teamBalanceSeason');
            titleSpan.textContent = getCurrentSeasonName();

            if (!teams.length) {
                content.innerHTML = '<div class="no-data-message">No teams found in this season.</div>';
            } else {
                let html = '';
                teams.forEach(team => {
                    const budget = team.budget || 0;
                    const spent = team.spent || 0;
                    const remaining = team.remaining || 0;
                    const spentPercent = budget ? ((spent / budget) * 100).toFixed(1) : 0;
                    html += `
                        <div class="balance-card" style="border-top:4px solid ${team.color}">
                            <h4>${team.name}</h4>
                            <div class="balance-stats">
                                <div class="balance-row">
                                    <span class="balance-label">Initial Budget:</span>
                                    <span class="balance-value">₹${budget.toLocaleString()}</span>
                                </div>
                                <div class="balance-row">
                                    <span class="balance-label">Amount Spent:</span>
                                    <span class="balance-value spent">₹${spent.toLocaleString()}</span>
                                </div>
                                <div class="balance-row">
                                    <span class="balance-label">Remaining:</span>
                                    <span class="balance-value remaining">₹${remaining.toLocaleString()}</span>
                                </div>
                                <div class="balance-row">
                                    <span class="balance-label">Players Bought:</span>
                                    <span class="balance-value">${(team.players || []).length}</span>
                                </div>
                                <div class="balance-progress">
                                    <div class="balance-progress-bar" style="width:${spentPercent}%; background:${team.color}"></div>
                                </div>
                                <div class="balance-percent">${spentPercent}% Budget Used</div>
                            </div>
                        </div>
                    `;
                });
                content.innerHTML = html;
            }

            modal.style.display = 'flex';
        }

        function showTeamPlayerInfo() {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }
            const teams = seasonTeams[currentSeason] || [];
            const modal = document.getElementById('teamPlayerInfoModal');
            const content = document.getElementById('teamPlayerContent');
            const titleSpan = document.getElementById('teamPlayerSeason');
            titleSpan.textContent = getCurrentSeasonName();

            if (!teams.length) {
                content.innerHTML = '<div class="no-data-message">No teams found in this season.</div>';
            } else {
                let html = '';
                teams.forEach(team => {
                    const players = team.players || [];
                    html += `
                        <div class="team-player-card" style="border-top:4px solid ${team.color}">
                            <div class="team-player-header">
                                <h4>${team.name}</h4>
                                <span class="player-count-badge">${players.length} Players</span>
                            </div>
                            <div class="team-player-list">
                                ${!players.length ? '<div class="no-players-text">No players bought yet</div>' :
                                    players.map((player, index) => `
                                        <div class="player-info-item" style="position: relative;">
                                            <span class="player-info-number">${index + 1}</span>
                                            <div class="player-info-details">
                                                <span class="player-info-name">${player.name}</span>
                                                <span class="player-info-category">${player.category}</span>
                                            </div>
                                            <span class="player-info-price">₹${player.price.toLocaleString()}</span>
                                            <button 
                                                class="btn-delete-player" 
                                                onclick="deleteSoldPlayer(${player.id}, ${team.id})"
                                                style="margin-left: 10px; padding: 4px 8px; background: #e74c3c; border: none; border-radius: 4px; color: white; cursor: pointer; font-size: 11px;"
                                                title="Delete Player">
                                                🗑️
                                            </button>
                                        </div>
                                    `).join('')
                                }
                            </div>
                            ${players.length ? `<div class="team-total">Total Spent: ₹${(team.spent || 0).toLocaleString()}</div>` : ''}
                        </div>
                    `;
                });
                content.innerHTML = html;
            }

            modal.style.display = 'flex';
        }

        function deleteTeamPlayer(teamId, playerId, soldPrice) {
            Swal.fire({
                title: 'Remove Player?',
                html: `<p>Are you sure you want to remove this player?</p>
                    <p style="font-weight: 600; color: #27ae60; font-size: 18px;">₹${soldPrice.toLocaleString()} will be refunded to team budget</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: '🗑️ Yes, Remove!',
                cancelButtonText: 'Cancel',
                background: '#16213e',
                color: '#fff',
                customClass: {
                    popup: 'swal-custom-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Removing...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        background: '#16213e',
                        color: '#fff',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('get_tpid.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `player_id=${playerId}&team_id=${teamId}`
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            return fetch('delete_sold_player.php', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                body: `tpid=${data.tpid}`
                            });
                        }
                        throw new Error('Player not found in database');
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const teams = seasonTeams[currentSeason] || [];
                            const team = teams.find(t => t.id === teamId);
                            if (team) {
                                team.players = team.players.filter(p => p.id !== playerId);
                                team.remaining += soldPrice;
                                team.spent -= soldPrice;
                                processedPlayerIds.delete(playerId);
                                soldPlayers = soldPlayers.filter(p => p.id !== playerId);
                            }
        
                            const allCards = document.querySelectorAll('.player-display-card');
                            allCards.forEach(card => {
                                const playerNameEl = card.querySelector('.player-display-name');
                                const playerPriceEl = card.querySelector('.player-display-price');
                                
                                if (playerNameEl && playerPriceEl) {
                                    const cardPrice = playerPriceEl.textContent.replace(/[₹,]/g, '').trim();
                                    if (parseInt(cardPrice) === soldPrice) {
                                        card.style.transition = 'opacity 0.4s, transform 0.4s';
                                        card.style.opacity = '0';
                                        card.style.transform = 'scale(0.8)';
                                        
                                        setTimeout(() => {
                                            card.remove();
                                        }, 400);
                                    }
                                }
                            });
                            
                            updateStats();
                            loadSeasonTeams();                            

                            Swal.fire({
                                title: 'Removed!',
                                html: `<p>Player has been removed successfully!</p>
                                    <p style="font-weight: 600; color: #27ae60; font-size: 16px;">₹${soldPrice.toLocaleString()} refunded to team</p>`,
                                icon: 'success',                                
                                showConfirmButton: false,
                                background: '#16213e',
                                color: '#fff',
                                timer: 2000,
                                timerProgressBar: true,                                
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to remove player',
                                icon: 'error',
                                confirmButtonColor: '#e74c3c',
                                background: '#16213e',
                                color: '#fff'
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            title: 'Error!',
                            text: err.message || 'Something went wrong',
                            icon: 'error',
                            confirmButtonColor: '#e74c3c',
                            background: '#16213e',
                            color: '#fff'
                        });
                    });
                }
            });
        }

        function showMostExpensivePlayer() {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }
            const teams = seasonTeams[currentSeason] || [];
            const modal = document.getElementById('mostExpensiveModal');
            const content = document.getElementById('mostExpensiveContent');
            const titleSpan = document.getElementById('expensiveSeason');
            titleSpan.textContent = getCurrentSeasonName();

            let allPlayers = [];
            teams.forEach(team => {
                (team.players || []).forEach(player => {
                    allPlayers.push({...player, teamName: team.name, teamColor: team.color});
                });
            });

            if (!allPlayers.length) {
                content.innerHTML = '<div class="no-data-message">No players have been sold yet.</div>';
            } else {
                allPlayers.sort((a, b) => b.price - a.price);
                const most = allPlayers[0];
                content.innerHTML = `
                    <div class="expensive-player-card" style="background:linear-gradient(135deg, ${most.teamColor} 0%, ${most.teamColor}dd 100%)">
                        <div class="expensive-crown">👑</div>
                        <div class="expensive-avatar">🏆</div>
                        <h2 class="expensive-name">${most.name}</h2>
                        <div class="expensive-category">${most.category}</div>
                        <div class="expensive-price">₹${most.price.toLocaleString()}</div>
                        <div class="expensive-team">Bought by ${most.teamName}</div>
                        <div class="expensive-base">Base Price: ₹${most.basePrice ? most.basePrice.toLocaleString() : ''}</div>
                    </div>
                `;
            }

            modal.style.display = 'flex';
        }

        function showTop5Players() {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }
            const teams = seasonTeams[currentSeason] || [];
            const modal = document.getElementById('top5Modal');
            const content = document.getElementById('top5Content');
            const titleSpan = document.getElementById('top5Season');
            titleSpan.textContent = getCurrentSeasonName();

            let allPlayers = [];
            teams.forEach(team => {
                (team.players || []).forEach(player => {
                    allPlayers.push({...player, teamName: team.name, teamColor: team.color});
                });
            });

            if (!allPlayers.length) {
                content.innerHTML = '<div class="no-data-message">No players have been sold yet.</div>';
            } else {
                allPlayers.sort((a, b) => b.price - a.price);
                const top5 = allPlayers.slice(0, 5);
                const medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                let html = '';
                top5.forEach((player, index) => {
                    html += `
                        <div class="top5-item" style="border-left:5px solid ${player.teamColor}">
                            <div class="top5-rank">${medals[index]}</div>
                            <div class="top5-player-info">
                                <div class="top5-player-name">${player.name}</div>
                                <div class="top5-player-details">
                                    <span class="top5-category">${player.category}</span>
                                    <span class="top5-team">${player.teamName}</span>
                                </div>
                            </div>
                            <div class="top5-price">₹${player.price.toLocaleString()}</div>
                        </div>
                    `;
                });
                content.innerHTML = html;
            }

            modal.style.display = 'flex';
        }

        /* ========= UNSOLD & RE-AUCTION ========= */
        function showUnsoldPlayers() {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }
            const modal = document.getElementById('unsoldPlayersModal');
            const content = document.getElementById('unsoldPlayersContent');

            if (!unsoldPlayers.length) {
                content.innerHTML = '<div class="no-data-message">No unsold players yet.</div>';
            } else {
                let html = '';
                unsoldPlayers.forEach((player, index) => {
                    html += `
                        <div class="player-display-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <div class="player-number-badge">${index + 1}</div>
                            <div class="player-avatar">❌</div>
                            <div class="player-display-name">${player.name}</div>
                            <div class="player-display-team">${player.category}</div>
                            <div class="player-display-price">Base: ₹${player.basePrice.toLocaleString()}</div>
                            <div class="player-sold-description">Last Bid: ₹${player.price.toLocaleString()}</div>
                            <div class="status-badge status-unsold">
                                <span class="status-text">UNSOLD</span>
                            </div>
                            <button 
                                class="control-btn" 
                                style="margin-top:12px; font-size:11px; padding:4px 8px;"
                                data-player-id="${player.id}">
                                ♻️ Re-Auction
                            </button>
                        </div>
                    `;
                });
                content.innerHTML = html;

                content.querySelectorAll('button[data-player-id]').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const pid = parseInt(this.getAttribute('data-player-id'), 10);
                        reAuctionSingleUnsold(pid);
                    });
                });
            }

            modal.style.display = 'flex';
        }

        function reAuctionSingleUnsold(playerId) {
            if (!currentSeason) {
                Swal.fire({
                    title: 'Please Select A Season First!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }            
            const idx = unsoldPlayers.findIndex(p => p.id === playerId);
            if (idx === -1) {
                Swal.fire({
                    title: 'Player Not Found in Unsold List!',                    
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }

            const player = unsoldPlayers[idx];
            unsoldPlayers.splice(idx, 1);
            processedPlayerIds.delete(player.id);
            updateStats();

            document.getElementById('unsoldPlayersModal').style.display = 'none';

            const mainIdx = playersDatabase.findIndex(p => p.id === player.id);
            if (mainIdx !== -1) {
                currentPlayerIndex = mainIdx;
                loadPlayer(mainIdx);
            }

            showNotification(`${player.name} is now available for re-auction.`);
        }

        function reAuctionAllUnsold() {
            if (!unsoldPlayers.length) {
                Swal.fire({
                    title: 'No Unsold Players Available For Re-Auction!',
                    icon: 'warning',
                    confirmButtonColor: '#27ae60',
                    background: '#16213e',
                    color: '#fff'
                });
                return;
            }

            unsoldPlayers.forEach(p => {
                if (p.id != null) {
                    processedPlayerIds.delete(p.id);
                }
            });

            unsoldPlayers = [];
            
            updateStats();
            applyFilter(currentCategoryFilter);

            showNotification('✅ All unsold players have been moved back for re-auction.');
        }

        /* ========= REMAINING PLAYERS ========= */
        function showRemainingPlayers() {
            const seasonPlayers = currentSeason ? 
                playersDatabase.filter(p => p.season_id == currentSeason) : 
                playersDatabase;
            
            const modal = document.getElementById('remainingPlayersModal');
            const content = document.getElementById('remainingPlayersContent');

            const notAuctionedYet = seasonPlayers.filter(p => !processedPlayerIds.has(p.id));
            const totalRemaining = unsoldPlayers.length + skippedPlayers.length + notAuctionedYet.length;

            document.getElementById('remainingUnsoldCount').textContent = unsoldPlayers.length;
            document.getElementById('remainingSkippedCount').textContent = skippedPlayers.length;
            document.getElementById('remainingNotAuctionedCount').textContent = notAuctionedYet.length;
            document.getElementById('remainingTotalCount').textContent = totalRemaining;

            if (!totalRemaining) {
                content.innerHTML = '<div class="no-data-message">All players have been sold! 🎉</div>';
            } else {
                let html = '';

                unsoldPlayers.forEach((player, index) => {
                    html += `
                        <div class="player-display-card" style="background:linear-gradient(135deg,#e74c3c 0%,#c0392b 100%)">
                            <div class="player-number-badge">${index + 1}</div>
                            <div class="player-avatar">❌</div>
                            <div class="player-display-name">${player.name}</div>
                            <div class="player-display-team">${player.category}</div>
                            <div class="player-display-price">₹${player.basePrice.toLocaleString()}</div>
                            <div class="status-badge status-unsold"><span class="status-text">UNSOLD</span></div>
                        </div>
                    `;
                });

                skippedPlayers.forEach((player, index) => {
                    html += `
                        <div class="player-display-card" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%)">
                            <div class="player-number-badge">${index + 1}</div>
                            <div class="player-avatar">⏭️</div>
                            <div class="player-display-name">${player.name}</div>
                            <div class="player-display-team">${player.category}</div>
                            <div class="player-display-price">₹${player.basePrice.toLocaleString()}</div>
                            <div class="status-badge" style="background:rgba(243,156,18,0.9)"><span class="status-text">SKIPPED</span></div>
                        </div>
                    `;
                });

                notAuctionedYet.forEach((player, index) => {
                    html += `
                        <div class="player-display-card" style="background:linear-gradient(135deg,#3498db 0%,#2980b9 100%)">
                            <div class="player-number-badge">${index + 1}</div>
                            <div class="player-avatar">⏳</div>
                            <div class="player-display-name">${player.name}</div>
                            <div class="player-display-team">${player.category}</div>
                            <div class="player-display-price">₹${player.basePrice.toLocaleString()}</div>
                            <div class="status-badge" style="background:rgba(52,152,219,0.9)"><span class="status-text">PENDING</span></div>
                        </div>
                    `;
                });

                content.innerHTML = html;
            }

            modal.style.display = 'flex';
        }

        /* ========= BUTTON HANDLERS ========= */
        function initEvents() {
            document.getElementById('categoryFilter').addEventListener('change', e => {
                if (!currentSeason) {
                    Swal.fire({
                        title: 'Please Select A Season First!',
                        icon: 'warning',
                        confirmButtonColor: '#27ae60',
                        background: '#16213e',
                        color: '#fff'
                    });
                    e.target.value = 'All'; // Reset to All
                    return;
                }
                applyFilter(e.target.value);
            });

            document.getElementById('autoNextSelect').addEventListener('change', e => {
                autoNextSeconds = parseInt(e.target.value, 10) || 1;
            });

            document.getElementById('seasonSelect').addEventListener('change', function () {
                currentSeason = this.value;
                selectedTeamId = null;
                
                // Update auction settings for this season
                const settings = updateAuctionSettings(currentSeason);
                if (settings) {
                    console.log(`Season changed: Base Price = ₹${settings.basePrice}, Bid Step = ₹${settings.bidStep}`);
                }
                
                loadSeasonTeams();
                
                // Reset and reload players for the selected season
                processedPlayerIds.clear();
                soldPlayers = [];
                unsoldPlayers = [];
                skippedPlayers = [];
                displayOrder = 1;
                document.getElementById('playersDisplay').innerHTML = '';
                
                // Load sold players for this season
                loadSoldPlayersFromDB();
                
                // Load first player
                applyFilter(currentCategoryFilter);
            });            

            document.getElementById('nextPlayerBtn').addEventListener('click', () => {
                clearTimeout(autoNextTimer);
                currentPlayerIndex++;
                if (currentPlayerIndex >= playersDatabase.length) currentPlayerIndex = 0;
                loadPlayer(currentPlayerIndex);
            });

            document.getElementById('skipBtn').addEventListener('click', () => {
                clearTimeout(autoNextTimer);
                const player = playersDatabase[currentPlayerIndex];
                if (processedPlayerIds.has(player.id)) {
                    showNotification(`${player.name} already processed.`);
                    return;
                }
                skippedPlayers.push({...player});
                processedPlayerIds.add(player.id);
                updateStats();
                currentPlayerIndex++;
                loadPlayer(currentPlayerIndex);
            });

            // SOLD BUTTON
            document.getElementById('soldBtn').addEventListener('click', function () {
                const player = playersDatabase[currentPlayerIndex];

                if (processedPlayerIds.has(player.id)) {
                    showNotification(`${player.name} already processed.`);
                    return;
                }
                if (!currentSeason) {
                    Swal.fire({
                        title: 'Please select a season first!',
                        icon: 'warning',
                        confirmButtonColor: '#27ae60',
                        background: '#16213e',
                        color: '#fff'
                    });
                    return;
                }
                if (!selectedTeamId) {
                    Swal.fire({
                        title: 'Please select a team first!',
                        icon: 'warning',
                        confirmButtonColor: '#27ae60',
                        background: '#16213e',
                        color: '#fff'
                    });                    
                    return;
                }

                const teams = seasonTeams[currentSeason] || [];
                const team = teams.find(t => t.id === selectedTeamId);
                if (!team) {
                    alert('Selected team not found!');
                    return;
                }

                const remaining = team.remaining || 0;
                if (remaining < currentBidPrice) {
                    alert(`${team.name} doesn't have enough budget! Remaining: ₹${remaining.toLocaleString()}`);
                    return;
                }

                // Save to database first
                saveSoldPlayerToDB(player.id, team.id, currentBidPrice, currentSeason, () => {
                    processedPlayerIds.add(player.id);

                    team.spent = (team.spent || 0) + currentBidPrice;
                    team.remaining = remaining - currentBidPrice;

                    fetch("update_remaining.php", {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: `team_id=${team.id}&remaining=${team.remaining}`
                    });

                    if (!team.players) team.players = [];
                    team.players.push({...player, price: currentBidPrice});

                    soldPlayers.push({
                        ...player,
                        price: currentBidPrice,
                        teamName: team.name,
                        teamColor: team.color
                    });

                    displayPlayerCard(player, 'sold', team);
                    updateStats();
                    loadSeasonTeams();

                    showNotification(`✅ ${player.name} sold to ${team.name} for ₹${currentBidPrice.toLocaleString()}`);

                    setTimeout(() => {
                        currentPlayerIndex++;
                        loadPlayer(currentPlayerIndex);
                    }, autoNextSeconds * 1000);
                });
            });

            // UNSOLD
            document.getElementById('unsoldBtn').addEventListener('click', function () {
                const player = playersDatabase[currentPlayerIndex];

                if (processedPlayerIds.has(player.id)) {
                    showNotification(`${player.name} already processed.`);
                    return;
                }
                if (!currentSeason) {
                    Swal.fire({
                        title: 'Please Select A Season First!',                    
                        icon: 'warning',
                        confirmButtonColor: '#27ae60',
                        background: '#16213e',
                        color: '#fff'
                    });
                    return;
                }

                processedPlayerIds.add(player.id);
                unsoldPlayers.push({...player, price: currentBidPrice});
                displayPlayerCard(player, 'unsold');
                updateStats();

                setTimeout(() => {
                    currentPlayerIndex++;
                    loadPlayer(currentPlayerIndex);
                }, autoNextSeconds * 1000);
            });

            // Bid controls
            document.getElementById('plus100Btn').addEventListener('click', () => {
                currentBidPrice += defaultBidStepSmall;
                updateBidDisplay();
            });
            document.getElementById('minus100Btn').addEventListener('click', () => {
                if (currentBidPrice >= defaultBidStepSmall) {
                    currentBidPrice -= defaultBidStepSmall;
                    updateBidDisplay();
                }
            });
            document.getElementById('plusBtn').addEventListener('click', () => {
                currentBidPrice += 1000;
                updateBidDisplay();
            });
            document.getElementById('minusBtn').addEventListener('click', () => {
                if (currentBidPrice >= 1000) {
                    currentBidPrice -= 1000;
                    updateBidDisplay();
                }
            });
            document.getElementById('resetBtn').addEventListener('click', () => {
                const player = playersDatabase[currentPlayerIndex];
                currentBidPrice = player.basePrice;
                updateBidDisplay();
            });

            // Select specific player modal
            document.getElementById('selectSpecificBtn').addEventListener('click', openPlayerSelectionModal);
            document.getElementById('closeModal').addEventListener('click', closePlayerSelectionModal);
            document.getElementById('selectPlayerModal').addEventListener('click', e => {
                if (e.target.id === 'selectPlayerModal') closePlayerSelectionModal();
            });
            document.getElementById('searchPlayerId').addEventListener('input', filterPlayerList);

            // Popup buttons
            document.getElementById('teamListBtn').addEventListener('click', showTeamListScreen);
            document.getElementById('teamOwnerBtn').addEventListener('click', showTeamOwnerList);
            document.getElementById('teamBalanceBtn').addEventListener('click', showTeamBalanceInfo);
            document.getElementById('teamPlayerInfoBtn').addEventListener('click', showTeamPlayerInfo);
            document.getElementById('mostExpensiveBtn').addEventListener('click', showMostExpensivePlayer);
            document.getElementById('top5PlayersBtn').addEventListener('click', showTop5Players);
            document.getElementById('unsoldDisplayBtn').addEventListener('click', showUnsoldPlayers);
            document.getElementById('remainingPlayersBtn').addEventListener('click', showRemainingPlayers);
            document.getElementById('reAuctionBtn').addEventListener('click', reAuctionAllUnsold);

            // Close buttons for modals
            document.getElementById('closeTeamListModal').addEventListener('click', () => {
                document.getElementById('teamListModal').style.display = 'none';
            });
            document.getElementById('closeTeamOwnerModal').addEventListener('click', () => {
                document.getElementById('teamOwnerModal').style.display = 'none';
            });
            document.getElementById('closeTeamBalanceModal').addEventListener('click', () => {
                document.getElementById('teamBalanceModal').style.display = 'none';
            });
            document.getElementById('closeTeamPlayerModal').addEventListener('click', () => {
                document.getElementById('teamPlayerInfoModal').style.display = 'none';
            });
            document.getElementById('closeMostExpensiveModal').addEventListener('click', () => {
                document.getElementById('mostExpensiveModal').style.display = 'none';
            });
            document.getElementById('closeTop5Modal').addEventListener('click', () => {
                document.getElementById('top5Modal').style.display = 'none';
            });
            document.getElementById('closeUnsoldModal').addEventListener('click', () => {
                document.getElementById('unsoldPlayersModal').style.display = 'none';
            });
            document.getElementById('closeRemainingModal').addEventListener('click', () => {
                document.getElementById('remainingPlayersModal').style.display = 'none';
            });

            // Close modal when clicking overlay background
            [
                'teamListModal', 'teamOwnerModal', 'teamBalanceModal', 'teamPlayerInfoModal',
                'mostExpensiveModal', 'top5Modal', 'unsoldPlayersModal', 'remainingPlayersModal'
            ].forEach(id => {
                const m = document.getElementById(id);
                m.addEventListener('click', e => {
                    if (e.target.id === id) {
                        m.style.display = 'none';
                    }
                });
            });
        }

        /* ========= INIT ========= */
        function init() {
            if (!playersDatabase || !playersDatabase.length) {
                document.getElementById('playerName').textContent = 'No players found in database';
                document.getElementById('playerBasePrice').textContent = 'Please add players to seasons first';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
                return;
            }
            
            initEvents();

            // Auto-select season if players have been sold
            if (!currentSeason && soldPlayersFromDB.length > 0) {
                const firstSoldPlayer = soldPlayersFromDB[0];
                currentSeason = firstSoldPlayer.season_id;
                
                const sel = document.getElementById('seasonSelect');
                if (sel) {
                    sel.value = currentSeason;
                }
            } else if (currentSeason) {
                const sel = document.getElementById('seasonSelect');
                if (sel) sel.value = currentSeason;
            }

            // Load sold players from database
            loadSoldPlayersFromDB();
            
            // Load season teams
            if (currentSeason) {
                loadSeasonTeams();
                applyFilter('All');
                loadPlayer(0);
            } else {
                // No season selected - show message
                document.getElementById('playerName').textContent = 'No Season Selected';
                document.getElementById('playerBasePrice').textContent = '⚠️ Please select a season first';
                document.getElementById('playerCurrentPrice').textContent = '';
                document.getElementById('colorDots').innerHTML = '';
                document.getElementById('soldBtn').disabled = true;
                document.getElementById('unsoldBtn').disabled = true;
            }
            
            updateStats();
        }

        window.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>