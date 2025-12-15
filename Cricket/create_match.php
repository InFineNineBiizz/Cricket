<?php
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_a_id = $_POST['team_a_id'];
    $team_b_id = $_POST['team_b_id'];
    $toss_winner_id = $_POST['toss_winner_id'];
    $elected_to = $_POST['elected_to'];
    
    $sql = "INSERT INTO matches (team_a_id, team_b_id, toss_winner_id, elected_to, match_status) 
            VALUES (?, ?, ?, ?, 'upcoming')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiis", $team_a_id, $team_b_id, $toss_winner_id, $elected_to);
    
    if (mysqli_stmt_execute($stmt)) {
        $match_id = mysqli_insert_id($conn);
        header("Location: scoring.php?match_id=" . $match_id);
        exit();
    }
}

// Get all teams
$teams_sql = "SELECT * FROM teams ORDER BY team_name";
$teams_result = mysqli_query($conn, $teams_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Match | CrickFolio</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    <script src="assets/script/sweetalert2.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #F59E0B;
            --primary-dark: #D97706;
            --secondary-color: #FFA500;
            --accent-color: #FBBF24;
            --dark-bg: #3C4857;
            --card-bg: #F5F7FA;
            --text-primary: #2D3748;
            --text-secondary: #64748B;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --light-grey: #F5F7FA;
            --medium-grey: #E2E8F0;
            --border-grey: #CBD5E0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--light-grey);
            min-height: 100vh;
            color: var(--text-primary);
            padding-bottom: 50px;
        }
        
        /* Header Styles */
        .header {
            background: white;
            border-bottom: 1px solid var(--border-grey);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn {
            background: var(--light-grey);
            border: 1px solid var(--border-grey);
            color: var(--text-primary);
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .back-btn:hover {
            background: var(--medium-grey);
            border-color: var(--primary-color);
            transform: translateX(-4px);
        }
        
        .header-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .cricket-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        }
        
        /* Main Content */
        .main-container {
            margin-top: 30px;
        }
        
        .section-card {
            background: white;
            border: 1px solid var(--border-grey);
            border-radius: 16px;
            padding: 30px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .section-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .section-card:hover::before {
            opacity: 1;
        }
        
        .section-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(245, 158, 11, 0.12);
            border-color: var(--accent-color);
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .section-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: white;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .section-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 400;
        }
        
        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-select {
            background: var(--light-grey);
            border: 2px solid var(--border-grey);
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 15px;
            color: var(--text-primary);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .form-select:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
            outline: none;
            color: var(--text-primary);
        }
        
        .form-select option {
            background: white;
            color: var(--text-primary);
        }
        
        /* VS Badge */
        .vs-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            position: relative;
        }
        
        .vs-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 10px 28px;
            border-radius: 24px;
            font-weight: 800;
            font-size: 15px;
            letter-spacing: 2px;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
            position: relative;
            z-index: 1;
        }
        
        .vs-badge::before,
        .vs-badge::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 50px;
            height: 2px;
            background: var(--border-grey);
        }
        
        .vs-badge::before {
            right: 100%;
            margin-right: 10px;
        }
        
        .vs-badge::after {
            left: 100%;
            margin-left: 10px;
        }
        
        /* Option Cards */
        .option-card {
            background: var(--light-grey);
            border: 2px solid var(--border-grey);
            border-radius: 14px;
            padding: 24px 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .option-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .option-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15);
            background: white;
        }
        
        .option-card.selected {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-color: var(--primary-color);
            box-shadow: 0 8px 28px rgba(245, 158, 11, 0.35);
            color: white;
        }
        
        .option-card.selected::before {
            opacity: 1;
        }
        
        .option-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--border-grey);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .option-card:hover .option-icon {
            transform: scale(1.1) rotate(10deg);
            border-color: var(--primary-color);
        }
        
        .option-card.selected .option-icon {
            background: white;
            border-color: white;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .option-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
            position: relative;
            z-index: 1;
            color: var(--text-primary);
        }
        
        .option-card.selected .option-title {
            color: white;
        }
        
        .option-subtitle {
            font-size: 13px;
            opacity: 0.7;
            font-weight: 500;
            position: relative;
            z-index: 1;
            color: var(--text-secondary);
        }
        
        .option-card.selected .option-subtitle {
            color: white;
            opacity: 0.9;
        }
        
        /* Coin Section */
        .coin-section {
            background: white;
            border: 1px solid var(--border-grey);
            border-radius: 16px;
            padding: 40px;
            margin-top: 30px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .coin-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        
        .coin-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 30px;
        }
        
        .coin {
            width: 160px;
            height: 160px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: white;
            box-shadow: 0 12px 40px rgba(245, 158, 11, 0.35);
            cursor: pointer;
            border: 6px solid rgba(245, 158, 11, 0.2);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .coin::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: linear-gradient(145deg, transparent, rgba(255, 255, 255, 0.3));
            pointer-events: none;
        }
        
        .coin:hover {
            transform: scale(1.08);
            box-shadow: 0 16px 50px rgba(245, 158, 11, 0.45);
        }
        
        .coin:active {
            transform: scale(0.95);
        }
        
        .coin.flipping {
            animation: flip 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes flip {
            0%, 100% { 
                transform: rotateY(0deg) scale(1);
            }
            50% { 
                transform: rotateY(1800deg) scale(1.15);
            }
        }
        
        .coin-text {
            text-align: center;
            line-height: 1.3;
            font-size: 15px;
            position: relative;
            z-index: 1;
        }
        
        .coin-result {
            font-size: 20px;
            font-weight: 700;
            min-height: 35px;
            color: var(--primary-color);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Submit Button */
        .submit-section {
            margin-top: 40px;
            text-align: center;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 18px 60px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 14px;
            color: white;
            box-shadow: 0 8px 28px rgba(245, 158, 11, 0.35);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .submit-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .submit-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(245, 158, 11, 0.45);
        }
        
        .submit-btn:active {
            transform: translateY(-1px);
        }
        
        .submit-btn span {
            position: relative;
            z-index: 1;
        }
        
        /* Radio inputs */
        input[type="radio"] {
            display: none;
        }
        
        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-in {
            animation: slideInUp 0.6s ease forwards;
        }
        
        .animate-in:nth-child(1) { animation-delay: 0.1s; }
        .animate-in:nth-child(2) { animation-delay: 0.2s; }
        .animate-in:nth-child(3) { animation-delay: 0.3s; }
        
        /* Badge Indicators */
        .badge-indicator {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 12px;
            height: 12px;
            background: var(--primary-color);
            border-radius: 50%;
            border: 2px solid white;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
        }
        
        .option-card.selected .badge-indicator {
            opacity: 1;
            transform: scale(1);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .section-card {
                margin-bottom: 20px;
            }
            
            .header-title {
                font-size: 20px;
            }
        }
        
        /* Loading State */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Tooltips */
        [data-tooltip] {
            position: relative;
        }
        
        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 8px 12px;
            background: var(--dark-bg);
            color: white;
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
            margin-bottom: 8px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="header-content">
                <button class="back-btn" onclick="window.location.href='index.php'" data-tooltip="Go Back">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <div class="cricket-icon">
                    🏏
                </div>
                <h1 class="header-title">Create New Match</h1>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container main-container">
        <form method="POST" id="matchForm">
            
            <!-- Main Row with 3 Columns -->
            <div class="row">
                
                <!-- Column 1: Select Playing Teams -->
                <div class="col-lg-4 mb-4 animate-in">
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <h3 class="section-title">Select Teams</h3>
                            <p class="section-subtitle">Choose the competing teams</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <span style="color: var(--primary-color);">●</span> Team A
                            </label>
                            <select name="team_a_id" id="team_a" class="form-select" required>
                                <option value="" hidden>Choose Team A</option>
                                <?php 
                                mysqli_data_seek($teams_result, 0);
                                while ($team = mysqli_fetch_assoc($teams_result)): ?>
                                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="vs-container">
                            <span class="vs-badge">VS</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <span style="color: var(--dark-bg);">●</span> Team B
                            </label>
                            <select name="team_b_id" id="team_b" class="form-select" required>
                                <option value="" hidden>Choose Team B</option>
                                <?php 
                                mysqli_data_seek($teams_result, 0);
                                while ($team = mysqli_fetch_assoc($teams_result)): ?>
                                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Column 2: Who Won the Toss -->
                <div class="col-lg-4 mb-4 animate-in">
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <h3 class="section-title">Toss Winner</h3>
                            <p class="section-subtitle">Who won the toss?</p>
                        </div>
                        
                        <div class="option-card" onclick="selectTossWinner('team_a')" data-tooltip="Select Team A">
                            <div class="badge-indicator"></div>
                            <input type="radio" name="toss_winner" id="toss_team_a" value="team_a">
                            <div class="option-icon">
                                🏏
                            </div>
                            <div class="option-title" id="toss_team_a_name">Team A</div>
                            <div class="option-subtitle">Toss Winner</div>
                        </div><br>
                        
                        <div class="option-card" onclick="selectTossWinner('team_b')" data-tooltip="Select Team B">
                            <div class="badge-indicator"></div>
                            <input type="radio" name="toss_winner" id="toss_team_b" value="team_b">
                            <div class="option-icon">
                                🏏
                            </div>
                            <div class="option-title" id="toss_team_b_name">Team B</div>
                            <div class="option-subtitle">Toss Winner</div>
                        </div>
                        
                        <input type="hidden" name="toss_winner_id" id="toss_winner_id" required>
                    </div>
                </div>
                
                <!-- Column 3: Toss Decision -->
                <div class="col-lg-4 mb-4 animate-in">
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-clipboard-check-fill"></i>
                            </div>
                            <h3 class="section-title">Toss Decision</h3>
                            <p class="section-subtitle">What's your choice?</p>
                        </div>
                        
                        <div class="option-card" onclick="selectElectedTo('bat')" data-tooltip="Choose to Bat First">
                            <div class="badge-indicator"></div>
                            <input type="radio" name="elected_to" id="elect_bat" value="bat">
                            <div class="option-icon">
                                🏏
                            </div>
                            <div class="option-title">BAT FIRST</div>
                            <div class="option-subtitle">Set the target</div>
                        </div><br>
                        
                        <div class="option-card" onclick="selectElectedTo('bowl')" data-tooltip="Choose to Bowl First">
                            <div class="badge-indicator"></div>
                            <input type="radio" name="elected_to" id="elect_bowl" value="bowl">
                            <div class="option-icon">
                                ⚾
                            </div>
                            <div class="option-title">BOWL FIRST</div>
                            <div class="option-subtitle">Chase the target</div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Coin Flip Section -->
            <div class="coin-section">
                <h3 class="coin-title">
                    <i class="bi bi-coin"></i> Virtual Coin Toss
                </h3>
                <p class="coin-subtitle">Click the coin for a random toss result</p>
                
                <div class="coin" id="coin" onclick="flipCoin()" data-tooltip="Click to Flip">
                    <div class="coin-text">
                        <strong style="font-size: 18px;">CRIC</strong><br>
                        <strong style="font-size: 18px;">HEROES</strong><br>
                        <span style="font-size: 12px; opacity: 0.8;">TAP TO FLIP</span>
                    </div>
                </div>
                
                <div class="coin-result" id="coinResult"></div>
            </div>

            <!-- Submit Button -->
            <div class="submit-section">
                <button type="submit" class="btn submit-btn">
                    <span>
                        <i class="bi bi-play-circle-fill"></i> Start Match
                    </span>
                </button>
            </div>
            
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let teamAId = '';
        let teamBId = '';
        let teamAName = '';
        let teamBName = '';

        // Add smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Team selection handlers
        document.getElementById('team_a').addEventListener('change', function() {
            teamAId = this.value;
            teamAName = this.options[this.selectedIndex].text;
            const nameElement = document.getElementById('toss_team_a_name');
            nameElement.textContent = teamAName;
            nameElement.style.animation = 'none';
            setTimeout(() => {
                nameElement.style.animation = 'fadeIn 0.5s ease';
            }, 10);
            
            // Filter Team B options
            if (teamAId) {
                filterTeamOptions('team_b', teamAId);
            } else {
                // If Team A is cleared, show all options in Team B
                resetTeamOptions('team_b', teamBId);
            }
        });

        document.getElementById('team_b').addEventListener('change', function() {
            teamBId = this.value;
            teamBName = this.options[this.selectedIndex].text;
            const nameElement = document.getElementById('toss_team_b_name');
            nameElement.textContent = teamBName;
            nameElement.style.animation = 'none';
            setTimeout(() => {
                nameElement.style.animation = 'fadeIn 0.5s ease';
            }, 10);
            
            // Filter Team A options
            if (teamBId) {
                filterTeamOptions('team_a', teamBId);
            } else {
                // If Team B is cleared, show all options in Team A
                resetTeamOptions('team_a', teamAId);
            }
        });
        
        // Function to filter team options
        function filterTeamOptions(dropdownId, excludeTeamId) {
            const dropdown = document.getElementById(dropdownId);
            const options = dropdown.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    // Keep the placeholder option
                    return;
                }
                
                if (option.value === excludeTeamId) {
                    // Hide the selected team from other dropdown
                    option.style.display = 'none';
                    option.disabled = true;
                } else {
                    // Show all other teams
                    option.style.display = '';
                    option.disabled = false;
                }
            });
            
            // If the currently selected value in this dropdown matches the excluded team, reset it
            if (dropdown.value === excludeTeamId) {
                dropdown.value = '';
                if (dropdownId === 'team_a') {
                    teamAId = '';
                    teamAName = '';
                    document.getElementById('toss_team_a_name').textContent = 'Team A';
                } else {
                    teamBId = '';
                    teamBName = '';
                    document.getElementById('toss_team_b_name').textContent = 'Team B';
                }
            }
        }
        
        // Function to reset team options (show all)
        function resetTeamOptions(dropdownId, currentlySelected) {
            const dropdown = document.getElementById(dropdownId);
            const options = dropdown.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    return;
                }
                // Show all options
                option.style.display = '';
                option.disabled = false;
            });
        }

        // Toss winner selection
        function selectTossWinner(team) {
            if ((team === 'team_a' && !teamAId) || (team === 'team_b' && !teamBId)) {
                showNotification('Please select teams first!', 'warning');
                return;
            }
            
            document.querySelectorAll('.option-card').forEach(card => {
                if (card.querySelector('#toss_team_a') || card.querySelector('#toss_team_b')) {
                    card.classList.remove('selected');
                }
            });
            
            event.currentTarget.classList.add('selected');
            
            const tossWinnerId = team === 'team_a' ? teamAId : teamBId;
            document.getElementById('toss_winner_id').value = tossWinnerId;
            document.getElementById('toss_' + team).checked = true;
            
            // Add haptic feedback (vibration on mobile)
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        }

        // Elected to selection
        function selectElectedTo(choice) {
            document.querySelectorAll('.option-card').forEach(card => {
                if (card.querySelector('#elect_bat') || card.querySelector('#elect_bowl')) {
                    card.classList.remove('selected');
                }
            });
            
            event.currentTarget.classList.add('selected');
            document.getElementById('elect_' + choice).checked = true;
            
            // Add haptic feedback
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        }

        // Coin flip functionality
        function flipCoin() {
            if (!teamAId || !teamBId) {
                showNotification('Please select both teams first!', 'warning');
                return;
            }
            
            const coin = document.getElementById('coin');
            const result = document.getElementById('coinResult');
            
            coin.classList.add('flipping');
            result.textContent = 'Flipping...';
            
            // Add haptic feedback
            if (navigator.vibrate) {
                navigator.vibrate([50, 100, 50]);
            }
            
            setTimeout(() => {
                coin.classList.remove('flipping');
                const outcome = Math.random() < 0.5 ? 'heads' : 'tails';
                const winner = outcome === 'heads' ? 'team_a' : 'team_b';
                const winnerName = winner === 'team_a' ? teamAName : teamBName;
                
                result.textContent = `🎉 ${winnerName} won the toss!`;
                
                // Auto-select the winner
                setTimeout(() => {
                    const tossCards = document.querySelectorAll('.option-card');
                    tossCards.forEach(card => {
                        if (card.querySelector('#toss_' + winner)) {
                            card.click();
                        }
                    });
                }, 500);
            }, 1200);
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'warning' ? '#F59E0B' : '#10b981'};
                color: white;
                padding: 16px 24px;
                border-radius: 12px;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
                z-index: 10000;
                font-weight: 600;
                animation: slideInRight 0.3s ease;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Form validation with better UX
        document.getElementById('matchForm').addEventListener('submit', function(e) {
            if (!teamAId || !teamBId) {
                e.preventDefault();
                showNotification('Please select both teams!', 'warning');
                document.getElementById('team_a').focus();
                return false;
            }
            
            if (teamAId === teamBId) {
                e.preventDefault();
                showNotification('Please select different teams!', 'warning');
                return false;
            }
            
            if (!document.getElementById('toss_winner_id').value) {
                e.preventDefault();
                showNotification('Please select the toss winner!', 'warning');
                return false;
            }
            
            if (!document.querySelector('input[name="elected_to"]:checked')) {
                e.preventDefault();
                showNotification('Please select bat or bowl!', 'warning');
                return false;
            }
            
            // Add loading state
            const btn = this.querySelector('.submit-btn');
            btn.classList.add('loading');
            btn.innerHTML = '<span><i class="bi bi-hourglass-split"></i> Creating Match...</span>';
        });

        // Add CSS for notification animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>