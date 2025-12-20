<?php
require_once 'config.php';

$match_id = $_GET['match_id'] ?? 0;
$innings_id = $_GET['innings_id'] ?? 0;

// Get match details
$match_sql = "SELECT m.*, 
              ta.team_name as team_a_name, 
              tb.team_name as team_b_name,
              tw.team_name as toss_winner_name
              FROM matches m
              LEFT JOIN teams ta ON m.team_a_id = ta.id
              LEFT JOIN teams tb ON m.team_b_id = tb.id
              LEFT JOIN teams tw ON m.toss_winner_id = tw.id
              WHERE m.id = ?";
$stmt = mysqli_prepare($conn, $match_sql);
mysqli_stmt_bind_param($stmt, "i", $match_id);
mysqli_stmt_execute($stmt);
$match = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get completed innings details
$innings_sql = "SELECT i.*, 
                t1.team_name as batting_team_name,
                t2.team_name as bowling_team_name
                FROM innings i
                LEFT JOIN teams t1 ON i.batting_team_id = t1.id
                LEFT JOIN teams t2 ON i.bowling_team_id = t2.id
                WHERE i.id = ?";
$stmt = mysqli_prepare($conn, $innings_sql);
mysqli_stmt_bind_param($stmt, "i", $innings_id);
mysqli_stmt_execute($stmt);
$completed_innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get all innings for this match
$all_innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY innings_number";
$stmt = mysqli_prepare($conn, $all_innings_sql);
mysqli_stmt_bind_param($stmt, "i", $match_id);
mysqli_stmt_execute($stmt);
$all_innings = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Get batsmen scores for all innings
$all_innings_batsmen = [];
$all_innings_bowlers = [];

foreach ($all_innings as $inning) {
    // Get batsmen for this innings
    $batsmen_sql = "SELECT bs.*, p.player_name 
                    FROM batsmen_scores bs
                    JOIN players p ON bs.player_id = p.id
                    WHERE bs.innings_id = ?
                    ORDER BY bs.runs DESC";
    $stmt = mysqli_prepare($conn, $batsmen_sql);
    mysqli_stmt_bind_param($stmt, "i", $inning['id']);
    mysqli_stmt_execute($stmt);
    $all_innings_batsmen[$inning['innings_number']] = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    
    // Get bowlers for this innings
    $bowler_sql = "SELECT bw.*, p.player_name 
                   FROM bowler_stats bw
                   JOIN players p ON bw.player_id = p.id
                   WHERE bw.innings_id = ?
                   ORDER BY bw.wickets DESC, bw.runs_conceded ASC";
    $stmt = mysqli_prepare($conn, $bowler_sql);
    mysqli_stmt_bind_param($stmt, "i", $inning['id']);
    mysqli_stmt_execute($stmt);
    $all_innings_bowlers[$inning['innings_number']] = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

// For backward compatibility - get current innings data
$batsmen = $all_innings_batsmen[$completed_innings['innings_number']] ?? [];
$bowlers = $all_innings_bowlers[$completed_innings['innings_number']] ?? [];

// Check if second innings exists
$second_innings_exists = count($all_innings) > 1;

// Handle start second innings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['start_second_innings'])) {
    // Create second innings
    $batting_team = $completed_innings['bowling_team_id'];
    $bowling_team = $completed_innings['batting_team_id'];
    
    $create_innings = "INSERT INTO innings (match_id, batting_team_id, bowling_team_id, innings_number) 
                      VALUES (?, ?, ?, 2)";
    $stmt = mysqli_prepare($conn, $create_innings);
    mysqli_stmt_bind_param($stmt, "iii", $match_id, $batting_team, $bowling_team);
    mysqli_stmt_execute($stmt);
    
    header("Location: scoring.php?match_id=" . $match_id);
    exit();
}

// Calculate match result if both innings completed
$match_result = null;
if (count($all_innings) == 2 && $all_innings[1]['is_completed'] == 1) {
    $first_innings_runs = $all_innings[0]['total_runs'];
    $second_innings_runs = $all_innings[1]['total_runs'];
    
    if ($second_innings_runs > $first_innings_runs) {
        $winning_team_id = $all_innings[1]['batting_team_id'];
        $margin = $second_innings_runs - $first_innings_runs;
        $match_result = [
            'winner_id' => $winning_team_id,
            'winner_name' => $all_innings[1]['batting_team_id'] == $match['team_a_id'] ? $match['team_a_name'] : $match['team_b_name'],
            'margin' => 10 - $all_innings[1]['total_wickets'] . ' wickets',
            'type' => 'wickets'
        ];
    } else if ($first_innings_runs > $second_innings_runs) {
        $winning_team_id = $all_innings[0]['batting_team_id'];
        $margin = $first_innings_runs - $second_innings_runs;
        $match_result = [
            'winner_id' => $winning_team_id,
            'winner_name' => $all_innings[0]['batting_team_id'] == $match['team_a_id'] ? $match['team_a_name'] : $match['team_b_name'],
            'margin' => $margin . ' runs',
            'type' => 'runs'
        ];
    } else {
        $match_result = [
            'winner_id' => null,
            'winner_name' => 'Match Tied',
            'margin' => '',
            'type' => 'tie'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innings Complete - Cricket Scoring</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    <script src="assets/script/sweetalert2.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-yellow: #F59E0B;
            --secondary-yellow: #FFA500;
            --light-grey: #F5F7FA;
            --medium-grey: #E2E8F0;
            --dark-grey: #64748B;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --success-green: #10B981;
            --danger-red: #EF4444;
            --info-blue: #3B82F6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(to bottom, var(--light-grey) 0%, white 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        .header {
            background: white;
            border-bottom: 1px solid var(--medium-grey);
            padding: 16px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-btn {
            background: var(--light-grey);
            border: 1px solid var(--medium-grey);
            color: var(--text-primary);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }
        
        .header-btn:hover {
            background: var(--medium-grey);
            border-color: var(--primary-yellow);
            color: var(--primary-yellow);
        }
        
        .header-title {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .result-card {
            background: linear-gradient(135deg, #F59E0B 0%, #FFA500 100%);
            color: white;
            border-radius: 24px;
            padding: 50px 30px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(245, 158, 11, 0.3);
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        
        .result-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, 20px); }
        }
        
        .result-icon {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            margin: 0 auto 25px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
        }
        
        .result-title {
            font-size: 32px;
            font-weight: 900;
            margin-bottom: 12px;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }
        
        .result-subtitle {
            font-size: 20px;
            opacity: 0.95;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .score-summary {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .score-header {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary-yellow);
        }
        
        .score-header i {
            color: var(--primary-yellow);
            font-size: 24px;
        }
        
        .team-score-box {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(255, 165, 0, 0.05) 100%);
            border: 2px solid rgba(245, 158, 11, 0.2);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .team-score-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.15);
        }
        
        .winner-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.08) 100%);
            border: 3px solid rgba(16, 185, 129, 0.4);
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.2);
            position: relative;
        }
        
        .winner-box::after {
            content: '🏆';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .winner-box:hover {
            box-shadow: 0 12px 40px rgba(16, 185, 129, 0.3);
        }
        
        .mom-section {
            background: linear-gradient(135deg, #F59E0B 0%, #FFA500 100%);
            border-radius: 24px;
            padding: 40px;
            margin: 30px 0;
            box-shadow: 0 20px 60px rgba(245, 158, 11, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .mom-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
            animation: shine 4s ease-in-out infinite;
        }
        
        @keyframes shine {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(20px, 20px) rotate(45deg); }
        }
        
        .mom-header {
            font-size: 16px;
            font-weight: 800;
            color: white;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }
        
        .mom-header i {
            font-size: 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .mom-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            display: flex;
            align-items: center;
            gap: 30px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
            border: 3px solid rgba(255, 255, 255, 0.5);
        }
        
        .mom-card::before {
            content: '⭐';
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 30px;
            opacity: 0.3;
            animation: sparkle 3s ease-in-out infinite;
        }
        
        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: rotate(0deg) scale(1); }
            50% { opacity: 0.8; transform: rotate(180deg) scale(1.3); }
        }
        
        .mom-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            flex-shrink: 0;
        }
        
        .mom-details {
            flex: 1;
            text-align: left;
        }
        
        .mom-name {
            font-size: 32px;
            font-weight: 900;
            color: var(--text-primary);
            margin-bottom: 12px;
            line-height: 1.2;
        }
        
        .mom-stats {
            font-size: 18px;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .mom-badge {
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            color: white;
            padding: 10px 22px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
            letter-spacing: 1px;
        }
        
        .team-name-display {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .score-large {
            font-size: 56px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 15px 0;
            line-height: 1;
        }
        
        .overs-text {
            font-size: 15px;
            color: var(--text-secondary);
            font-weight: 600;
            margin-top: 10px;
        }
        
        .performance-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .performance-table thead {
            background: linear-gradient(135deg, var(--light-grey), #E2E8F0);
        }
        
        .performance-table th {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--dark-grey);
            font-weight: 800;
            padding: 14px 10px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--primary-yellow);
        }
        
        .performance-table th:first-child {
            border-top-left-radius: 12px;
        }
        
        .performance-table th:last-child {
            border-top-right-radius: 12px;
        }
        
        .performance-table td {
            padding: 16px 10px;
            font-size: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
        }
        
        .performance-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .performance-table tbody tr:hover {
            background: rgba(245, 158, 11, 0.03);
        }
        
        .performance-table tr:last-child td {
            border-bottom: none;
        }
        
        .player-name-col {
            font-weight: 700;
            color: var(--text-primary);
            text-align: left;
        }
        
        .stat-col {
            text-align: center;
            font-weight: 700;
        }
        
        .stat-highlight {
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 800;
            display: inline-block;
        }
        
        .stat-danger {
            color: var(--danger-red);
            font-weight: 800;
        }
        
        .stat-success {
            color: var(--success-green);
            font-weight: 800;
        }
        
        .action-btn {
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            border: none;
            color: white;
            padding: 18px 36px;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(245, 158, 11, 0.4);
        }
        
        .secondary-btn {
            background: white;
            border: 2px solid var(--medium-grey);
            color: var(--text-primary);
            padding: 18px 36px;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .secondary-btn:hover {
            border-color: var(--primary-yellow);
            color: var(--primary-yellow);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .winner-badge {
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            display: inline-block;
            margin-left: 8px;
        }
        
        .badge-out {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-red);
        }
        
        .badge-not-out {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }
        
        .eco-rate {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 600;
        }
        
        .eco-good {
            color: var(--success-green) !important;
        }
        
        .eco-bad {
            color: var(--danger-red) !important;
        }
        
        @keyframes celebration {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.1) rotate(5deg); }
            75% { transform: scale(1.1) rotate(-5deg); }
        }
        
        .celebrate {
            animation: celebration 0.6s ease-in-out infinite;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .innings-column-separator {
            position: relative;
        }
        
        .winner-column {
            position: relative;
        }
        
        .winner-column::before {
            content: '🏆 WINNER';
            position: absolute;
            top: -10px;
            right: 15px;
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        @media (min-width: 992px) {
            .innings-column-separator::after {
                content: '';
                position: absolute;
                right: -12px;
                top: 0;
                bottom: 0;
                width: 2px;
                background: linear-gradient(to bottom, transparent, var(--primary-yellow), transparent);
            }
        }
        
        .stat-box-small {
            background: var(--light-grey);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .stat-box-small:hover {
            border-color: var(--primary-yellow);
            transform: translateY(-2px);
        }
        
        .stat-label-small {
            font-size: 11px;
            color: var(--text-secondary);
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        
        .stat-value-small {
            font-size: 24px;
            font-weight: 900;
            color: var(--text-primary);
        }
        
        @media print {
            .header-btn, .action-btn, .secondary-btn {
                display: none;
            }
        }
        
        @media (max-width: 991px) {
            .col-lg-6 {
                margin-bottom: 20px;
            }
            
            .col-md-6:last-child .team-score-box {
                margin-bottom: 0;
            }
        }
        
        @media (max-width: 768px) {
            .score-large {
                font-size: 44px;
            }
            
            .result-title {
                font-size: 26px;
            }
            
            .performance-table {
                font-size: 11px;
            }
            
            .performance-table th,
            .performance-table td {
                padding: 8px 4px;
            }
            
            .player-name-col {
                font-size: 12px;
            }
            
            .score-header {
                font-size: 16px;
            }
            
            .score-summary {
                padding: 20px;
            }
            
            .mom-card {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .mom-icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
            }
            
            .mom-details {
                text-align: center;
            }
            
            .mom-name {
                font-size: 24px;
            }
            
            .mom-stats {
                font-size: 15px;
            }
            
            .team-score-box {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <a href="index.php" class="header-btn">
                        <i class="bi bi-house-fill"></i>
                    </a>
                </div>
                <div class="col text-center">
                    <h1 class="header-title mb-0">
                        <?= $match_result ? '🏆 Match Complete' : '✅ Innings Complete' ?>
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="scoring.php?match_id=<?= $match_id ?>" class="header-btn">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Result Card -->
        <?php if ($match_result): ?>
            <div class="result-card">
                <div class="result-icon <?= $match_result['type'] != 'tie' ? 'celebrate' : '' ?>">
                    <?= $match_result['type'] == 'tie' ? '🤝' : '🏆' ?>
                </div>
                <div class="result-title">
                    <?= htmlspecialchars($match_result['winner_name']) ?>
                    <?= $match_result['type'] != 'tie' ? ' Wins!' : '' ?>
                </div>
                <?php if ($match_result['margin']): ?>
                    <div class="result-subtitle">
                        By <?= htmlspecialchars($match_result['margin']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="result-card">
                <div class="result-icon">
                    ✅
                </div>
                <div class="result-title">Innings Complete!</div>
                <div class="result-subtitle">
                    <?= htmlspecialchars($completed_innings['batting_team_name']) ?> scored 
                    <?= $completed_innings['total_runs'] ?>/<?= $completed_innings['total_wickets'] ?>
                    in <?= number_format($completed_innings['total_overs'], 1) ?> overs
                </div>
            </div>
        <?php endif; ?>

        <!-- Match Summary -->
        <div class="score-summary">
            <h3 class="score-header">
                <i class="bi bi-trophy-fill"></i>
                Match Summary
            </h3>

            <div class="row g-3">
                <?php foreach ($all_innings as $inn): ?>
                    <?php
                    $team_name = $inn['batting_team_id'] == $match['team_a_id'] ? 
                                $match['team_a_name'] : $match['team_b_name'];
                    $is_winner = $match_result && $match_result['winner_id'] == $inn['batting_team_id'];
                    $run_rate = $inn['total_overs'] > 0 ? $inn['total_runs'] / $inn['total_overs'] : 0;
                    ?>
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="team-score-box <?= $is_winner ? 'winner-box' : '' ?>">
                            <div class="team-name-display">
                                <span>🏏</span>
                                <?= htmlspecialchars($team_name) ?>
                                <?php if ($is_winner): ?>
                                    <span class="winner-badge">
                                        <i class="bi bi-trophy-fill"></i> WINNER
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="score-large">
                                <?= $inn['total_runs'] ?><span>/</span><?= $inn['total_wickets'] ?>
                            </div>
                            <div class="overs-text">
                                <i class="bi bi-clock-fill"></i> <?= number_format($inn['total_overs'], 1) ?> Overs
                                • Run Rate: <?= number_format($run_rate, 2) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($match_result): ?>
        <!-- Man of the Match -->
        <?php
        // Determine Man of the Match
        $mom_player = null;
        $mom_stats = null;
        $mom_type = null; // 'batting' or 'bowling'
        
        $all_batsmen = [];
        $all_bowlers = [];
        
        // Collect all players
        foreach ($all_innings_batsmen as $inns_batsmen) {
            foreach ($inns_batsmen as $bat) {
                $all_batsmen[] = $bat;
            }
        }
        foreach ($all_innings_bowlers as $inns_bowlers) {
            foreach ($inns_bowlers as $bowl) {
                $all_bowlers[] = $bowl;
            }
        }
        
        // Find best batsman (highest runs)
        $best_batsman = null;
        $best_runs = 0;
        foreach ($all_batsmen as $bat) {
            if ($bat['runs'] > $best_runs) {
                $best_runs = $bat['runs'];
                $best_batsman = $bat;
            }
        }
        
        // Find best bowler (most wickets, then lowest runs)
        $best_bowler = null;
        $best_wickets = 0;
        $best_runs_conceded = 999;
        foreach ($all_bowlers as $bowl) {
            if ($bowl['wickets'] > $best_wickets || 
                ($bowl['wickets'] == $best_wickets && $bowl['runs_conceded'] < $best_runs_conceded)) {
                $best_wickets = $bowl['wickets'];
                $best_runs_conceded = $bowl['runs_conceded'];
                $best_bowler = $bowl;
            }
        }
        
        // Decide MoM: if batsman scored 40+ OR bowler took 3+ wickets, or highest contribution
        if ($best_batsman && $best_runs >= 40) {
            $mom_player = $best_batsman;
            $mom_type = 'batting';
            $mom_stats = $best_runs . ' runs off ' . $best_batsman['balls'] . ' balls';
        } elseif ($best_bowler && $best_wickets >= 3) {
            $mom_player = $best_bowler;
            $mom_type = 'bowling';
            $mom_stats = $best_wickets . '/' . $best_bowler['runs_conceded'] . ' in ' . number_format($best_bowler['overs'], 1) . ' overs';
        } elseif ($best_batsman && (!$best_bowler || $best_runs > $best_wickets * 15)) {
            $mom_player = $best_batsman;
            $mom_type = 'batting';
            $mom_stats = $best_runs . ' runs off ' . $best_batsman['balls'] . ' balls';
        } elseif ($best_bowler) {
            $mom_player = $best_bowler;
            $mom_type = 'bowling';
            $mom_stats = $best_wickets . '/' . $best_bowler['runs_conceded'] . ' in ' . number_format($best_bowler['overs'], 1) . ' overs';
        }
        ?>
        
        <?php if ($mom_player): ?>
        <div class="mom-section">
            <div class="mom-header">
                <i class="bi bi-star-fill"></i>
                Player of the Match
            </div>
            <div class="mom-card">
                <div class="mom-icon">
                    <?= $mom_type == 'batting' ? '🏏' : '⚡' ?>
                </div>
                <div class="mom-details">
                    <div class="mom-name"><?= htmlspecialchars($mom_player['player_name']) ?></div>
                    <div class="mom-stats"><?= $mom_stats ?></div>
                    <div class="mom-badge">
                        <i class="bi bi-award-fill"></i> PLAYER OF THE MATCH
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Performance Statistics -->
        <?php if ($match_result): ?>
            <!-- Match Complete - Show Both Innings Side by Side -->
            <?php
                // Determine which innings won
                $first_innings_won = $match_result['winner_id'] == $all_innings[0]['batting_team_id'];
                $second_innings_won = $match_result['winner_id'] == $all_innings[1]['batting_team_id'];
            ?>
            <div class="row g-4">
                <!-- 1st Innings Performance -->
                <div class="col-lg-6 col-12 innings-column-separator">
                    <div class="score-summary">
                        <h3 class="score-header">
                            <i class="bi bi-lightning-charge-fill"></i>
                            1st Innings - <?= htmlspecialchars($all_innings[0]['batting_team_id'] == $match['team_a_id'] ? $match['team_a_name'] : $match['team_b_name']) ?>
                        </h3>
                        
                        <!-- 1st Innings Batting -->
                        <h5 style="font-size: 14px; font-weight: 800; color: var(--text-secondary); margin: 20px 0 15px 0; text-transform: uppercase;">
                            <i class="bi bi-person-fill"></i> Batting
                        </h5>
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">BATSMAN</th>
                                    <th>R</th>
                                    <th>B</th>
                                    <th>4s</th>
                                    <th>6s</th>
                                    <th>SR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $first_innings_batsmen = $all_innings_batsmen[1] ?? [];
                                $top_scorer_1 = !empty($first_innings_batsmen) ? $first_innings_batsmen[0]['runs'] : 0;
                                foreach ($first_innings_batsmen as $batsman): 
                                    $strike_rate = $batsman['balls'] > 0 ? ($batsman['runs'] / $batsman['balls']) * 100 : 0;
                                    $is_top_scorer = $batsman['runs'] == $top_scorer_1 && $top_scorer_1 > 0;
                                ?>
                                    <tr>
                                        <td class="player-name-col">
                                            <?= htmlspecialchars($batsman['player_name']) ?>
                                            <?php if ($is_top_scorer): ?>
                                                <i class="bi bi-star-fill" style="color: var(--primary-yellow); font-size: 11px;"></i>
                                            <?php endif; ?>
                                            <?php if (!$batsman['is_out']): ?>
                                                <span style="color: var(--success-green); font-size: 10px;">*</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col">
                                            <?php if ($is_top_scorer): ?>
                                                <span class="stat-highlight"><?= $batsman['runs'] ?></span>
                                            <?php else: ?>
                                                <strong><?= $batsman['runs'] ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col"><?= $batsman['balls'] ?></td>
                                        <td class="stat-col stat-success"><?= $batsman['fours'] ?></td>
                                        <td class="stat-col stat-success"><?= $batsman['sixes'] ?></td>
                                        <td class="stat-col">
                                            <span class="<?= $strike_rate > 150 ? 'stat-success' : ($strike_rate < 80 ? 'stat-danger' : '') ?>">
                                                <?= number_format($strike_rate, 0) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- 1st Innings Bowling -->
                        <?php 
                        $first_innings_bowlers = $all_innings_bowlers[1] ?? [];
                        if (!empty($first_innings_bowlers)): 
                        ?>
                        <h5 style="font-size: 14px; font-weight: 800; color: var(--text-secondary); margin: 25px 0 15px 0; text-transform: uppercase;">
                            <i class="bi bi-activity"></i> Bowling
                        </h5>
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">BOWLER</th>
                                    <th>O</th>
                                    <th>R</th>
                                    <th>W</th>
                                    <th>ECO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $best_bowler_wickets_1 = 0;
                                foreach ($first_innings_bowlers as $b) {
                                    if ($b['wickets'] > $best_bowler_wickets_1) {
                                        $best_bowler_wickets_1 = $b['wickets'];
                                    }
                                }
                                
                                foreach ($first_innings_bowlers as $bowler): 
                                    $economy = $bowler['overs'] > 0 ? $bowler['runs_conceded'] / $bowler['overs'] : 0;
                                    $is_best_bowler = $bowler['wickets'] == $best_bowler_wickets_1 && $best_bowler_wickets_1 > 0;
                                    $eco_class = '';
                                    if ($economy < 6) {
                                        $eco_class = 'eco-good';
                                    } elseif ($economy > 10) {
                                        $eco_class = 'eco-bad';
                                    }
                                ?>
                                    <tr>
                                        <td class="player-name-col">
                                            <?= htmlspecialchars($bowler['player_name']) ?>
                                            <?php if ($is_best_bowler): ?>
                                                <i class="bi bi-star-fill" style="color: var(--info-blue); font-size: 11px;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col"><?= number_format($bowler['overs'], 1) ?></td>
                                        <td class="stat-col"><?= $bowler['runs_conceded'] ?></td>
                                        <td class="stat-col">
                                            <?php if ($is_best_bowler): ?>
                                                <span class="stat-highlight"><?= $bowler['wickets'] ?></span>
                                            <?php else: ?>
                                                <strong><?= $bowler['wickets'] ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col">
                                            <span class="eco-rate <?= $eco_class ?>">
                                                <?= number_format($economy, 1) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 2nd Innings Performance -->
                <div class="col-lg-6 col-12 <?= $second_innings_won ? 'winner-column' : '' ?>">
                    <div class="score-summary">
                        <h3 class="score-header">
                            <i class="bi bi-lightning-charge-fill"></i>
                            2nd Innings - <?= htmlspecialchars($all_innings[1]['batting_team_id'] == $match['team_a_id'] ? $match['team_a_name'] : $match['team_b_name']) ?>
                        </h3>
                        
                        <!-- 2nd Innings Batting -->
                        <h5 style="font-size: 14px; font-weight: 800; color: var(--text-secondary); margin: 20px 0 15px 0; text-transform: uppercase;">
                            <i class="bi bi-person-fill"></i> Batting
                        </h5>
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">BATSMAN</th>
                                    <th>R</th>
                                    <th>B</th>
                                    <th>4s</th>
                                    <th>6s</th>
                                    <th>SR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $second_innings_batsmen = $all_innings_batsmen[2] ?? [];
                                $top_scorer_2 = !empty($second_innings_batsmen) ? $second_innings_batsmen[0]['runs'] : 0;
                                foreach ($second_innings_batsmen as $batsman): 
                                    $strike_rate = $batsman['balls'] > 0 ? ($batsman['runs'] / $batsman['balls']) * 100 : 0;
                                    $is_top_scorer = $batsman['runs'] == $top_scorer_2 && $top_scorer_2 > 0;
                                ?>
                                    <tr>
                                        <td class="player-name-col">
                                            <?= htmlspecialchars($batsman['player_name']) ?>
                                            <?php if ($is_top_scorer): ?>
                                                <i class="bi bi-star-fill" style="color: var(--primary-yellow); font-size: 11px;"></i>
                                            <?php endif; ?>
                                            <?php if (!$batsman['is_out']): ?>
                                                <span style="color: var(--success-green); font-size: 10px;">*</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col">
                                            <?php if ($is_top_scorer): ?>
                                                <span class="stat-highlight"><?= $batsman['runs'] ?></span>
                                            <?php else: ?>
                                                <strong><?= $batsman['runs'] ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col"><?= $batsman['balls'] ?></td>
                                        <td class="stat-col stat-success"><?= $batsman['fours'] ?></td>
                                        <td class="stat-col stat-success"><?= $batsman['sixes'] ?></td>
                                        <td class="stat-col">
                                            <span class="<?= $strike_rate > 150 ? 'stat-success' : ($strike_rate < 80 ? 'stat-danger' : '') ?>">
                                                <?= number_format($strike_rate, 0) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- 2nd Innings Bowling -->
                        <?php 
                        $second_innings_bowlers = $all_innings_bowlers[2] ?? [];
                        if (!empty($second_innings_bowlers)): 
                        ?>
                        <h5 style="font-size: 14px; font-weight: 800; color: var(--text-secondary); margin: 25px 0 15px 0; text-transform: uppercase;">
                            <i class="bi bi-activity"></i> Bowling
                        </h5>
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">BOWLER</th>
                                    <th>O</th>
                                    <th>R</th>
                                    <th>W</th>
                                    <th>ECO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $best_bowler_wickets_2 = 0;
                                foreach ($second_innings_bowlers as $b) {
                                    if ($b['wickets'] > $best_bowler_wickets_2) {
                                        $best_bowler_wickets_2 = $b['wickets'];
                                    }
                                }
                                
                                foreach ($second_innings_bowlers as $bowler): 
                                    $economy = $bowler['overs'] > 0 ? $bowler['runs_conceded'] / $bowler['overs'] : 0;
                                    $is_best_bowler = $bowler['wickets'] == $best_bowler_wickets_2 && $best_bowler_wickets_2 > 0;
                                    $eco_class = '';
                                    if ($economy < 6) {
                                        $eco_class = 'eco-good';
                                    } elseif ($economy > 10) {
                                        $eco_class = 'eco-bad';
                                    }
                                ?>
                                    <tr>
                                        <td class="player-name-col">
                                            <?= htmlspecialchars($bowler['player_name']) ?>
                                            <?php if ($is_best_bowler): ?>
                                                <i class="bi bi-star-fill" style="color: var(--info-blue); font-size: 11px;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col"><?= number_format($bowler['overs'], 1) ?></td>
                                        <td class="stat-col"><?= $bowler['runs_conceded'] ?></td>
                                        <td class="stat-col">
                                            <?php if ($is_best_bowler): ?>
                                                <span class="stat-highlight"><?= $bowler['wickets'] ?></span>
                                            <?php else: ?>
                                                <strong><?= $bowler['wickets'] ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td class="stat-col">
                                            <span class="eco-rate <?= $eco_class ?>">
                                                <?= number_format($economy, 1) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        
        <?php else: ?>
            <!-- Single Innings Complete - Show Current Innings Only -->
            <!-- Batting Performance -->
            <div class="score-summary">
                <h3 class="score-header">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Batting Performance
                </h3>
                
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">BATSMAN</th>
                            <th>R</th>
                            <th>B</th>
                            <th>4s</th>
                            <th>6s</th>
                            <th>SR</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $top_scorer = !empty($batsmen) ? $batsmen[0]['runs'] : 0;
                        foreach ($batsmen as $batsman): 
                            $strike_rate = $batsman['balls'] > 0 ? ($batsman['runs'] / $batsman['balls']) * 100 : 0;
                            $is_top_scorer = $batsman['runs'] == $top_scorer && $top_scorer > 0;
                        ?>
                            <tr>
                                <td class="player-name-col">
                                    <i class="bi bi-person-circle" style="color: var(--primary-yellow);"></i>
                                    <?= htmlspecialchars($batsman['player_name']) ?>
                                    <?php if ($is_top_scorer): ?>
                                        <i class="bi bi-star-fill" style="color: var(--primary-yellow); font-size: 12px;"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="stat-col">
                                    <?php if ($is_top_scorer): ?>
                                        <span class="stat-highlight"><?= $batsman['runs'] ?></span>
                                    <?php else: ?>
                                        <?= $batsman['runs'] ?>
                                    <?php endif; ?>
                                </td>
                                <td class="stat-col"><?= $batsman['balls'] ?></td>
                                <td class="stat-col stat-success"><?= $batsman['fours'] ?></td>
                                <td class="stat-col stat-success"><?= $batsman['sixes'] ?></td>
                                <td class="stat-col">
                                    <span class="<?= $strike_rate > 150 ? 'stat-success' : ($strike_rate < 80 ? 'stat-danger' : '') ?>">
                                        <?= number_format($strike_rate, 1) ?>
                                    </span>
                                </td>
                                <td class="stat-col">
                                    <?php if ($batsman['is_out']): ?>
                                        <span class="badge-out">OUT</span>
                                    <?php else: ?>
                                        <span class="badge-not-out">NOT OUT</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bowling Performance -->
            <?php if (!empty($bowlers)): ?>
            <div class="score-summary">
                <h3 class="score-header">
                    <i class="bi bi-activity"></i>
                    Bowling Performance
                </h3>
                
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">BOWLER</th>
                            <th>O</th>
                            <th>M</th>
                            <th>R</th>
                            <th>W</th>
                            <th>ECON</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $best_bowler_wickets = 0;
                        foreach ($bowlers as $b) {
                            if ($b['wickets'] > $best_bowler_wickets) {
                                $best_bowler_wickets = $b['wickets'];
                            }
                        }
                        
                        foreach ($bowlers as $bowler): 
                            $economy = $bowler['overs'] > 0 ? $bowler['runs_conceded'] / $bowler['overs'] : 0;
                            $is_best_bowler = $bowler['wickets'] == $best_bowler_wickets && $best_bowler_wickets > 0;
                            $eco_class = '';
                            if ($economy < 6) {
                                $eco_class = 'eco-good';
                            } elseif ($economy > 10) {
                                $eco_class = 'eco-bad';
                            }
                        ?>
                            <tr>
                                <td class="player-name-col">
                                    <i class="bi bi-person-circle" style="color: var(--info-blue);"></i>
                                    <?= htmlspecialchars($bowler['player_name']) ?>
                                    <?php if ($is_best_bowler): ?>
                                        <i class="bi bi-star-fill" style="color: var(--info-blue); font-size: 12px;"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="stat-col"><?= number_format($bowler['overs'], 1) ?></td>
                                <td class="stat-col"><?= $bowler['maidens'] ?></td>
                                <td class="stat-col"><?= $bowler['runs_conceded'] ?></td>
                                <td class="stat-col">
                                    <?php if ($is_best_bowler): ?>
                                        <span class="stat-highlight"><?= $bowler['wickets'] ?></span>
                                    <?php else: ?>
                                        <?= $bowler['wickets'] ?>
                                    <?php endif; ?>
                                </td>
                                <td class="stat-col">
                                    <span class="eco-rate <?= $eco_class ?>">
                                        <?= number_format($economy, 2) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Actions -->
        <div class="row g-3 mt-4">
            <?php if (!$second_innings_exists && !$match_result): ?>
                <div class="col-12">
                    <form method="POST">
                        <button type="submit" name="start_second_innings" class="action-btn">
                            <i class="bi bi-play-circle-fill"></i> Start Second Innings
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if ($match_result): ?>
                <div class="col-6">
                    <button class="action-btn" onclick="window.location.href='index.php'">
                        <i class="bi bi-house-fill"></i> Back to Home
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="col-6">
                <button class="secondary-btn" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> Print Scorecard
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>