<?php
require_once 'config.php';

// Handle team addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_team'])) {
    $team_name = trim($_POST['team_name']);
    
    if (!empty($team_name)) {
        $sql = "INSERT INTO teams (team_name) VALUES (?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $team_name);
        mysqli_stmt_execute($stmt);
        
        header("Location: manage_teams.php");
        exit();
    }
}

// Handle team deletion
if (isset($_GET['delete'])) {
    $team_id = intval($_GET['delete']);
    $sql = "DELETE FROM teams WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $team_id);
    mysqli_stmt_execute($stmt);
    
    header("Location: manage_teams.php");
    exit();
}

// Get all teams
$teams_sql = "SELECT t.*, COUNT(p.id) as player_count 
              FROM teams t 
              LEFT JOIN players p ON t.id = p.team_id 
              GROUP BY t.id 
              ORDER BY t.team_name";
$teams = mysqli_query($conn, $teams_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teams</title>
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
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }
        input[type="text"]:focus {
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
        .teams-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .team-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .team-item:last-child {
            border-bottom: none;
        }
        .team-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .team-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        .team-details h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .team-details p {
            font-size: 14px;
            color: #666;
        }
        .team-actions {
            display: flex;
            gap: 10px;
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
    </style>
</head>
<body>
    <div class="header">
        <button class="back-btn" onclick="window.location.href='index.php'">←</button>
        <h2>Manage Teams</h2>
    </div>

    <div class="container">
        <div class="add-form">
            <h3 style="margin-bottom: 20px;">Add New Team</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Team Name</label>
                    <input type="text" name="team_name" placeholder="Enter team name" required>
                </div>
                <button type="submit" name="add_team" class="btn btn-primary">Add Team</button>
            </form>
        </div>

        <div class="teams-list">
            <h3 style="margin-bottom: 20px;">All Teams</h3>
            <?php if (mysqli_num_rows($teams) > 0): ?>
                <?php while ($team = mysqli_fetch_assoc($teams)): ?>
                    <div class="team-item">
                        <div class="team-info">
                            <div class="team-icon">
                                <?= strtoupper(substr($team['team_name'], 0, 2)) ?>
                            </div>
                            <div class="team-details">
                                <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                                <p><?= $team['player_count'] ?> players</p>
                            </div>
                        </div>
                        <div class="team-actions">
                            <button onclick="window.location.href='manage_players.php?team_id=<?= $team['id'] ?>'" class="btn btn-primary btn-small">
                                View Players
                            </button>
                            <button onclick="if(confirm('Delete this team?')) window.location.href='?delete=<?= $team['id'] ?>'" class="btn btn-danger btn-small">
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No teams yet. Add your first team above!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>