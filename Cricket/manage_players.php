<?php
require_once 'config.php';

$team_id = $_GET['team_id'] ?? null;

// Get team details
if ($team_id) {
    $team_sql = "SELECT * FROM teams WHERE id = ?";
    $stmt = mysqli_prepare($conn, $team_sql);
    mysqli_stmt_bind_param($stmt, "i", $team_id);
    mysqli_stmt_execute($stmt);
    $team = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Handle player addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_player'])) {
    $player_name = trim($_POST['player_name']);
    $selected_team_id = $_POST['team_id'];
    
    if (!empty($player_name) && !empty($selected_team_id)) {
        $sql = "INSERT INTO players (player_name, team_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $player_name, $selected_team_id);
        mysqli_stmt_execute($stmt);
        
        header("Location: manage_players.php?team_id=" . $selected_team_id);
        exit();
    }
}

// Handle player deletion
if (isset($_GET['delete'])) {
    $player_id = intval($_GET['delete']);
    $sql = "DELETE FROM players WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $player_id);
    mysqli_stmt_execute($stmt);
    
    header("Location: manage_players.php?team_id=" . $team_id);
    exit();
}

// Get all teams for dropdown
$teams_sql = "SELECT * FROM teams ORDER BY team_name";
$teams = mysqli_query($conn, $teams_sql);

// Get players
if ($team_id) {
    $players_sql = "SELECT * FROM players WHERE team_id = ? ORDER BY player_name";
    $stmt = mysqli_prepare($conn, $players_sql);
    mysqli_stmt_bind_param($stmt, "i", $team_id);
    mysqli_stmt_execute($stmt);
    $players = mysqli_stmt_get_result($stmt);
} else {
    $players_sql = "SELECT p.*, t.team_name FROM players p 
                    LEFT JOIN teams t ON p.team_id = t.id 
                    ORDER BY t.team_name, p.player_name";
    $players = mysqli_query($conn, $players_sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Players</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: #d32f2f;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }
        .back-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            margin-right: 15px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .add-form {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #d32f2f;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #d32f2f;
            color: white;
        }
        .btn-primary:hover {
            background: #b71c1c;
        }
        .players-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .player-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .player-item:last-child {
            border-bottom: none;
        }
        .player-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .player-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        .player-details h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .player-details p {
            font-size: 14px;
            color: #666;
        }
        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
        }
        .btn-danger {
            background: #f44336;
            color: white;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .team-filter {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="back-btn" onclick="window.location.href='<?= $team_id ? 'manage_teams.php' : 'index.php' ?>'">←</button>
        <h2><?= $team ? htmlspecialchars($team['team_name']) . ' - ' : '' ?>Manage Players</h2>
    </div>

    <div class="container">
        <?php if (!$team_id): ?>
        <div class="team-filter">
            <label>Filter by Team</label>
            <select onchange="if(this.value) window.location.href='?team_id=' + this.value; else window.location.href='manage_players.php'">
                <option value="">All Teams</option>
                <?php 
                mysqli_data_seek($teams, 0);
                while ($t = mysqli_fetch_assoc($teams)): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['team_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="add-form">
            <h3 style="margin-bottom: 20px;">Add New Player</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Player Name</label>
                    <input type="text" name="player_name" placeholder="Enter player name" required>
                </div>
                <div class="form-group">
                    <label>Team</label>
                    <select name="team_id" required>
                        <option value="">Select Team</option>
                        <?php 
                        mysqli_data_seek($teams, 0);
                        while ($t = mysqli_fetch_assoc($teams)): ?>
                            <option value="<?= $t['id'] ?>" <?= $team_id == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['team_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="add_player" class="btn btn-primary">Add Player</button>
            </form>
        </div>

        <div class="players-list">
            <h3 style="margin-bottom: 20px;">
                <?= $team ? htmlspecialchars($team['team_name']) . ' ' : 'All ' ?>Players
            </h3>
            <?php if (mysqli_num_rows($players) > 0): ?>
                <?php while ($player = mysqli_fetch_assoc($players)): ?>
                    <div class="player-item">
                        <div class="player-info">
                            <div class="player-icon">
                                <?= strtoupper(substr($player['player_name'], 0, 1)) ?>
                            </div>
                            <div class="player-details">
                                <h3><?= htmlspecialchars($player['player_name']) ?></h3>
                                <?php if (!$team_id): ?>
                                    <p><?= htmlspecialchars($player['team_name'] ?? 'No Team') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button onclick="if(confirm('Delete this player?')) window.location.href='?delete=<?= $player['id'] ?>&team_id=<?= $team_id ?>'" class="btn btn-danger btn-small">
                            Delete
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No players yet. Add your first player above!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>