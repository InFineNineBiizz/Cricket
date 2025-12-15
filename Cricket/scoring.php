<?php
require_once 'config.php';

$match_id = $_GET['match_id'] ?? 0;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    // Handle add batsman via AJAX
    if (isset($_POST['action']) && $_POST['action'] == 'add_batsman') {
        $player_id = $_POST['player_id'];
        
        // Get current innings
        $innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $innings_sql);
        mysqli_stmt_bind_param($stmt, "i", $match_id);
        mysqli_stmt_execute($stmt);
        $innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        // Get current batsmen count
        $batsmen_sql = "SELECT COUNT(*) as count FROM batsmen_scores WHERE innings_id = ? AND is_out = 0";
        $stmt = mysqli_prepare($conn, $batsmen_sql);
        mysqli_stmt_bind_param($stmt, "i", $innings['id']);
        mysqli_stmt_execute($stmt);
        $count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $count_batsmen = $count_result['count'];
        
        // Determine striker order
        $striker_order = $count_batsmen == 0 ? 1 : 2;
        
        // Insert new batsman
        $insert_batsman = "INSERT INTO batsmen_scores (innings_id, player_id, striker_order) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_batsman);
        mysqli_stmt_bind_param($stmt, "iii", $innings['id'], $player_id, $striker_order);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Batsman added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add batsman']);
        }
        exit();
    }
    
    // Handle add opening pair via AJAX
    if (isset($_POST['action']) && $_POST['action'] == 'add_opening_pair') {
        $striker_id = $_POST['striker_id'];
        $non_striker_id = $_POST['non_striker_id'];
        
        // Get current innings
        $innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $innings_sql);
        mysqli_stmt_bind_param($stmt, "i", $match_id);
        mysqli_stmt_execute($stmt);
        $innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        // Insert striker (striker_order = 1)
        $insert_striker = "INSERT INTO batsmen_scores (innings_id, player_id, striker_order) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($conn, $insert_striker);
        mysqli_stmt_bind_param($stmt, "ii", $innings['id'], $striker_id);
        $striker_success = mysqli_stmt_execute($stmt);
        
        // Insert non-striker (striker_order = 2)
        $insert_non_striker = "INSERT INTO batsmen_scores (innings_id, player_id, striker_order) VALUES (?, ?, 2)";
        $stmt = mysqli_prepare($conn, $insert_non_striker);
        mysqli_stmt_bind_param($stmt, "ii", $innings['id'], $non_striker_id);
        $non_striker_success = mysqli_stmt_execute($stmt);
        
        if ($striker_success && $non_striker_success) {
            echo json_encode(['success' => true, 'message' => 'Opening pair added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add opening pair']);
        }
        exit();
    }
    
    // Handle replace batsman via AJAX
    if (isset($_POST['action']) && $_POST['action'] == 'replace_batsman') {
        $old_batsman_id = $_POST['old_batsman_id'];
        $new_player_id = $_POST['new_player_id'];
        
        // Get current innings
        $innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $innings_sql);
        mysqli_stmt_bind_param($stmt, "i", $match_id);
        mysqli_stmt_execute($stmt);
        $innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        // Get the striker_order of the batsman being replaced
        $get_order_sql = "SELECT striker_order FROM batsmen_scores WHERE id = ?";
        $stmt = mysqli_prepare($conn, $get_order_sql);
        mysqli_stmt_bind_param($stmt, "i", $old_batsman_id);
        mysqli_stmt_execute($stmt);
        $old_batsman = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $striker_order = $old_batsman['striker_order'] ?? 2;
        
        // Mark old batsman as retired/out
        $retire_batsman = "UPDATE batsmen_scores SET is_out = 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $retire_batsman);
        mysqli_stmt_bind_param($stmt, "i", $old_batsman_id);
        mysqli_stmt_execute($stmt);
        
        // Add new batsman with same striker position
        $insert_new = "INSERT INTO batsmen_scores (innings_id, player_id, striker_order) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_new);
        mysqli_stmt_bind_param($stmt, "iii", $innings['id'], $new_player_id, $striker_order);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Batsman replaced successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to replace batsman']);
        }
        exit();
    }
    
    // Handle add bowler via AJAX
    if (isset($_POST['action']) && $_POST['action'] == 'add_bowler') {
        $player_id = $_POST['player_id'];
        
        // Get current innings
        $innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $innings_sql);
        mysqli_stmt_bind_param($stmt, "i", $match_id);
        mysqli_stmt_execute($stmt);
        $innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        // Check if bowlers table exists, if not create it
        $create_table = "CREATE TABLE IF NOT EXISTS bowler_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            innings_id INT NOT NULL,
            player_id INT NOT NULL,
            overs DECIMAL(3,1) DEFAULT 0.0,
            maidens INT DEFAULT 0,
            runs_conceded INT DEFAULT 0,
            wickets INT DEFAULT 0,
            is_current TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (innings_id) REFERENCES innings(id),
            FOREIGN KEY (player_id) REFERENCES players(id)
        )";
        mysqli_query($conn, $create_table);
        
        // Mark all other bowlers as not current
        $update_current = "UPDATE bowler_stats SET is_current = 0 WHERE innings_id = ?";
        $stmt = mysqli_prepare($conn, $update_current);
        mysqli_stmt_bind_param($stmt, "i", $innings['id']);
        mysqli_stmt_execute($stmt);
        
        // Insert new bowler as current
        $insert_bowler = "INSERT INTO bowler_stats (innings_id, player_id, is_current) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($conn, $insert_bowler);
        mysqli_stmt_bind_param($stmt, "ii", $innings['id'], $player_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Bowler added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add bowler']);
        }
        exit();
    }
    
    // Handle replace bowler via AJAX
    if (isset($_POST['action']) && $_POST['action'] == 'replace_bowler') {
        $new_player_id = $_POST['new_player_id'];
        
        // Get current innings
        $innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $innings_sql);
        mysqli_stmt_bind_param($stmt, "i", $match_id);
        mysqli_stmt_execute($stmt);
        $innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        // Mark all bowlers as not current
        $update_current = "UPDATE bowler_stats SET is_current = 0 WHERE innings_id = ?";
        $stmt = mysqli_prepare($conn, $update_current);
        mysqli_stmt_bind_param($stmt, "i", $innings['id']);
        mysqli_stmt_execute($stmt);
        
        // Check if this bowler has bowled before in this innings
        $check_bowler = "SELECT id FROM bowler_stats WHERE innings_id = ? AND player_id = ?";
        $stmt = mysqli_prepare($conn, $check_bowler);
        mysqli_stmt_bind_param($stmt, "ii", $innings['id'], $new_player_id);
        mysqli_stmt_execute($stmt);
        $existing_bowler = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if ($existing_bowler) {
            // Update existing bowler to current
            $update_bowler = "UPDATE bowler_stats SET is_current = 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_bowler);
            mysqli_stmt_bind_param($stmt, "i", $existing_bowler['id']);
            mysqli_stmt_execute($stmt);
        } else {
            // Insert new bowler
            $insert_bowler = "INSERT INTO bowler_stats (innings_id, player_id, is_current) VALUES (?, ?, 1)";
            $stmt = mysqli_prepare($conn, $insert_bowler);
            mysqli_stmt_bind_param($stmt, "ii", $innings['id'], $new_player_id);
            mysqli_stmt_execute($stmt);
        }
        
        echo json_encode(['success' => true, 'message' => 'Bowler replaced successfully']);
        exit();
    }
    
    // Get or create current innings
    $innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $innings_sql);
    mysqli_stmt_bind_param($stmt, "i", $match_id);
    mysqli_stmt_execute($stmt);
    $innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    // Get current batsmen
    $batsmen_sql = "SELECT bs.*, p.player_name FROM batsmen_scores bs 
                    JOIN players p ON bs.player_id = p.id 
                    WHERE bs.innings_id = ? AND bs.is_out = 0 
                    ORDER BY COALESCE(bs.striker_order, bs.id) ASC LIMIT 2";
    $stmt = mysqli_prepare($conn, $batsmen_sql);
    mysqli_stmt_bind_param($stmt, "i", $innings['id']);
    mysqli_stmt_execute($stmt);
    $current_batsmen = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    
    // Check if we have 2 batsmen
    if (count($current_batsmen) < 2) {
        echo json_encode(['success' => false, 'error' => 'Please add 2 batsmen first!']);
        exit();
    }
    
    // Get current bowler
    $bowler_check_sql = "SELECT * FROM bowler_stats WHERE innings_id = ? AND is_current = 1 LIMIT 1";
    $stmt = mysqli_prepare($conn, $bowler_check_sql);
    mysqli_stmt_bind_param($stmt, "i", $innings['id']);
    mysqli_stmt_execute($stmt);
    $current_bowler_check = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    // Check if we have a bowler
    if (!$current_bowler_check) {
        echo json_encode(['success' => false, 'error' => 'Please add a bowler first!']);
        exit();
    }
    
    $runs = intval($_POST['runs']);
    $is_wicket = isset($_POST['is_wicket']) && $_POST['is_wicket'] == '1' ? 1 : 0;
    $extras_type = $_POST['extras_type'] ?? '';
    $extra_runs = $extras_type ? intval($_POST['extra_runs'] ?? 0) : 0;
    
    // Calculate total runs to add
    $total_runs_add = $runs + $extra_runs;
    
    // Calculate new overs value
    $current_overs = $innings['total_overs'];
    $whole_overs = floor($current_overs);
    $balls_in_over = round(($current_overs - $whole_overs) * 10);
    
    // Track if striker should change
    $rotate_strike = false;
    
    // Only increment ball count if it's not a wide or no-ball
    $new_overs = $current_overs;
    if ($extras_type != 'WD' && $extras_type != 'NB') {
        $balls_in_over++;
        
        // Check if over is completed (6 balls)
        if ($balls_in_over >= 6) {
            $whole_overs++;
            $balls_in_over = 0;
            $rotate_strike = true; // Rotate strike at end of over
        }
        
        $new_overs = $whole_overs + ($balls_in_over / 10);
    }
    
    // Rotate strike if odd runs scored (1, 3, 5, etc.) or if over completed
    if (!in_array($extras_type, ['BYE', 'LB']) && $runs % 2 != 0) {
        $rotate_strike = true;
    } elseif (in_array($extras_type, ['BYE', 'LB']) && ($runs + $extra_runs) % 2 != 0) {
        $rotate_strike = true;
    }
    
    // Check if target is chased (for second innings)
    $target_chased = false;
    if ($innings['innings_number'] == 2) {
        $first_innings_sql = "SELECT total_runs FROM innings WHERE match_id = ? AND innings_number = 1";
        $stmt = mysqli_prepare($conn, $first_innings_sql);
        mysqli_stmt_bind_param($stmt, "i", $match_id);
        mysqli_stmt_execute($stmt);
        $first_innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if ($first_innings) {
            $target = $first_innings['total_runs'] + 1;
            $new_score = $innings['total_runs'] + $total_runs_add;
            
            if ($new_score >= $target) {
                $target_chased = true;
            }
        }
    }
    
    // Check if innings should end
    $innings_completed = 0;
    if (($whole_overs >= 6 && $balls_in_over == 0) || $target_chased) {
        $innings_completed = 1;
    }
    
    // Update innings totals
    $update_innings = "UPDATE innings SET 
                       total_runs = total_runs + ?, 
                       total_wickets = total_wickets + ?,
                       total_overs = ?,
                       is_completed = ?
                       WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_innings);
    mysqli_stmt_bind_param($stmt, "iidii", $total_runs_add, $is_wicket, $new_overs, $innings_completed, $innings['id']);
    mysqli_stmt_execute($stmt);
    
    // Update batsman score
    $striker_id = $current_batsmen[0]['id'];
    
    if ($extras_type != 'WD') {
        // Determine if this counts as a ball faced
        $balls_add = ($extras_type == 'NB') ? 0 : 1;
        
        // Count fours and sixes only for actual runs by batsman (not byes/leg byes)
        $fours_add = ($runs == 4 && !in_array($extras_type, ['BYE', 'LB'])) ? 1 : 0;
        $sixes_add = ($runs == 6 && !in_array($extras_type, ['BYE', 'LB'])) ? 1 : 0;
        
        // Only add runs to batsman if it's not byes or leg byes
        $batsman_runs = in_array($extras_type, ['BYE', 'LB']) ? 0 : $runs;
        
        $update_batsman = "UPDATE batsmen_scores SET 
                          runs = runs + ?, 
                          balls = balls + ?, 
                          fours = fours + ?, 
                          sixes = sixes + ?,
                          is_out = ?
                          WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_batsman);
        mysqli_stmt_bind_param($stmt, "iiiiii", $batsman_runs, $balls_add, $fours_add, $sixes_add, $is_wicket, $striker_id);
        mysqli_stmt_execute($stmt);
    }
    
    // Rotate strike if needed (swap striker positions)
    if ($rotate_strike && !$is_wicket && count($current_batsmen) == 2) {
        // Get both batsmen IDs
        $batsman1_id = $current_batsmen[0]['id'];
        $batsman2_id = $current_batsmen[1]['id'];
        
        // Swap their positions
        $swap1 = "UPDATE batsmen_scores SET striker_order = 2 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $swap1);
        mysqli_stmt_bind_param($stmt, "i", $batsman1_id);
        mysqli_stmt_execute($stmt);
        
        $swap2 = "UPDATE batsmen_scores SET striker_order = 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $swap2);
        mysqli_stmt_bind_param($stmt, "i", $batsman2_id);
        mysqli_stmt_execute($stmt);
    }
    
    // Update bowler stats
    $get_current_bowler = "SELECT * FROM bowler_stats WHERE innings_id = ? AND is_current = 1 LIMIT 1";
    $stmt = mysqli_prepare($conn, $get_current_bowler);
    mysqli_stmt_bind_param($stmt, "i", $innings['id']);
    mysqli_stmt_execute($stmt);
    $current_bowler_stat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($current_bowler_stat) {
        // Calculate bowler's overs separately
        $bowler_overs = $current_bowler_stat['overs'];
        $bowler_whole_overs = floor($bowler_overs);
        $bowler_balls_in_over = round(($bowler_overs - $bowler_whole_overs) * 10);
        
        // Only increment bowler overs if it's a legal delivery (not wide or no-ball)
        if ($extras_type != 'WD' && $extras_type != 'NB') {
            $bowler_balls_in_over++;
            
            // Check if bowler's over is completed
            if ($bowler_balls_in_over >= 6) {
                $bowler_whole_overs++;
                $bowler_balls_in_over = 0;
            }
            
            $bowler_overs = $bowler_whole_overs + ($bowler_balls_in_over / 10);
        }
        
        // Update runs conceded and wickets
        $update_bowler = "UPDATE bowler_stats SET 
                         overs = ?,
                         runs_conceded = runs_conceded + ?,
                         wickets = wickets + ?
                         WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_bowler);
        mysqli_stmt_bind_param($stmt, "diii", $bowler_overs, $total_runs_add, $is_wicket, $current_bowler_stat['id']);
        mysqli_stmt_execute($stmt);
    }
    
    // Check if over is completed (end of innings over, not bowler's over)
    $over_completed = false;
    if ($extras_type != 'WD' && $extras_type != 'NB' && $balls_in_over == 0 && $whole_overs > 0) {
        $over_completed = true;
    }
    
    // Check if we need to add a new batsman (wicket taken and less than 2 batsmen after wicket)
    $need_new_batsman = false;
    if ($is_wicket) {
        // Count current batsmen after wicket
        $count_batsmen_sql = "SELECT COUNT(*) as count FROM batsmen_scores WHERE innings_id = ? AND is_out = 0";
        $stmt = mysqli_prepare($conn, $count_batsmen_sql);
        mysqli_stmt_bind_param($stmt, "i", $innings['id']);
        mysqli_stmt_execute($stmt);
        $count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if ($count_result['count'] < 2) {
            $need_new_batsman = true;
        }
    }
    
    // Return updated data
    echo json_encode([
        'success' => true,
        'innings_completed' => $innings_completed,
        'over_completed' => $over_completed,
        'wicket_taken' => $need_new_batsman,
        'redirect_url' => $innings_completed ? "innings_complete.php?match_id=$match_id&innings_id={$innings['id']}" : null
    ]);
    exit();
}

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

if (!$match) {
    die("Match not found");
}

// Get or create current innings
$innings_sql = "SELECT * FROM innings WHERE match_id = ? ORDER BY id DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $innings_sql);
mysqli_stmt_bind_param($stmt, "i", $match_id);
mysqli_stmt_execute($stmt);
$innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$innings) {
    // Create first innings
    $batting_team = $match['elected_to'] == 'bat' ? $match['toss_winner_id'] : 
                   ($match['toss_winner_id'] == $match['team_a_id'] ? $match['team_b_id'] : $match['team_a_id']);
    $bowling_team = $batting_team == $match['team_a_id'] ? $match['team_b_id'] : $match['team_a_id'];
    
    $create_innings = "INSERT INTO innings (match_id, batting_team_id, bowling_team_id, innings_number) VALUES (?, ?, ?, 1)";
    $stmt = mysqli_prepare($conn, $create_innings);
    mysqli_stmt_bind_param($stmt, "iii", $match_id, $batting_team, $bowling_team);
    mysqli_stmt_execute($stmt);
    $innings_id = mysqli_insert_id($conn);
    
    // Reload innings
    $stmt = mysqli_prepare($conn, $innings_sql);
    mysqli_stmt_bind_param($stmt, "i", $match_id);
    mysqli_stmt_execute($stmt);
    $innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Get batting team name
$batting_team_sql = "SELECT team_name FROM teams WHERE id = ?";
$stmt = mysqli_prepare($conn, $batting_team_sql);
mysqli_stmt_bind_param($stmt, "i", $innings['batting_team_id']);
mysqli_stmt_execute($stmt);
$batting_team_name = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['team_name'];

// Get bowling team name
$bowling_team_sql = "SELECT team_name FROM teams WHERE id = ?";
$stmt = mysqli_prepare($conn, $bowling_team_sql);
mysqli_stmt_bind_param($stmt, "i", $innings['bowling_team_id']);
mysqli_stmt_execute($stmt);
$bowling_team_name = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['team_name'];

// Get players from batting team who haven't batted yet (not in batsmen_scores at all for this innings)
$players_sql = "SELECT p.* FROM players p 
                WHERE p.team_id = ? 
                AND p.id NOT IN (
                    SELECT player_id FROM batsmen_scores 
                    WHERE innings_id = ?
                )
                ORDER BY p.player_name";
$stmt = mysqli_prepare($conn, $players_sql);
mysqli_stmt_bind_param($stmt, "ii", $innings['batting_team_id'], $innings['id']);
mysqli_stmt_execute($stmt);
$players = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Get players available for replacement (only players who haven't batted yet)
$replacement_players_sql = "SELECT p.* FROM players p 
                            WHERE p.team_id = ? 
                            AND p.id NOT IN (
                                SELECT player_id FROM batsmen_scores 
                                WHERE innings_id = ?
                            )
                            ORDER BY p.player_name";
$stmt = mysqli_prepare($conn, $replacement_players_sql);
mysqli_stmt_bind_param($stmt, "ii", $innings['batting_team_id'], $innings['id']);
mysqli_stmt_execute($stmt);
$replacement_players = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Get bowlers from bowling team
$bowlers_sql = "SELECT p.* FROM players p 
                WHERE p.team_id = ? 
                ORDER BY p.player_name";
$stmt = mysqli_prepare($conn, $bowlers_sql);
mysqli_stmt_bind_param($stmt, "i", $innings['bowling_team_id']);
mysqli_stmt_execute($stmt);
$bowlers = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Get current bowler
$current_bowler_sql = "SELECT bs.*, p.player_name FROM bowler_stats bs 
                       JOIN players p ON bs.player_id = p.id 
                       WHERE bs.innings_id = ? AND bs.is_current = 1 
                       LIMIT 1";
$stmt = mysqli_prepare($conn, $current_bowler_sql);
mysqli_stmt_bind_param($stmt, "i", $innings['id']);
mysqli_stmt_execute($stmt);
$current_bowler = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get current batsmen
$batsmen_sql = "SELECT bs.*, p.player_name FROM batsmen_scores bs 
                JOIN players p ON bs.player_id = p.id 
                WHERE bs.innings_id = ? AND bs.is_out = 0 
                ORDER BY COALESCE(bs.striker_order, bs.id) ASC LIMIT 2";
$stmt = mysqli_prepare($conn, $batsmen_sql);
mysqli_stmt_bind_param($stmt, "i", $innings['id']);
mysqli_stmt_execute($stmt);
$current_batsmen = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Handle add batsman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_batsman'])) {
    $player_id = $_POST['player_id'];
    
    // Determine striker order based on current batsmen count
    $count_batsmen = count($current_batsmen);
    $striker_order = $count_batsmen == 0 ? 1 : 2;
    
    $insert_batsman = "INSERT INTO batsmen_scores (innings_id, player_id, striker_order) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_batsman);
    mysqli_stmt_bind_param($stmt, "iii", $innings['id'], $player_id, $striker_order);
    mysqli_stmt_execute($stmt);
    
    header("Location: scoring.php?match_id=" . $match_id);
    exit();
}

// Handle replace batsman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['replace_batsman'])) {
    $old_batsman_id = $_POST['old_batsman_id'];
    $new_player_id = $_POST['new_player_id'];
    
    // Get the striker_order of the batsman being replaced
    $get_order_sql = "SELECT striker_order FROM batsmen_scores WHERE id = ?";
    $stmt = mysqli_prepare($conn, $get_order_sql);
    mysqli_stmt_bind_param($stmt, "i", $old_batsman_id);
    mysqli_stmt_execute($stmt);
    $old_batsman = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $striker_order = $old_batsman['striker_order'] ?? 2;
    
    // Mark old batsman as retired/out
    $retire_batsman = "UPDATE batsmen_scores SET is_out = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $retire_batsman);
    mysqli_stmt_bind_param($stmt, "i", $old_batsman_id);
    mysqli_stmt_execute($stmt);
    
    // Add new batsman with same striker position
    $insert_new = "INSERT INTO batsmen_scores (innings_id, player_id, striker_order) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_new);
    mysqli_stmt_bind_param($stmt, "iii", $innings['id'], $new_player_id, $striker_order);
    mysqli_stmt_execute($stmt);
    
    header("Location: scoring.php?match_id=" . $match_id);
    exit();
}

// Reload innings for display
$stmt = mysqli_prepare($conn, $innings_sql);
mysqli_stmt_bind_param($stmt, "i", $match_id);
mysqli_stmt_execute($stmt);
$innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Check if innings is completed
if ($innings['is_completed'] == 1) {
    header("Location: innings_complete.php?match_id=" . $match_id . "&innings_id=" . $innings['id']);
    exit();
}

// Reload batsmen
$stmt = mysqli_prepare($conn, $batsmen_sql);
mysqli_stmt_bind_param($stmt, "i", $innings['id']);
mysqli_stmt_execute($stmt);
$current_batsmen = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($batting_team_name) ?> - Live Scoring</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-yellow: #F59E0B;
            --secondary-yellow: #FFA500;
            --accent-yellow: #FBBF24;
            --light-grey: #F5F7FA;
            --medium-grey: #E2E8F0;
            --dark-grey: #64748B;
            --text-primary: #2D3748;
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
            background: var(--light-grey);
            color: var(--text-primary);
            overflow-x: hidden;
        }
        
        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid var(--medium-grey);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
        }
        
        .header-btn:hover {
            background: var(--medium-grey);
            border-color: var(--primary-yellow);
        }
        
        .header-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        /* Scoreboard */
        .scoreboard-section {
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            padding: 30px 20px;
            color: white;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
        }
        
        .team-badge {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .team-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .toss-info {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .score-display {
            font-size: 64px;
            font-weight: 800;
            line-height: 1;
            margin: 15px 0;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .overs-display {
            font-size: 20px;
            font-weight: 600;
            opacity: 0.95;
        }
        
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-label {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: 700;
            margin-top: 4px;
        }

        /* Over Progress Indicator */
        .over-progress {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .over-info {
            color: white;
            font-weight: 600;
        }
        
        .balls-indicator {
            display: flex;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        .ball-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }
        
        .ball-dot.completed {
            background: white;
            border-color: white;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        /* Batsmen Section */
        .batsmen-section {
            background: white;
            padding: 20px;
            margin-top: -15px;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 10;
        }
        
        .batsmen-section .container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        #actionButtonsRow {
            position: relative;
            z-index: 100;
            pointer-events: auto;
        }
        
        #actionButtonsRow .col-6,
        #actionButtonsRow .col-12 {
            position: relative;
            z-index: 100;
        }
        
        .section-header {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-batsman-btn {
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            border: none;
            color: white;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
            pointer-events: auto !important;
            position: relative;
            z-index: 1;
        }
        
        .add-batsman-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
        }
        
        .add-batsman-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: auto !important;
        }
        
        .batsman-card {
            background: var(--light-grey);
            border: 2px solid var(--medium-grey);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }
        
        .batsman-card:hover {
            border-color: var(--primary-yellow);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.1);
        }
        
        .batsman-card.striker {
            border-color: var(--primary-yellow);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(255, 165, 0, 0.05));
        }
        
        .batsman-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .batsman-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .striker-badge {
            background: var(--primary-yellow);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .replace-btn {
            background: white;
            border: 1px solid var(--medium-grey);
            color: var(--text-secondary);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .replace-btn:hover {
            border-color: var(--primary-yellow);
            color: var(--primary-yellow);
        }
        
        .batsman-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .stat-box {
            text-align: center;
            padding: 8px;
            background: white;
            border-radius: 8px;
        }
        
        .stat-box-label {
            font-size: 11px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .stat-box-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 2px;
        }
        
        /* Scoring Pad */
        .scoring-pad {
            background: white;
            padding: 16px;
            border-top: 3px solid var(--primary-yellow);
        }

        .pad-section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pad-btn {
            height: 60px;
            border: 2px solid var(--medium-grey);
            border-radius: 10px;
            font-size: 22px;
            font-weight: 700;
            cursor: pointer;
            background: white;
            color: var(--text-primary);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pad-btn:active {
            transform: scale(0.95);
        }

        .pad-btn:hover {
            border-color: var(--primary-yellow);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        }
        
        .pad-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pad-btn.four {
            background: linear-gradient(135deg, #DBEAFE, #BFDBFE);
            border-color: var(--info-blue);
            color: var(--info-blue);
        }

        .pad-btn.six {
            background: linear-gradient(135deg, #FCE7F3, #FBCFE8);
            border-color: #EC4899;
            color: #EC4899;
        }

        .pad-btn.out {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            border-color: var(--danger-red);
            color: var(--danger-red);
        }

        .pad-btn.undo {
            background: linear-gradient(135deg, #FEF3C7, #FDE68A);
            border-color: #F59E0B;
            color: #D97706;
        }

        .pad-btn.special {
            font-size: 13px;
            font-weight: 700;
        }

        .extra-btn {
            padding: 10px;
            border: 2px solid var(--medium-grey);
            border-radius: 10px;
            background: white;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            height: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .extra-btn:hover {
            border-color: var(--primary-yellow);
            background: var(--light-grey);
        }

        .extra-btn:active {
            transform: scale(0.97);
        }
        
        .extra-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Loading Spinner */
        .loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            z-index: 9999;
            display: none;
            pointer-events: none;
        }
        
        .loading-spinner.show {
            display: block;
            pointer-events: all;
        }
        
        .spinner-border {
            width: 2rem;
            height: 2rem;
            border-width: 0.2rem;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .pad-btn {
                height: 50px;
                font-size: 18px;
            }
            
            .pad-btn.special {
                font-size: 11px;
            }
            
            .extra-btn {
                height: 50px;
                font-size: 11px;
                padding: 8px;
            }
        }
        
        /* Modal */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            color: white;
            border-radius: 20px 20px 0 0;
            border: none;
            padding: 20px 24px;
        }
        
        .modal-title {
            font-weight: 700;
            font-size: 20px;
        }
        
        .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .player-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .player-item {
            padding: 16px 20px;
            border-bottom: 1px solid var(--medium-grey);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: auto;
        }
        
        .player-item:hover {
            background: var(--light-grey);
        }
        
        .player-item:last-child {
            border-bottom: none;
        }
        
        .player-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .select-icon {
            color: var(--primary-yellow);
            font-size: 20px;
        }
        
        /* Animations */
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
        
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        
        /* Opening Pair Modal Styles */
        .selection-box {
            background: var(--light-grey);
            border: 2px solid var(--medium-grey);
            border-radius: 16px;
            padding: 20px;
            min-height: 200px;
        }
        
        .selection-header {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: space-between;
        }
        
        .selection-header i {
            color: var(--primary-yellow);
        }
        
        .player-selection-area {
            background: white;
            border: 2px dashed var(--medium-grey);
            border-radius: 12px;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .player-selection-area.has-selection {
            border-style: solid;
            border-color: var(--primary-yellow);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(255, 165, 0, 0.05));
        }
        
        .selected-player-card {
            padding: 20px;
            text-align: center;
            width: 100%;
        }
        
        .selected-player-card .player-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-yellow), var(--secondary-yellow));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: white;
            font-size: 28px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .selected-player-card .player-name-text {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .selected-player-card .remove-btn {
            background: var(--danger-red);
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }
        
        .selected-player-card .remove-btn:hover {
            background: #DC2626;
            transform: translateY(-1px);
        }
        
        .opening-player-item.selected {
            background: var(--light-grey);
            opacity: 0.5;
            pointer-events: none;
        }
        
        .opening-player-item.selected .select-icon {
            color: var(--success-green);
        }
        
        .opening-player-item.selected .select-icon i::before {
            content: "\f26b"; /* checkmark icon */
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .score-display {
                font-size: 52px;
            }
            
            .team-name {
                font-size: 20px;
            }
            
            .modal-lg {
                max-width: 100%;
                margin: 0;
            }
            
            .selection-box {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="text-center">
            <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Updating score...</div>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <button class="header-btn" onclick="window.location.href='index.php'">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                </div>
                <div class="col text-center">
                    <h1 class="header-title mb-0">Live Match Scoring</h1>
                </div>
                <div class="col-auto">
                    <button class="header-btn" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="bi bi-gear-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scoreboard -->
    <div class="scoreboard-section" id="scoreboardSection">
        <div class="container text-center">
            <div class="team-badge">
                🏏
            </div>
            <div class="team-name"><?= htmlspecialchars($batting_team_name) ?></div>
            <div class="toss-info">
                <i class="bi bi-info-circle"></i> 
                <?= htmlspecialchars($match['toss_winner_name']) ?> won the toss and elected to <?= $match['elected_to'] ?>
            </div>
            
            <div class="score-display">
                <span id="totalRuns"><?= $innings['total_runs'] ?></span><span style="opacity: 0.8;">/</span><span id="totalWickets"><?= $innings['total_wickets'] ?></span>
            </div>
            
            <div class="overs-display">
                <i class="bi bi-clock"></i> <span id="totalOvers"><?= number_format($innings['total_overs'], 1) ?></span> Overs
            </div>
            
            <?php
            // Calculate current over and ball
            $current_overs = $innings['total_overs'];
            $whole_overs = floor($current_overs);
            $balls_in_over = round(($current_overs - $whole_overs) * 10);
            
            // Get first innings score if this is second innings
            $target = null;
            $required_runs = null;
            $required_rate = null;
            if ($innings['innings_number'] == 2) {
                $first_innings_sql = "SELECT total_runs FROM innings WHERE match_id = ? AND innings_number = 1";
                $stmt = mysqli_prepare($conn, $first_innings_sql);
                mysqli_stmt_bind_param($stmt, "i", $match_id);
                mysqli_stmt_execute($stmt);
                $first_innings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                
                if ($first_innings) {
                    $target = $first_innings['total_runs'] + 1;
                    $required_runs = $target - $innings['total_runs'];
                    $overs_left = 6 - $innings['total_overs'];
                    $required_rate = $overs_left > 0 ? ($required_runs / $overs_left) : 0;
                }
            }
            ?>
            
            <!-- Over Progress Indicator -->
            <div class="over-progress mt-3">
                <div class="over-info mb-2">
                    <span style="font-size: 14px; opacity: 0.9;">Over <span id="currentOver"><?= $whole_overs + 1 ?></span> • Ball <span id="currentBall"><?= $balls_in_over + 1 ?></span></span>
                </div>
                <div class="balls-indicator" id="ballsIndicator">
                    <?php for($i = 1; $i <= 6; $i++): ?>
                        <div class="ball-dot <?= $i <= $balls_in_over ? 'completed' : '' ?>" data-ball="<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <?php
            $overs_remaining = 6 - $whole_overs;
            if ($overs_remaining <= 1 && $overs_remaining > 0): ?>
            <div class="mt-2" id="lastOverWarning">
                <span style="font-size: 13px; opacity: 0.9; background: rgba(239, 68, 68, 0.2); padding: 6px 12px; border-radius: 8px;">
                    ⚠️ Last Over!
                </span>
            </div>
            <?php endif; ?>
            
            <div class="stats-row" id="statsRow">
                <?php if ($target): ?>
                    <div class="stat-item">
                        <div class="stat-label">Target</div>
                        <div class="stat-value"><?= $target ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Required</div>
                        <div class="stat-value" id="requiredRuns" style="color: <?= $required_runs <= 0 ? '#10B981' : 'white' ?>">
                            <?= $required_runs > 0 ? $required_runs : 'Won!' ?>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Req Rate</div>
                        <div class="stat-value" id="requiredRate"><?= number_format($required_rate, 2) ?></div>
                    </div>
                <?php else: ?>
                    <div class="stat-item">
                        <div class="stat-label">Run Rate</div>
                        <div class="stat-value" id="runRate"><?= $innings['total_overs'] > 0 ? number_format($innings['total_runs'] / $innings['total_overs'], 2) : '0.00' ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Projected</div>
                        <div class="stat-value" id="projected">
                            <?php 
                            if ($innings['total_overs'] > 0) {
                                $run_rate = $innings['total_runs'] / $innings['total_overs'];
                                $projected = round($run_rate * 6);
                                echo $projected;
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Overs Left</div>
                        <div class="stat-value" id="oversLeft">
                            <?php 
                            // Calculate overs left properly
                            $total_balls = 36; // 6 overs = 36 balls
                            $current_overs = $innings['total_overs'];
                            $whole_overs_bowled = floor($current_overs);
                            $balls_in_current_over = round(($current_overs - $whole_overs_bowled) * 10);
                            
                            // Total balls bowled
                            $balls_bowled = ($whole_overs_bowled * 6) + $balls_in_current_over;
                            
                            // Remaining balls
                            $balls_remaining = $total_balls - $balls_bowled;
                            
                            // Convert to overs.balls format
                            $overs_remaining = floor($balls_remaining / 6);
                            $balls_in_over_remaining = $balls_remaining % 6;
                            
                            $overs_left = $overs_remaining + ($balls_in_over_remaining / 10);
                            echo number_format($overs_left, 1);
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Batsmen Section -->
    <div class="batsmen-section" id="batsmenSection">
        <div class="container" id="batsmenContainer">
            <h3 class="section-header">
                <i class="bi bi-people-fill"></i>
                Current Batsmen & Bowler
            </h3>

            <div id="alertsSection">
            <?php if (isset($_GET['error']) && $_GET['error'] == 'need_batsmen'): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Cannot Score!</strong> Please add 2 batsmen before scoring.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!$current_bowler): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>No Bowler!</strong> Please add a bowler to start scoring.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            </div>
            
            <!-- Add Batsman and Bowler Buttons -->
            <div class="row g-2 mb-3" id="actionButtonsRow" style="position: relative; z-index: 100;">
                <?php if (count($current_batsmen) < 2): ?>
                <!-- Show both buttons in col-6 when less than 2 batsmen -->
                <div class="col-6" style="position: relative; z-index: 100;">
                    <button class="add-batsman-btn" type="button" id="addBatsmanBtn" onclick="openAddBatsmanModal(); return false;" style="position: relative; z-index: 100;">
                        <i class="bi bi-plus-circle"></i> Add Batsman
                    </button>
                </div>
                <div class="col-6" style="position: relative; z-index: 100;">
                    <button class="add-batsman-btn" type="button" onclick="<?= $current_bowler ? 'openReplaceBowlerModal()' : 'openAddBowlerModal()' ?>; return false;" style="position: relative; z-index: 100;">
                        <i class="bi bi-plus-circle"></i> <?= $current_bowler ? 'Change Bowler' : 'Add Bowler' ?>
                    </button>
                </div>
                <?php else: ?>
                <!-- Show only Change Bowler in full width when 2 batsmen present -->
                <div class="col-12" style="position: relative; z-index: 100;">
                    <button class="add-batsman-btn" type="button" onclick="<?= $current_bowler ? 'openReplaceBowlerModal()' : 'openAddBowlerModal()' ?>; return false;" style="position: relative; z-index: 100;">
                        <i class="bi bi-plus-circle"></i> <?= $current_bowler ? 'Change Bowler' : 'Add Bowler' ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Current Bowler Card -->
            <div id="bowlerCardSection">
            <?php if ($current_bowler): ?>
            <div class="row mb-3" id="bowlerCard" data-bowler-name="<?= htmlspecialchars($current_bowler['player_name']) ?>">
                <div class="col-12 fade-in">
                    <div class="batsman-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(37, 99, 235, 0.08)); border-color: #3B82F6;">
                        <div class="batsman-header">
                            <div class="batsman-name">
                                <i class="bi bi-person-badge" style="color: #3B82F6;"></i>
                                <span class="batsman-name-text"><?= htmlspecialchars($current_bowler['player_name']) ?></span>
                                <span class="striker-badge" style="background: #3B82F6;">Bowling</span>
                            </div>
                            <button class="replace-btn" style="border-color: #3B82F6; color: #3B82F6;" onclick="openReplaceBowlerModal()">
                                <i class="bi bi-arrow-repeat"></i> Change
                            </button>
                        </div>
                        
                        <div class="batsman-stats">
                            <div class="stat-box">
                                <div class="stat-box-label">Overs</div>
                                <div class="stat-box-value bowler-overs" data-value="<?= number_format($current_bowler['overs'], 1) ?>"><?= number_format($current_bowler['overs'], 1) ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Runs</div>
                                <div class="stat-box-value bowler-runs" data-value="<?= $current_bowler['runs_conceded'] ?>"><?= $current_bowler['runs_conceded'] ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Wickets</div>
                                <div class="stat-box-value bowler-wickets" data-value="<?= $current_bowler['wickets'] ?>"><?= $current_bowler['wickets'] ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Maidens</div>
                                <div class="stat-box-value bowler-maidens" data-value="<?= $current_bowler['maidens'] ?>"><?= $current_bowler['maidens'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            </div>

            <div class="row" id="batsmenCards">
                <?php foreach ($current_batsmen as $index => $batsman): ?>
                <div class="col-12 fade-in">
                    <div class="batsman-card <?= $index === 0 ? 'striker' : '' ?>" data-batsman-id="<?= $batsman['id'] ?>">
                        <div class="batsman-header">
                            <div class="batsman-name">
                                <span class="batsman-name-text"><?= htmlspecialchars($batsman['player_name']) ?></span>
                                <?php if ($index === 0): ?>
                                    <span class="striker-badge">Striker</span>
                                <?php endif; ?>
                            </div>
                            <button class="replace-btn" onclick="openReplaceModal(<?= $batsman['id'] ?>, <?= $batsman['player_id'] ?>, '<?= htmlspecialchars($batsman['player_name'], ENT_QUOTES) ?>', <?= $batsman['runs'] ?>, <?= $batsman['balls'] ?>)">
                                <i class="bi bi-arrow-repeat"></i> Replace
                            </button>
                        </div>
                        
                        <div class="batsman-stats">
                            <div class="stat-box">
                                <div class="stat-box-label">Runs</div>
                                <div class="stat-box-value batsman-runs"><?= $batsman['runs'] ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Balls</div>
                                <div class="stat-box-value batsman-balls"><?= $batsman['balls'] ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">4s</div>
                                <div class="stat-box-value batsman-fours"><?= $batsman['fours'] ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">6s</div>
                                <div class="stat-box-value batsman-sixes"><?= $batsman['sixes'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Scoring Pad -->
    <div class="scoring-pad">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="pad-section-title">
                        <i class="bi bi-calculator"></i> Quick Scoring
                    </h4>
                </div>
            </div>
            
            <!-- Runs Buttons -->
            <div class="row g-3 mb-3">
                <div class="col-3">
                    <button type="button" class="pad-btn w-100" onclick="submitRuns(0)">0</button>
                </div>
                <div class="col-3">
                    <button type="button" class="pad-btn w-100" onclick="submitRuns(1)">1</button>
                </div>
                <div class="col-3">
                    <button type="button" class="pad-btn w-100" onclick="submitRuns(2)">2</button>
                </div>
                <div class="col-3">
                    <button type="button" class="pad-btn w-100" onclick="submitRuns(3)">3</button>
                </div>
                <div class="col-3">
                    <button type="button" class="pad-btn four w-100 special" onclick="submitRuns(4)">
                        FOUR
                    </button>
                </div>                    
                <div class="col-3">
                    <button type="button" class="pad-btn six w-100 special" onclick="submitRuns(6)">
                        SIX
                    </button>
                </div>
                <div class="col-3">
                    <button type="button" class="pad-btn out w-100 special" onclick="submitWicket()">
                        WICKET
                    </button>
                </div>
                <div class="col-3">
                    <button type="button" class="pad-btn undo w-100 special" onclick="undoLast()">
                        UNDO
                    </button>
                </div>
            </div>

            <!-- Extras Buttons -->
            <div class="row">
                <div class="col-12">
                    <h4 class="pad-section-title">
                        <i class="bi bi-plus-square"></i> Extras
                    </h4>
                </div>
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-3">
                    <button type="button" class="extra-btn w-100" onclick="submitExtras('WD')">
                        <div>WD</div>
                        <small style="font-size: 10px; opacity: 0.7;">Wide</small>
                    </button>
                </div>
                <div class="col-3">
                    <button type="button" class="extra-btn w-100" onclick="submitExtras('NB')">
                        <div>NB</div>
                        <small style="font-size: 10px; opacity: 0.7;">No Ball</small>
                    </button>
                </div>
                <div class="col-3">
                    <button type="button" class="extra-btn w-100" onclick="submitExtras('BYE')">
                        <div>BYE</div>
                        <small style="font-size: 10px; opacity: 0.7;">Bye</small>
                    </button>
                </div>
                <div class="col-3">
                    <button type="button" class="extra-btn w-100" onclick="submitExtras('LB')">
                        <div>LB</div>
                        <small style="font-size: 10px; opacity: 0.7;">Leg Bye</small>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Batsman Modal -->
    <div class="modal fade" id="addBatsmanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus-fill"></i> Select New Batsman
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="player-list" id="addBatsmanPlayerList">
                        <?php 
                        // Filter out players who are currently batting
                        $current_batsmen_ids = array_column($current_batsmen, 'player_id');
                        $available_for_batting = array_filter($players, function($player) use ($current_batsmen_ids) {
                            return !in_array($player['id'], $current_batsmen_ids);
                        });
                        
                        foreach ($available_for_batting as $player): 
                        ?>
                            <div class="player-item" data-player-id="<?= $player['id'] ?>" data-player-name="<?= htmlspecialchars($player['player_name']) ?>" onclick="addBatsman(<?= $player['id'] ?>)">
                                <span class="player-name">
                                    <i class="bi bi-person-circle"></i>
                                    <?= htmlspecialchars($player['player_name']) ?>
                                </span>
                                <span class="select-icon">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Opening Pair Modal -->
    <div class="modal fade" id="openingPairModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-people-fill"></i> Select Opening Batsmen
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Striker Selection -->
                        <div class="col-md-6">
                            <div class="selection-box">
                                <div class="selection-header">
                                    <i class="bi bi-star-fill"></i> Striker
                                    <span class="badge bg-warning">1st</span>
                                </div>
                                <div id="strikerSelection" class="player-selection-area">
                                    <div class="text-center text-muted py-4" id="strikerPlaceholder">
                                        <i class="bi bi-person-plus" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2">Click a player below to select as Striker</p>
                                    </div>
                                    <div id="selectedStriker" class="selected-player-card" style="display: none;">
                                        <!-- Will be filled by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Non-Striker Selection -->
                        <div class="col-md-6">
                            <div class="selection-box">
                                <div class="selection-header">
                                    <i class="bi bi-person-fill"></i> Non-Striker
                                    <span class="badge bg-secondary">2nd</span>
                                </div>
                                <div id="nonStrikerSelection" class="player-selection-area">
                                    <div class="text-center text-muted py-4" id="nonStrikerPlaceholder">
                                        <i class="bi bi-person-plus" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2">Click a player below to select as Non-Striker</p>
                                    </div>
                                    <div id="selectedNonStriker" class="selected-player-card" style="display: none;">
                                        <!-- Will be filled by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Player List -->
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-list-ul"></i> Available Players
                        </h6>
                        <div class="player-list" style="max-height: 300px; overflow-y: auto;" id="openingPairPlayerList">
                            <?php foreach ($players as $player): ?>
                                <div class="player-item opening-player-item" data-player-id="<?= $player['id'] ?>" data-player-name="<?= htmlspecialchars($player['player_name']) ?>" onclick="selectForOpening(<?= $player['id'] ?>, '<?= htmlspecialchars($player['player_name'], ENT_QUOTES) ?>')">
                                    <span class="player-name">
                                        <i class="bi bi-person-circle"></i>
                                        <?= htmlspecialchars($player['player_name']) ?>
                                    </span>
                                    <span class="select-icon">
                                        <i class="bi bi-chevron-right"></i>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cancelOpeningPair()">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-secondary" id="confirmOpeningPairBtn" onclick="confirmOpeningPair()" disabled>
                        <i class="bi bi-check-circle"></i> Confirm Opening Pair
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replace Batsman Modal -->
    <div class="modal fade" id="replaceBatsmanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-left-right"></i> Replace Batsman
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom" id="selectedBatsmanInfo">
                        <label class="form-label fw-bold mb-2">
                            <i class="bi bi-person-x"></i> Replacing Batsman
                        </label>
                        <div class="alert alert-warning mb-0" id="batsmanToReplaceInfo">
                            <!-- Will be filled by JavaScript -->
                        </div>
                        <input type="hidden" id="oldBatsmanId">
                    </div>
                    
                    <div class="p-3">
                        <label class="form-label fw-bold mb-2">
                            <i class="bi bi-person-plus"></i> Select New Batsman
                        </label>
                        <div class="player-list" style="max-height: 300px; overflow-y: auto;">
                            <?php 
                            // Filter out currently batting players from replacement list
                            $current_batsmen_ids = array_column($current_batsmen, 'player_id');
                            $available_for_replacement = array_filter($replacement_players, function($player) use ($current_batsmen_ids) {
                                return !in_array($player['id'], $current_batsmen_ids);
                            });
                            
                            foreach ($available_for_replacement as $player): 
                            ?>
                                <div class="player-item" data-player-id="<?= $player['id'] ?>" data-player-name="<?= htmlspecialchars($player['player_name']) ?>" onclick="replaceWithPlayer(<?= $player['id'] ?>)">
                                    <span class="player-name">
                                        <i class="bi bi-person-circle"></i>
                                        <?= htmlspecialchars($player['player_name']) ?>
                                    </span>
                                    <span class="select-icon">
                                        <i class="bi bi-chevron-right"></i>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bowler Modal -->
    <div class="modal fade" id="addBowlerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus-fill"></i> Select Bowler
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="player-list">
                        <?php foreach ($bowlers as $bowler): ?>
                            <div class="player-item" data-player-id="<?= $bowler['id'] ?>" data-player-name="<?= htmlspecialchars($bowler['player_name']) ?>" onclick="addBowler(<?= $bowler['id'] ?>)">
                                <span class="player-name">
                                    <i class="bi bi-person-circle"></i>
                                    <?= htmlspecialchars($bowler['player_name']) ?>
                                </span>
                                <span class="select-icon">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Replace Bowler Modal -->
    <div class="modal fade" id="replaceBowlerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-left-right"></i> Change Bowler
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom" id="currentBowlerInfoSection">
                        <label class="form-label fw-bold mb-2">
                            <i class="bi bi-person-badge"></i> Current Bowler
                        </label>
                        <div class="alert alert-info mb-0" id="currentBowlerInfo">
                            <!-- Will be filled dynamically by JavaScript -->
                            <strong>No bowler selected</strong>
                        </div>
                    </div>
                    
                    <div class="p-3">
                        <label class="form-label fw-bold mb-2">
                            <i class="bi bi-person-plus"></i> Select New Bowler
                        </label>
                        <div class="player-list" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($bowlers as $bowler): ?>
                                <div class="player-item" data-player-id="<?= $bowler['id'] ?>" data-player-name="<?= htmlspecialchars($bowler['player_name']) ?>" onclick="replaceBowler(<?= $bowler['id'] ?>)">
                                    <span class="player-name">
                                        <i class="bi bi-person-circle"></i>
                                        <?= htmlspecialchars($bowler['player_name']) ?>
                                    </span>
                                    <span class="select-icon">
                                        <i class="bi bi-chevron-right"></i>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/script/sweetalert2.js"></script>
    
    <script>
        const matchId = <?= $match_id ?>;
        let isSubmitting = false;

        // AJAX function to submit score
        function submitScore(data) {
            if (isSubmitting) return;
            
            isSubmitting = true;
            disableButtons();
            showLoading();
            
            // Add AJAX flag
            data.ajax = '1';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.innings_completed) {
                        // Redirect to completion page
                        window.location.href = data.redirect_url;
                    } else {
                        // Refresh the page data
                        refreshPageData();
                        
                        // Check if wicket was taken - show add batsman modal FIRST
                        if (data.wicket_taken) {
                            setTimeout(() => {
                                Swal.fire({
                                    title: 'Wicket Down!',
                                    text: 'Please add the next batsman',
                                    icon: 'warning',
                                    confirmButtonColor: '#F59E0B',
                                    confirmButtonText: 'Add Batsman',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    openAddBatsmanModal();
                                });
                            }, 500);
                        }
                        // Check if over is completed - show change bowler modal
                        else if (data.over_completed) {
                            setTimeout(() => {
                                Swal.fire({
                                    title: 'Over Completed!',
                                    text: 'Please change the bowler',
                                    icon: 'info',
                                    confirmButtonColor: '#3B82F6',
                                    confirmButtonText: 'Change Bowler',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    openReplaceBowlerModal();
                                });
                            }, 500);
                        }
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.error || 'Error updating score',
                        confirmButtonColor: '#F59E0B'
                    });
                    enableButtons();
                    hideLoading();
                    isSubmitting = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error updating score. Please try again.',
                    confirmButtonColor: '#F59E0B'
                });
                enableButtons();
                hideLoading();
                isSubmitting = false;
            });
        }

        function submitRuns(runs) {
            submitScore({
                runs: runs,
                is_wicket: '',
                extras_type: '',
                extra_runs: ''
            });
        }

        function submitWicket() {
            Swal.fire({
                title: 'Confirm Wicket',
                text: 'Are you sure you want to record a wicket?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Wicket!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitScore({
                        runs: '0',
                        is_wicket: '1',
                        extras_type: '',
                        extra_runs: ''
                    });
                }
            });
        }

        function submitExtras(type) {
            const extraTypes = {
                'WD': 'Wide',
                'NB': 'No Ball',
                'BYE': 'Bye',
                'LB': 'Leg Bye'
            };
            
            Swal.fire({
                title: `Enter ${extraTypes[type]} Runs`,
                input: 'number',
                inputValue: 0,
                inputAttributes: {
                    min: 1,
                    step: 1
                },
                showCancelButton: true,
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value || value < 0) {
                        return 'Please enter a valid number of runs';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitScore({
                        runs: '0',
                        is_wicket: '',
                        extras_type: type,
                        extra_runs: result.value
                    });
                }
            });
        }
        
        function refreshPageData() {
            // COMPREHENSIVE DIMENSION LOCKING
            const batsmenSection = document.querySelector('.batsmen-section');
            const container = document.querySelector('#batsmenContainer');
            const actionButtons = document.querySelector('#actionButtonsRow');
            const bowlerSection = document.querySelector('#bowlerCardSection');
            const batsmenCards = document.querySelector('#batsmenCards');
            
            // Save ALL current widths
            const locks = {
                section: batsmenSection ? {
                    width: batsmenSection.offsetWidth,
                    elem: batsmenSection
                } : null,
                container: container ? {
                    width: container.offsetWidth,
                    elem: container
                } : null,
                buttons: actionButtons ? {
                    width: actionButtons.offsetWidth,
                    elem: actionButtons
                } : null,
                bowler: bowlerSection ? {
                    width: bowlerSection.offsetWidth,
                    elem: bowlerSection
                } : null,
                batsmen: batsmenCards ? {
                    width: batsmenCards.offsetWidth,
                    elem: batsmenCards
                } : null
            };
            
            // Apply locks
            Object.values(locks).forEach(lock => {
                if (lock && lock.elem) {
                    lock.elem.style.width = lock.width + 'px';
                    lock.elem.style.minWidth = lock.width + 'px';
                    lock.elem.style.maxWidth = lock.width + 'px';
                }
            });
            
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    requestAnimationFrame(() => {
                        // Update scores
                        const newTotalRuns = doc.querySelector('#totalRuns')?.textContent;
                        const newTotalWickets = doc.querySelector('#totalWickets')?.textContent;
                        const newTotalOvers = doc.querySelector('#totalOvers')?.textContent;
                        
                        if (newTotalRuns) document.querySelector('#totalRuns').textContent = newTotalRuns;
                        if (newTotalWickets) document.querySelector('#totalWickets').textContent = newTotalWickets;
                        if (newTotalOvers) document.querySelector('#totalOvers').textContent = newTotalOvers;
                        
                        // Update stats
                        const statsMap = {
                            '#runRate': doc.querySelector('#runRate')?.textContent,
                            '#projected': doc.querySelector('#projected')?.textContent,
                            '#oversLeft': doc.querySelector('#oversLeft')?.textContent,
                            '#requiredRuns': doc.querySelector('#requiredRuns')?.textContent,
                            '#requiredRate': doc.querySelector('#requiredRate')?.textContent
                        };
                        
                        Object.keys(statsMap).forEach(selector => {
                            const elem = document.querySelector(selector);
                            if (elem && statsMap[selector]) {
                                elem.textContent = statsMap[selector];
                            }
                        });
                        
                        // Update over progress
                        const newBallsIndicator = doc.querySelector('#ballsIndicator');
                        const currentBallsIndicator = document.querySelector('#ballsIndicator');
                        if (newBallsIndicator && currentBallsIndicator) {
                            currentBallsIndicator.innerHTML = newBallsIndicator.innerHTML;
                        }
                        
                        // Update over/ball numbers
                        const newCurrentOver = doc.querySelector('#currentOver')?.textContent;
                        const newCurrentBall = doc.querySelector('#currentBall')?.textContent;
                        if (newCurrentOver) document.querySelector('#currentOver').textContent = newCurrentOver;
                        if (newCurrentBall) document.querySelector('#currentBall').textContent = newCurrentBall;
                        
                        // Update existing batsmen
                        document.querySelectorAll('.batsman-card[data-batsman-id]').forEach(currentCard => {
                            const batsmanId = currentCard.getAttribute('data-batsman-id');
                            const newCard = doc.querySelector(`.batsman-card[data-batsman-id="${batsmanId}"]`);
                            
                            if (newCard) {
                                const runsElem = currentCard.querySelector('.batsman-runs');
                                const ballsElem = currentCard.querySelector('.batsman-balls');
                                const foursElem = currentCard.querySelector('.batsman-fours');
                                const sixesElem = currentCard.querySelector('.batsman-sixes');
                                
                                if (runsElem) runsElem.textContent = newCard.querySelector('.batsman-runs')?.textContent || '0';
                                if (ballsElem) ballsElem.textContent = newCard.querySelector('.batsman-balls')?.textContent || '0';
                                if (foursElem) foursElem.textContent = newCard.querySelector('.batsman-fours')?.textContent || '0';
                                if (sixesElem) sixesElem.textContent = newCard.querySelector('.batsman-sixes')?.textContent || '0';
                                
                                const hasStriker = newCard.classList.contains('striker');
                                if (hasStriker && !currentCard.classList.contains('striker')) {
                                    currentCard.classList.add('striker');
                                    if (!currentCard.querySelector('.striker-badge')) {
                                        const badge = document.createElement('span');
                                        badge.className = 'striker-badge';
                                        badge.textContent = 'Striker';
                                        currentCard.querySelector('.batsman-name').appendChild(badge);
                                    }
                                } else if (!hasStriker && currentCard.classList.contains('striker')) {
                                    currentCard.classList.remove('striker');
                                    const badge = currentCard.querySelector('.striker-badge');
                                    if (badge) badge.remove();
                                }
                            }
                        });
                        
                        // Check batsmen count AND IDs (for replacement detection)
                        const newBatsmenCards = doc.querySelectorAll('#batsmenCards .batsman-card[data-batsman-id]');
                        const currentBatsmenCards = document.querySelectorAll('#batsmenCards .batsman-card[data-batsman-id]');
                        
                        // Get batsmen IDs
                        const newBatsmenIds = Array.from(newBatsmenCards).map(card => card.getAttribute('data-batsman-id')).sort().join(',');
                        const currentBatsmenIds = Array.from(currentBatsmenCards).map(card => card.getAttribute('data-batsman-id')).sort().join(',');
                        
                        // Replace if count changed OR if IDs changed (replacement)
                        if (newBatsmenCards.length !== currentBatsmenCards.length || newBatsmenIds !== currentBatsmenIds) {
                            const batsmenCardsContainer = document.querySelector('#batsmenCards');
                            const newBatsmenCardsContainer = doc.querySelector('#batsmenCards');
                            if (batsmenCardsContainer && newBatsmenCardsContainer) {
                                batsmenCardsContainer.innerHTML = newBatsmenCardsContainer.innerHTML;
                            }
                        }
                        
                        // Update bowler
                        const currentBowlerCard = document.querySelector('#bowlerCard');
                        const newBowlerCard = doc.querySelector('#bowlerCard');
                        
                        if (currentBowlerCard && newBowlerCard) {
                            const currentName = currentBowlerCard.getAttribute('data-bowler-name');
                            const newName = newBowlerCard.getAttribute('data-bowler-name');
                            
                            if (currentName === newName) {
                                const oversElem = currentBowlerCard.querySelector('.bowler-overs');
                                const runsElem = currentBowlerCard.querySelector('.bowler-runs');
                                const wicketsElem = currentBowlerCard.querySelector('.bowler-wickets');
                                const maidensElem = currentBowlerCard.querySelector('.bowler-maidens');
                                
                                if (oversElem) {
                                    oversElem.textContent = newBowlerCard.querySelector('.bowler-overs')?.textContent || '0.0';
                                    oversElem.setAttribute('data-value', newBowlerCard.querySelector('.bowler-overs')?.getAttribute('data-value') || '0.0');
                                }
                                if (runsElem) {
                                    runsElem.textContent = newBowlerCard.querySelector('.bowler-runs')?.textContent || '0';
                                    runsElem.setAttribute('data-value', newBowlerCard.querySelector('.bowler-runs')?.getAttribute('data-value') || '0');
                                }
                                if (wicketsElem) {
                                    wicketsElem.textContent = newBowlerCard.querySelector('.bowler-wickets')?.textContent || '0';
                                    wicketsElem.setAttribute('data-value', newBowlerCard.querySelector('.bowler-wickets')?.getAttribute('data-value') || '0');
                                }
                                if (maidensElem) {
                                    maidensElem.textContent = newBowlerCard.querySelector('.bowler-maidens')?.textContent || '0';
                                }
                            } else {
                                const bowlerSection = document.querySelector('#bowlerCardSection');
                                const newBowlerSection = doc.querySelector('#bowlerCardSection');
                                if (bowlerSection && newBowlerSection) {
                                    bowlerSection.innerHTML = newBowlerSection.innerHTML;
                                }
                            }
                        } else if (!currentBowlerCard && newBowlerCard) {
                            const bowlerSection = document.querySelector('#bowlerCardSection');
                            const newBowlerSection = doc.querySelector('#bowlerCardSection');
                            if (bowlerSection && newBowlerSection) {
                                bowlerSection.innerHTML = newBowlerSection.innerHTML;
                            }
                        } else if (currentBowlerCard && !newBowlerCard) {
                            const bowlerSection = document.querySelector('#bowlerCardSection');
                            if (bowlerSection) {
                                bowlerSection.innerHTML = '';
                            }
                        }
                        
                        // Update buttons
                        const newButtonRow = doc.querySelector('#actionButtonsRow');
                        const currentButtonRow = document.querySelector('#actionButtonsRow');
                        if (newButtonRow && currentButtonRow) {
                            const newHTML = newButtonRow.innerHTML;
                            const currentHTML = currentButtonRow.innerHTML;
                            if (newHTML !== currentHTML) {
                                currentButtonRow.innerHTML = newHTML;
                                
                                // Re-attach event listeners to new buttons
                                const addBatsmanBtn = document.getElementById('addBatsmanBtn');
                                if (addBatsmanBtn) {
                                    // Remove old listeners by cloning
                                    const newBtn = addBatsmanBtn.cloneNode(true);
                                    addBatsmanBtn.parentNode.replaceChild(newBtn, addBatsmanBtn);
                                    
                                    // Add fresh listeners
                                    newBtn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        openAddBatsmanModal();
                                    }, false);
                                }
                            }
                        }
                        
                        // Update modals
                        const newAddBatsmanList = doc.querySelector('#addBatsmanPlayerList');
                        const currentAddBatsmanList = document.querySelector('#addBatsmanPlayerList');
                        if (newAddBatsmanList && currentAddBatsmanList) {
                            currentAddBatsmanList.innerHTML = newAddBatsmanList.innerHTML;
                        }
                        
                        const newReplaceBatsmanList = doc.querySelector('#replaceBatsmanModal .player-list');
                        const currentReplaceBatsmanList = document.querySelector('#replaceBatsmanModal .player-list');
                        if (newReplaceBatsmanList && currentReplaceBatsmanList) {
                            currentReplaceBatsmanList.innerHTML = newReplaceBatsmanList.innerHTML;
                        }
                        
                        // Update alerts section
                        const newAlertsSection = doc.querySelector('#alertsSection');
                        const currentAlertsSection = document.querySelector('#alertsSection');
                        if (newAlertsSection && currentAlertsSection) {
                            currentAlertsSection.innerHTML = newAlertsSection.innerHTML;
                        }
                        
                        // RELEASE ALL LOCKS
                        setTimeout(() => {
                            Object.values(locks).forEach(lock => {
                                if (lock && lock.elem) {
                                    lock.elem.style.width = '';
                                    lock.elem.style.minWidth = '';
                                    lock.elem.style.maxWidth = '';
                                }
                            });
                        }, 50);
                        
                        // Enable everything
                        enableButtons();
                        hideLoading();
                        isSubmitting = false;
                        
                        // Force enable page interaction (belt and suspenders approach)
                        setTimeout(() => {
                            forceEnablePageInteraction();
                        }, 100);
                        
                        if (navigator.vibrate) {
                            navigator.vibrate(50);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    window.location.reload();
                });
        }

        function showLoading() {
            document.getElementById('loadingSpinner').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').classList.remove('show');
        }
        
        // Force reset page to interactive state
        function forceEnablePageInteraction() {
            // Hide loading spinner completely
            const spinner = document.getElementById('loadingSpinner');
            if (spinner) {
                spinner.classList.remove('show');
                spinner.style.display = 'none';
            }
            
            // Remove any stuck modal backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // Enable all buttons
            document.querySelectorAll('.pad-btn, .extra-btn, .add-batsman-btn').forEach(btn => {
                btn.disabled = false;
                btn.style.pointerEvents = 'auto';
                btn.style.opacity = '1';
            });
            
            // Reset submitting flag
            isSubmitting = false;
            
            // Re-attach Add Batsman button handler
            const addBatsmanBtn = document.getElementById('addBatsmanBtn');
            if (addBatsmanBtn && !addBatsmanBtn.hasAttribute('data-listener-attached')) {
                addBatsmanBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openAddBatsmanModal();
                }, false);
                addBatsmanBtn.setAttribute('data-listener-attached', 'true');
            }
        }
        
        // Make it available globally for console testing
        window.forceEnablePageInteraction = forceEnablePageInteraction;

        function disableButtons() {
            document.querySelectorAll('.pad-btn, .extra-btn').forEach(btn => {
                btn.disabled = true;
            });
        }

        function enableButtons() {
            document.querySelectorAll('.pad-btn, .extra-btn').forEach(btn => {
                btn.disabled = false;
            });
        }

        function preselectBatsman(batsmanId, playerId, batsmanName, runs, balls) {
            setTimeout(() => {
                document.getElementById('oldBatsmanId').value = batsmanId;
                document.getElementById('batsmanToReplaceInfo').innerHTML = 
                    `<strong>${batsmanName}</strong><br>
                    <small>${runs} runs, ${balls} balls</small>`;
            }, 100);
        }
        
        function openReplaceModal(batsmanId, playerId, batsmanName, runs, balls) {
            document.getElementById('oldBatsmanId').value = batsmanId;
            document.getElementById('batsmanToReplaceInfo').innerHTML = 
                `<strong>${batsmanName}</strong><br>
                <small>${runs} runs, ${balls} balls</small>`;
            
            // Get all currently batting player names from the page
            const currentBatsmenNames = [];
            document.querySelectorAll('.batsman-name-text').forEach(elem => {
                currentBatsmenNames.push(elem.textContent.trim());
            });
            
            // Hide ALL currently batting players from the replacement list
            const playerItems = document.querySelectorAll('#replaceBatsmanModal .player-item');
            playerItems.forEach(item => {
                const itemPlayerName = item.getAttribute('data-player-name');
                
                // Hide if this player is currently batting
                if (currentBatsmenNames.includes(itemPlayerName)) {
                    item.style.display = 'none';
                } else {
                    item.style.display = 'flex';
                }
            });
            
            // Open the modal
            const modal = new bootstrap.Modal(document.getElementById('replaceBatsmanModal'));
            modal.show();
        }
        
        function openAddBatsmanModal() {
            try {
                // Force hide any stuck loading spinner
                const spinner = document.getElementById('loadingSpinner');
                if (spinner) spinner.classList.remove('show');
                
                // Check batsmen count
                const currentBatsmenCount = document.querySelectorAll('.batsman-card[data-batsman-id]').length;
                
                if (currentBatsmenCount === 0) {
                    // Open opening pair modal
                    openOpeningPairModal();
                    return;
                }
                
                // Get currently batting players
                const currentBatsmenNames = [];
                document.querySelectorAll('.batsman-name-text').forEach(elem => {
                    currentBatsmenNames.push(elem.textContent.trim());
                });
                
                // Reset ALL player items in the list
                const playerItems = document.querySelectorAll('#addBatsmanPlayerList .player-item');
                playerItems.forEach(item => {
                    const itemPlayerName = item.getAttribute('data-player-name');
                    
                    // Force reset ALL styles
                    item.style.pointerEvents = 'auto';
                    item.style.opacity = '1';
                    item.style.cursor = 'pointer';
                    item.classList.remove('selected', 'opening-player-item');
                    
                    // Show or hide based on batting status
                    if (currentBatsmenNames.includes(itemPlayerName)) {
                        item.style.display = 'none';
                    } else {
                        item.style.display = 'flex';
                    }
                });
                
                // Force show the modal
                const modalEl = document.getElementById('addBatsmanModal');
                if (!modalEl) {
                    console.error('Add Batsman Modal not found!');
                    alert('Error: Modal not found. Please refresh the page.');
                    return;
                }
                
                const existingModal = bootstrap.Modal.getInstance(modalEl);
                if (existingModal) {
                    existingModal.dispose();
                }
                const modal = new bootstrap.Modal(modalEl, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
                modal.show();
            } catch (error) {
                console.error('Error in openAddBatsmanModal:', error);
                alert('An error occurred. Please refresh the page and try again.');
            }
        }

        function undoLast() {
            Swal.fire({
                title: 'Undo Last Ball',
                text: 'Are you sure you want to undo the last ball?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Undo',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Coming Soon',
                        text: 'Undo functionality will be implemented soon',
                        confirmButtonColor: '#F59E0B'
                    });
                }
            });
        }

        function addBatsman(playerId) {
            // Close modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('addBatsmanModal'));
            if (modal) {
                modal.hide();
            }
            
            // Wait for modal to close completely
            setTimeout(() => {
                // Show loading
                showLoading();
                
                // Send AJAX request
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        ajax: '1',
                        action: 'add_batsman',
                        player_id: playerId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // IMMEDIATELY force enable the page
                        setTimeout(() => {
                            forceEnablePageInteraction();
                        }, 50);
                        
                        // Then refresh the page data
                        refreshPageData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.error || 'Error adding batsman',
                            confirmButtonColor: '#F59E0B'
                        });
                        hideLoading();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error adding batsman. Please try again.',
                        confirmButtonColor: '#F59E0B'
                    });
                    hideLoading();
                });
            }, 200); // Wait for modal to close
        }

        function replaceWithPlayer(newPlayerId) {
            const oldBatsmanId = document.getElementById('oldBatsmanId').value;
            
            if (!oldBatsmanId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Error',
                    text: 'Could not identify batsman to replace!',
                    confirmButtonColor: '#F59E0B'
                });
                return;
            }
            
            Swal.fire({
                title: 'Replace Batsman',
                text: 'Are you sure you want to replace this batsman?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Replace',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('replaceBatsmanModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Small delay to ensure modal is fully closed
                    setTimeout(() => {
                        // Show loading
                        showLoading();
                        
                        // Send AJAX request
                        fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                ajax: '1',
                                action: 'replace_batsman',
                                old_batsman_id: oldBatsmanId,
                                new_player_id: newPlayerId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // IMMEDIATELY force enable the page
                                setTimeout(() => {
                                    forceEnablePageInteraction();
                                }, 50);
                                
                                // Then refresh the page data
                                refreshPageData();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.error || 'Error replacing batsman',
                                    confirmButtonColor: '#F59E0B'
                                });
                                hideLoading();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Error replacing batsman. Please try again.',
                                confirmButtonColor: '#F59E0B'
                            });
                            hideLoading();
                        });
                    }, 200); // End of setTimeout
                }
            });
        }

        // Bowler functions
        function openAddBowlerModal() {
            const modal = new bootstrap.Modal(document.getElementById('addBowlerModal'));
            modal.show();
        }

        function openReplaceBowlerModal() {
            // Get current bowler data from the bowler card on the page
            const bowlerCard = document.getElementById('bowlerCard');
            
            if (bowlerCard) {
                const currentBowlerName = bowlerCard.getAttribute('data-bowler-name');
                const bowlerOvers = bowlerCard.querySelector('.bowler-overs')?.getAttribute('data-value') || '0.0';
                const bowlerRuns = bowlerCard.querySelector('.bowler-runs')?.getAttribute('data-value') || '0';
                const bowlerWickets = bowlerCard.querySelector('.bowler-wickets')?.getAttribute('data-value') || '0';
                
                // Update the modal with current bowler info
                const currentBowlerInfo = document.getElementById('currentBowlerInfo');
                if (currentBowlerInfo && currentBowlerName) {
                    currentBowlerInfo.innerHTML = `
                        <strong>${currentBowlerName}</strong><br>
                        <small>${bowlerOvers} overs, ${bowlerRuns} runs, ${bowlerWickets} wickets</small>
                    `;
                }
                
                // Hide current bowler from the list
                const playerItems = document.querySelectorAll('#replaceBowlerModal .player-item');
                playerItems.forEach(item => {
                    const itemPlayerName = item.getAttribute('data-player-name');
                    
                    // Hide if this is the current bowler
                    if (itemPlayerName === currentBowlerName) {
                        item.style.display = 'none';
                    } else {
                        item.style.display = 'flex';
                    }
                });
            } else {
                // No current bowler
                const currentBowlerInfo = document.getElementById('currentBowlerInfo');
                if (currentBowlerInfo) {
                    currentBowlerInfo.innerHTML = '<strong>No bowler selected</strong>';
                }
                
                // Show all bowlers in list
                const playerItems = document.querySelectorAll('#replaceBowlerModal .player-item');
                playerItems.forEach(item => {
                    item.style.display = 'flex';
                });
            }
            
            const modal = new bootstrap.Modal(document.getElementById('replaceBowlerModal'));
            modal.show();
        }

        function addBowler(playerId) {
            // Close modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('addBowlerModal'));
            if (modal) {
                modal.hide();
            }
            
            // Wait for modal to close completely
            setTimeout(() => {
                // Show loading
                showLoading();
                
                // Send AJAX request
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        ajax: '1',
                        action: 'add_bowler',
                        player_id: playerId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // IMMEDIATELY force enable the page
                        setTimeout(() => {
                            forceEnablePageInteraction();
                        }, 50);
                        
                        // Then refresh the page data
                        refreshPageData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.error || 'Error adding bowler',
                            confirmButtonColor: '#F59E0B'
                        });
                        hideLoading();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error adding bowler. Please try again.',
                        confirmButtonColor: '#F59E0B'
                    });
                    hideLoading();
                });
            }, 200); // Wait for modal to close
        }

        function replaceBowler(newPlayerId) {
            Swal.fire({
                title: 'Change Bowler',
                text: 'Are you sure you want to change the bowler?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3B82F6',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Change',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('replaceBowlerModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Wait for modal to close completely
                    setTimeout(() => {
                        // Show loading
                        showLoading();
                        
                        // Send AJAX request
                        fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                ajax: '1',
                                action: 'replace_bowler',
                                new_player_id: newPlayerId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // IMMEDIATELY force enable the page
                                setTimeout(() => {
                                    forceEnablePageInteraction();
                                }, 50);
                                
                                // Then refresh the page data
                                refreshPageData();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.error || 'Error changing bowler',
                                    confirmButtonColor: '#F59E0B'
                                });
                                hideLoading();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Error changing bowler. Please try again.',
                                confirmButtonColor: '#F59E0B'
                            });
                            hideLoading();
                        });
                    }, 200); // Wait for modal and SweetAlert to close
                }
            });
        }

        // Add haptic feedback on button clicks (mobile)
        document.addEventListener('DOMContentLoaded', function() {
            // Haptic feedback
            document.querySelectorAll('.pad-btn, .extra-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (navigator.vibrate) {
                        navigator.vibrate(50);
                    }
                });
            });
            
            // ULTRA-SIMPLE: Just make the Add Batsman button work
            function forceButtonToWork() {
                const btn = document.getElementById('addBatsmanBtn');
                if (!btn) return;
                
                // Remove ALL existing event listeners by cloning
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                // Add ONE simple click handler
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openAddBatsmanModal();
                }, false);
                
                // Also try touchstart for mobile
                newBtn.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openAddBatsmanModal();
                }, { passive: false });
            }
            
            // Try immediately
            forceButtonToWork();
            
            // Try again after a short delay
            setTimeout(forceButtonToWork, 100);
            setTimeout(forceButtonToWork, 500);
        });

        // Opening Pair Selection
        let selectedStrikerId = null;
        let selectedNonStrikerId = null;

        function openOpeningPairModal() {
            // Reset selections
            selectedStrikerId = null;
            selectedNonStrikerId = null;
            
            // Show modal FIRST to ensure elements exist
            const modalEl = document.getElementById('openingPairModal');
            if (!modalEl) {
                console.error('Opening Pair Modal not found!');
                return;
            }
            
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            // Wait a bit for modal to render, then reset UI
            setTimeout(() => {
                // Reset UI with null checks
                const strikerPlaceholder = document.getElementById('strikerPlaceholder');
                const selectedStriker = document.getElementById('selectedStriker');
                const nonStrikerPlaceholder = document.getElementById('nonStrikerPlaceholder');
                const selectedNonStriker = document.getElementById('selectedNonStriker');
                const strikerArea = document.querySelector('#strikerSelection .player-selection-area');
                const nonStrikerArea = document.querySelector('#nonStrikerSelection .player-selection-area');
                
                if (strikerPlaceholder) strikerPlaceholder.style.display = 'block';
                if (selectedStriker) selectedStriker.style.display = 'none';
                if (nonStrikerPlaceholder) nonStrikerPlaceholder.style.display = 'block';
                if (selectedNonStriker) selectedNonStriker.style.display = 'none';
                
                if (strikerArea) strikerArea.classList.remove('has-selection');
                if (nonStrikerArea) nonStrikerArea.classList.remove('has-selection');
                
                // Reset button state
                updateConfirmButton();
                
                // Reset player list
                document.querySelectorAll('.opening-player-item').forEach(item => {
                    item.classList.remove('selected');
                });
            }, 100);
        }

        function selectForOpening(playerId, playerName) {
            // Check if already selected
            if (selectedStrikerId === playerId || selectedNonStrikerId === playerId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Already Selected',
                    text: 'This player is already selected!',
                    confirmButtonColor: '#F59E0B'
                });
                return;
            }
            
            // If striker not selected, select as striker
            if (!selectedStrikerId) {
                selectedStrikerId = playerId;
                
                // Update UI with null checks
                const strikerPlaceholder = document.getElementById('strikerPlaceholder');
                const selectedStriker = document.getElementById('selectedStriker');
                const strikerArea = document.querySelector('#strikerSelection .player-selection-area');
                
                if (strikerPlaceholder) strikerPlaceholder.style.display = 'none';
                if (selectedStriker) {
                    selectedStriker.style.display = 'block';
                    selectedStriker.innerHTML = `
                        <div class="player-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="player-name-text">${playerName}</div>
                        <div class="badge bg-warning">Striker</div>
                        <button class="remove-btn" onclick="removeStriker()">
                            <i class="bi bi-x-circle"></i> Remove
                        </button>
                    `;
                }
                
                // Mark as selected in list
                const playerItem = document.querySelector(`.opening-player-item[data-player-id="${playerId}"]`);
                if (playerItem) {
                    playerItem.classList.add('selected');
                }
                
                // Add selection effect
                if (strikerArea) strikerArea.classList.add('has-selection');
            }
            // If striker selected but non-striker not selected, select as non-striker
            else if (!selectedNonStrikerId) {
                selectedNonStrikerId = playerId;
                
                // Update UI with null checks
                const nonStrikerPlaceholder = document.getElementById('nonStrikerPlaceholder');
                const selectedNonStriker = document.getElementById('selectedNonStriker');
                const nonStrikerArea = document.querySelector('#nonStrikerSelection .player-selection-area');
                
                if (nonStrikerPlaceholder) nonStrikerPlaceholder.style.display = 'none';
                if (selectedNonStriker) {
                    selectedNonStriker.style.display = 'block';
                    selectedNonStriker.innerHTML = `
                        <div class="player-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="player-name-text">${playerName}</div>
                        <div class="badge bg-secondary">Non-Striker</div>
                        <button class="remove-btn" onclick="removeNonStriker()">
                            <i class="bi bi-x-circle"></i> Remove
                        </button>
                    `;
                }
                
                // Mark as selected in list
                const playerItem = document.querySelector(`.opening-player-item[data-player-id="${playerId}"]`);
                if (playerItem) {
                    playerItem.classList.add('selected');
                }
                
                // Add selection effect
                if (nonStrikerArea) nonStrikerArea.classList.add('has-selection');
            }
            else {
                Swal.fire({
                    icon: 'info',
                    title: 'Both Batsmen Selected',
                    text: 'Remove a batsman first if you want to change selection',
                    confirmButtonColor: '#F59E0B'
                });
            }
            
            // Check if both are selected and enable button
            updateConfirmButton();
        }

        function updateConfirmButton() {
            const confirmBtn = document.getElementById('confirmOpeningPairBtn');
            if (!confirmBtn) return; // Exit if button doesn't exist yet
            
            if (selectedStrikerId && selectedNonStrikerId) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('btn-secondary');
                confirmBtn.classList.add('btn-warning');
            } else {
                confirmBtn.disabled = true;
                confirmBtn.classList.remove('btn-warning');
                confirmBtn.classList.add('btn-secondary');
            }
        }

        function removeStriker() {
            if (!selectedStrikerId) return;
            
            // Remove from UI
            const playerItem = document.querySelector(`.opening-player-item[data-player-id="${selectedStrikerId}"]`);
            if (playerItem) {
                playerItem.classList.remove('selected');
            }
            
            selectedStrikerId = null;
            
            const strikerPlaceholder = document.getElementById('strikerPlaceholder');
            const selectedStriker = document.getElementById('selectedStriker');
            const strikerArea = document.querySelector('#strikerSelection .player-selection-area');
            
            if (strikerPlaceholder) strikerPlaceholder.style.display = 'block';
            if (selectedStriker) selectedStriker.style.display = 'none';
            if (strikerArea) strikerArea.classList.remove('has-selection');
            
            // Update button state
            updateConfirmButton();
        }

        function removeNonStriker() {
            if (!selectedNonStrikerId) return;
            
            // Remove from UI
            const playerItem = document.querySelector(`.opening-player-item[data-player-id="${selectedNonStrikerId}"]`);
            if (playerItem) {
                playerItem.classList.remove('selected');
            }
            
            selectedNonStrikerId = null;
            
            const nonStrikerPlaceholder = document.getElementById('nonStrikerPlaceholder');
            const selectedNonStriker = document.getElementById('selectedNonStriker');
            const nonStrikerArea = document.querySelector('#nonStrikerSelection .player-selection-area');
            
            if (nonStrikerPlaceholder) nonStrikerPlaceholder.style.display = 'block';
            if (selectedNonStriker) selectedNonStriker.style.display = 'none';
            if (nonStrikerArea) nonStrikerArea.classList.remove('has-selection');
            
            // Update button state
            updateConfirmButton();
        }

        function cancelOpeningPair() {
            Swal.fire({
                title: 'Cancel Selection?',
                text: 'Are you sure you want to cancel? You need to select 2 opening batsmen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No, Continue'
            }).then((result) => {
                if (result.isConfirmed) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('openingPairModal'));
                    if (modal) {
                        modal.hide();
                    }
                }
            });
        }

        function confirmOpeningPair() {
            if (!selectedStrikerId || !selectedNonStrikerId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Selection',
                    text: 'Please select both Striker and Non-Striker',
                    confirmButtonColor: '#F59E0B'
                });
                return;
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('openingPairModal'));
            if (modal) {
                modal.hide();
            }
            
            // Force remove modal backdrop immediately
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                    backdrop.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 100);
            
            // Wait for modal to close completely
            setTimeout(() => {
                // Show loading
                showLoading();
                
                // Add both batsmen via AJAX
                const formData = new URLSearchParams({
                    ajax: '1',
                    action: 'add_opening_pair',
                    striker_id: selectedStrikerId,
                    non_striker_id: selectedNonStrikerId
                });
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // IMMEDIATELY force enable the page (don't wait for refresh)
                        setTimeout(() => {
                            forceEnablePageInteraction();
                        }, 50);
                        
                        // Then refresh the page data
                        refreshPageData();
                        
                        // No success alert - user can see the batsmen appear immediately
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.error || 'Error adding opening pair',
                            confirmButtonColor: '#F59E0B'
                        });
                        hideLoading();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error adding opening pair. Please try again.',
                        confirmButtonColor: '#F59E0B'
                    });
                    hideLoading();
                });
            }, 200); // Wait for modal to close
        }
    </script>
</body>
</html>