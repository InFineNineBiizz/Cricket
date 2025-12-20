<?php
    ob_start();
    session_start();
    include "connection.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
    }

    // ================= AJAX HANDLER =================
    if (isset($_POST['action'])) {

        // ADD PLAYER
        if ($_POST['action'] === 'add_player') {

            $season_id = intval($_POST['season_id']);
            $fname     = mysqli_real_escape_string($conn, $_POST['fname']);
            $lname     = mysqli_real_escape_string($conn, $_POST['lname']);
            $number    = mysqli_real_escape_string($conn, $_POST['phone']);
            $role = isset($_POST['role']) ? implode(',', $_POST['role']) : '';
            $batstyle  = mysqli_real_escape_string($conn, $_POST['batstyle']);
            $bowlstyle = mysqli_real_escape_string($conn, $_POST['bowlstyle']);
            $tname     = mysqli_real_escape_string($conn, $_POST['tname']);
            $tnumber   = mysqli_real_escape_string($conn, $_POST['tnumber']);
            $tsize     = mysqli_real_escape_string($conn, $_POST['tsize']);

            // Image upload
            $logo = '';
            if (!empty($_FILES['image']['name'])) {
                $logo = time() . '_' . $_FILES['image']['name'];
                $upload_path = "../assets/images/" . $logo;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    echo json_encode(['status' => 'error', 'message' => 'Image upload failed']);
                    exit;
                }
            }

            // INSERT PLAYER
            $q1 = "INSERT INTO players
                    (fname,lname,number,logo,role,batstyle,bowlstyle,tname,tsize,tnumber)
                VALUES
                    ('$fname','$lname','$number','$logo','$role','$batstyle','$bowlstyle','$tname','$tsize','$tnumber')";

            if (mysqli_query($conn, $q1)) {

                $player_id = mysqli_insert_id($conn);

                // MAP PLAYER TO SEASON
                $q2 = "INSERT INTO season_players (season_id, player_id)
                    VALUES ('$season_id','$player_id')";
                    
                if (mysqli_query($conn, $q2)) {
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Player added successfully',
                        'player_id' => $player_id
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'Failed to link player to season: ' . mysqli_error($conn)
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Database error: ' . mysqli_error($conn)
                ]);
            }
            exit;
        }

        // FETCH PLAYERS         
        if ($_POST['action'] === 'fetch_players') {
            $season_id = intval($_POST['season_id']);

            $q = "SELECT p.*, sp.id AS spid
                FROM season_players sp
                JOIN players p ON p.id = sp.player_id
                WHERE sp.season_id = '$season_id'
                ORDER BY p.id ";

            $res = mysqli_query($conn, $q);
            
            if (!$res) {
                echo json_encode(['error' => mysqli_error($conn)]);
                exit;
            }
            
            $data = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $data[] = $row;
            }

            echo json_encode($data);
            exit;
        }

        // DELETE PLAYER
        if ($_POST['action'] === 'delete_player') {
            $player_id = intval($_POST['player_id']);

            mysqli_query($conn, "DELETE FROM season_players WHERE player_id='$player_id'");
            mysqli_query($conn, "DELETE FROM players WHERE id='$player_id'");

            echo json_encode(['status' => 'deleted']);
            exit;
        }
        // EDIT/UPDATE PLAYER
        if ($_POST['action'] === 'update_player') {
            $player_id = intval($_POST['player_id']);
            $fname     = mysqli_real_escape_string($conn, $_POST['fname']);
            $lname     = mysqli_real_escape_string($conn, $_POST['lname']);
            $number    = mysqli_real_escape_string($conn, $_POST['phone']);
            $role = isset($_POST['role']) ? implode(',', $_POST['role']) : '';
            $batstyle  = mysqli_real_escape_string($conn, $_POST['batstyle']);
            $bowlstyle = mysqli_real_escape_string($conn, $_POST['bowlstyle']);
            $tname     = mysqli_real_escape_string($conn, $_POST['tname']);
            $tnumber   = mysqli_real_escape_string($conn, $_POST['tnumber']);
            $tsize     = mysqli_real_escape_string($conn, $_POST['tsize']);

            // Image upload (if new image provided)
            $logo_update = '';
            if (!empty($_FILES['image']['name'])) {
                $logo = time() . '_' . $_FILES['image']['name'];
                $upload_path = "../assets/images/" . $logo;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $logo_update = ", logo='$logo'";
                }
            }

            // UPDATE PLAYER
            $q = "UPDATE players SET
                    fname='$fname',
                    lname='$lname',
                    number='$number',
                    role='$role',
                    batstyle='$batstyle',
                    bowlstyle='$bowlstyle',
                    tname='$tname',
                    tsize='$tsize',
                    tnumber='$tnumber'
                    $logo_update
                WHERE id='$player_id'";

            if (mysqli_query($conn, $q)) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Player updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Update failed: ' . mysqli_error($conn)
                ]);
            }
            exit;
        }

        // GET SINGLE PLAYER DATA
        if ($_POST['action'] === 'get_player') {
            $player_id = intval($_POST['player_id']);
            
            $q = "SELECT * FROM players WHERE id='$player_id'";
            $res = mysqli_query($conn, $q);
            
            if ($row = mysqli_fetch_assoc($res)) {
                echo json_encode($row);
            } else {
                echo json_encode(['error' => 'Player not found']);
            }
            exit;
        }
        exit;
    }

    $name=$tour_id=$sea_id=$venue=$sdate=$edate=$logo=$ctype=$max=$min=$reserve=$camt=$bidamt=$bprice=$img="";
    $tour_name=$s_date=$e_date=$sname="";

    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $str="select a.*,t.name as tour_name,s.name as sname,s.sdate as start_date,s.edate as end_date 
        from auctions a,tournaments t,seasons s where a.tour_id=t.tid and a.sea_id=s.id and sea_id='".$id."'";
        
        $res=mysqli_query($conn,$str);
        $row=mysqli_fetch_assoc($res);
        $name=$row['name'];
        $logo = $row['logo'];
        $tour_id=$row['tour_id'];
        $sea_id=$row['sea_id'];
        $venue=$row['venue'];        
        $sdate=$row['sdate'];
        $edate=$row['edate'];
        $ctype=$row['credit_type'];
        $min=$row['minplayer'];
        $max=$row['maxplayer'];
        $reserve=$row['resplayer'];
        $camt=$row['camt'];
        $bidamt=$row['bidamt'];
        $bprice=$row['bprice'];
        $tour_name=$row['tour_name'];
        $s_date=$row['start_date'];
        $e_date=$row['end_date'];
        $sname=$row['sname'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tour_name;?> - Manage</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <link rel="stylesheet" href="../assets/css/player-style.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">
    <script src="../assets/script/sweetalert2.js"></script>
    <!-- <script src="../assets/script/jquery.min.js"></script> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
    
</head>
<body>
    <!-- Top Navigation -->
    <?php
        include 'topbar.php';
    ?>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <?php
            include 'sidebar.php';
        ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            
            <?php include "auc_topbar.php";?>
            
            <div class="container">

                <?php include "auc_header.php";?>

                <!-- Bottom Tabs -->
                <div class="bottom-tabs">
                    <a href="tour-manage.php?id=<?php echo $id;?>" class="bottom-tab">Teams</a>
                    <a href="player.php?id=<?php echo $id;?>" class="bottom-tab active">Players</a>
                    <a href="information.php?id=<?php echo $id;?>" class="bottom-tab">Information</a>
                </div>

                <!-- Players Section Header -->
                <div class="players-section-header">
                    <div class="players-section-actions">
                        <button class="btn-add-player">
                            <i class="fas fa-plus-circle"></i>
                            ADD PLAYER
                        </button>
                        <button class="btn-dropdown">
                            <i class="fas fa-file-import"></i>
                            Import/Export
                            <i class="fas fa-chevron-down"></i>
                        </button>                        
                        <button class="btn-dropdown" onclick="window.print()">
                            <i class="fas fa-file-pdf"></i>
                            Download PDF
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>

                <!-- Players Filter Section -->
                <div class="players-filter-section">
                    <div class="total-players">Total Players: <span id="totalPlayersCount">1</span></div>
                    <input type="text" class="search-box" id="searchBox" placeholder="Search by name or number...">
                </div>

                <!-- Price Section -->
                <div class="price-section">
                    <h3>Same Price For All Players</h3>
                    <div class="base-price-badge">Base Price: <?php echo $bprice ." ".$ctype;?></div>
                </div>

                <!-- Players Grid -->
                <div class="players-grid" id="playersGrid"></div>
            </div>
        </div>
    </div>

    <!-- Add Player Modal -->
    <div id="addPlayerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Player Details</h2>
                <span class="close-modal">&times;</span>
            </div>
            <input type="hidden" id="editPlayerId" value="">
            <div class="modal-body">
                <div class="modal-form-row">
                    <div class="form-group full-width">
                        <label>Phone*</label>
                        <input type="tel" id="playerPhone" class="form-input" placeholder="Enter phone number" />
                    </div>
                </div>
                
                <div class="modal-form-row two-cols">
                    <div class="form-group">
                        <label>First Name*</label>
                        <input type="text" id="playerFirstName" class="form-input" placeholder="Enter First Name" />
                    </div>
                    <div class="form-group">
                        <label>Last Name*</label>
                        <input type="text" id="playerLastName" class="form-input" placeholder="Enter Last Name" />
                    </div>
                </div>

                <div class="modal-form-row">
                    <div class="player-image-section">
                        <div class="player-image-placeholder" id="playerImagePreview">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="file" id="playerImageInput" accept="image/*" style="display: none;">
                        <button class="btn-change-picture" id="changePictureBtn">
                            <i class="fas fa-camera"></i>
                            Change Picture
                        </button>
                    </div>
                </div>                

                <!-- Skills Section with Modern Checkboxes -->
                <div class="form-section">
                    <h5 class="section-label">All Rounder</h5>
                    <div class="button-group">
                        <input type="checkbox" class="btn-check role-check" name="roles[]" id="modal-allrounder" value="All Rounder">
                        <label class="btn btn-outline-primary" for="modal-allrounder">All Rounder</label>
                    </div>
                </div>

                <div class="form-section">
                    <h5 class="section-label">Batter</h5>
                    <div class="button-group">
                        <input type="checkbox" class="btn-check parent" name="roles[]" id="modal-batsman" value="Batsman">
                        <label class="btn btn-outline-primary" for="modal-batsman">Batsman</label>

                        <input type="checkbox" class="btn-check child-bat" data-parent="modal-batsman" name="bat_type[]" id="modal-rightbat" value="RHB">
                        <label class="btn btn-outline-primary" for="modal-rightbat">Right Hand Bat</label>

                        <input type="checkbox" class="btn-check child-bat" data-parent="modal-batsman" name="bat_type[]" id="modal-leftbat" value="LHB">
                        <label class="btn btn-outline-primary" for="modal-leftbat">Left Hand Bat</label>
                    </div>
                </div>

                <div class="form-section">
                    <h5 class="section-label">Bowler</h5>
                    <div class="button-group">
                        <input type="checkbox" class="btn-check parent" name="roles[]" id="modal-bowler" value="Bowler">
                        <label class="btn btn-outline-primary" for="modal-bowler">Bowler</label>

                        <input type="checkbox" class="btn-check child-bowl" data-parent="modal-bowler" name="bowl_type[]" id="modal-rightfast" value="Right Arm Fast">
                        <label class="btn btn-outline-primary" for="modal-rightfast">Right-Arm-Fast</label>

                        <input type="checkbox" class="btn-check child-bowl" data-parent="modal-bowler" name="bowl_type[]" id="modal-rightmedium" value="Right Arm Medium">
                        <label class="btn btn-outline-primary" for="modal-rightmedium">Right-Arm-Medium</label>

                        <input type="checkbox" class="btn-check child-bowl" data-parent="modal-bowler" name="bowl_type[]" id="modal-rightoff" value="Right Arm Off Break">
                        <label class="btn btn-outline-primary" for="modal-rightoff">Right-Arm-Off-Break</label>

                        <input type="checkbox" class="btn-check child-bowl" data-parent="modal-bowler" name="bowl_type[]" id="modal-rightleg" value="Right Arm Leg Break">
                        <label class="btn btn-outline-primary" for="modal-rightleg">Right-Arm-Leg-Break</label>

                        <input type="checkbox" class="btn-check child-bowl" data-parent="modal-bowler" name="bowl_type[]" id="modal-leftfast" value="Left Arm Fast">
                        <label class="btn btn-outline-primary" for="modal-leftfast">Left-Arm-Fast</label>

                        <input type="checkbox" class="btn-check child-bowl" data-parent="modal-bowler" name="bowl_type[]" id="modal-leftmedium" value="Left Arm Medium">
                        <label class="btn btn-outline-primary" for="modal-leftmedium">Left-Arm-Medium</label>

                        <input type="checkbox" class="btn-check child-bowl" data-parent="modal-bowler" name="bowl_type[]" id="modal-leftorthodox" value="Left Arm Orthodox">
                        <label class="btn btn-outline-primary" for="modal-leftorthodox">Left-Arm-Orthodox</label>
                    </div>
                </div>

                <div class="form-section">
                    <h5 class="section-label">Wicket Keeper</h5>
                    <div class="button-group">
                        <input type="checkbox" class="btn-check parent" name="roles[]" id="modal-wk" value="WK">
                        <label class="btn btn-outline-primary" for="modal-wk">Wicket Keeper</label>

                        <input type="checkbox" class="btn-check child-wk" data-parent="modal-wk" name="wk_type[]" id="modal-wkbat" value="WK-Batsman">
                        <label class="btn btn-outline-primary" for="modal-wkbat">WK-Batsman</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <label class="section-label">T-Shirt Details</label>
                    <div class="modal-form-row two-cols">
                        <div class="form-group">
                            <label>T-Shirt Name</label>
                            <input type="text" id="playerTshirtName" name="tname" class="form-input" placeholder="Enter T-Shirt Name" />
                        </div>
                        <div class="form-group">
                            <label>T-Shirt Number</label>
                            <input type="text" id="playerTshirtNumber" name="tnumber" class="form-input" placeholder="Enter T-Shirt Number" />
                        </div>
                    </div>
                    <div class="modal-form-row">
                        <div class="form-group full-width">
                            <label>T-Shirt Size</label>
                            <select id="playerTshirtSize" name="tsize" class="form-input">
                                <option value="" selected disabled>Select Size</option>                                
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                                <option value="XXXL">XXXL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel">Cancel</button>
                <button type="button" class="btn-save" onclick="event.stopPropagation(); savePlayer();">Save</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let playerImageUrl = null;
            let playerCount = 5;

            const modal = document.getElementById('addPlayerModal');
            const addPlayerBtn = document.querySelector('.btn-add-player');
            const closeModal = document.querySelector('.close-modal');
            const cancelBtn = document.querySelector('.btn-cancel');
            const changePictureBtn = document.getElementById('changePictureBtn');
            const playerImageInput = document.getElementById('playerImageInput');
            const playerImagePreview = document.getElementById('playerImagePreview');

            addPlayerBtn.addEventListener('click', function() {
                modal.style.display = 'block';
                resetForm();
            });

            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });

            changePictureBtn.addEventListener('click', function() {
                playerImageInput.click();
            });

            playerImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        playerImagePreview.innerHTML =
                            `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                    };
                    reader.readAsDataURL(file);
                }
            });

            document.querySelector('.modal-content').addEventListener('click', function(e) {
                e.stopPropagation();
            });

        });
        
        // Reset form function - IMPORTANT!
        function resetForm() {
            document.getElementById('playerPhone').value = '';
            document.getElementById('playerFirstName').value = '';
            document.getElementById('playerLastName').value = '';
            document.getElementById('playerTshirtName').value = '';
            document.getElementById('playerTshirtNumber').value = '';
            document.getElementById('playerTshirtSize').value = '';
            
            const playerImagePreview = document.getElementById('playerImagePreview');
            playerImagePreview.innerHTML = '<i class="fas fa-user"></i>';
            document.getElementById('playerImageInput').value = '';
            
            jQuery('input[type="checkbox"].btn-check').prop('checked', false);
            jQuery('.form-input').css('border-color', '#ddd');
            jQuery('.error-msg').remove();
            
            // Reset edit mode
            isEditMode = false;
            jQuery('#editPlayerId').val('');
            jQuery('#modalTitle').text('Add Player Details');
        }
        
        // Update player numbers after deletion
        function updatePlayerNumbers() {
            const playerCards = document.querySelectorAll('.player-card');
            playerCards.forEach((card, index) => {
                const playerNumber = card.querySelector('.player-number');
                playerNumber.textContent = index + 1;
                card.setAttribute('data-player-number', index + 1);
            });
        }                
    </script>

    <script>
        // Checkbox interaction logic for skills
        jQuery(document).ready(function() {
            
            // AUTO SELECT PARENT IF CHILD CLICKED
            jQuery(".child-bat").on("change", function () {
                if(this.checked) {
                    jQuery("#modal-batsman").prop("checked", true);
                }
            });

            jQuery(".child-bowl").on("change", function () {
                if(this.checked) {
                    jQuery("#modal-bowler").prop("checked", true);
                }
            });

            jQuery(".child-wk").on("change", function () {
                if(this.checked) {
                    jQuery("#modal-wk").prop("checked", true);
                }
            });

            // ONLY ONE CHILD PER GROUP
            jQuery(".child-bat").on("change", function () {
                if(this.checked) {
                    jQuery(".child-bat").not(this).prop("checked", false);
                }
            });

            jQuery(".child-bowl").on("change", function () {
                if(this.checked) {
                    jQuery(".child-bowl").not(this).prop("checked", false);
                }
            });

            jQuery(".child-wk").on("change", function () {
                if(this.checked) {
                    jQuery(".child-wk").not(this).prop("checked", false);
                }
            });

            // UNCHECK PARENT → REMOVE CHILDREN
            jQuery("#modal-batsman").on("change", function() {
                if (!this.checked) {
                    jQuery(".child-bat").prop("checked", false);
                }
            });

            jQuery("#modal-bowler").on("change", function() {
                if (!this.checked) {
                    jQuery(".child-bowl").prop("checked", false);
                }
            });

            jQuery("#modal-wk").on("change", function() {
                if (!this.checked) {
                    jQuery(".child-wk").prop("checked", false);
                }
            });
            
        });
    </script>

    <script>        
        // Live validation using jQuery
        jQuery(document).ready(function() {
            
            // Phone validation - only numbers, 10 digits
            jQuery('#playerPhone').on('input', function() {
                let phone = jQuery(this).val().replace(/\D/g, '');
                jQuery(this).val(phone);
                
                if (phone.length === 0) {
                    jQuery(this).css('border-color', '#dc3545');
                    if (!jQuery(this).next('.error-msg').length) {
                        jQuery(this).after('<span class="error-msg" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">Phone number is required</span>');
                    }
                } else if (phone.length < 10) {
                    jQuery(this).css('border-color', '#dc3545');
                    if (!jQuery(this).next('.error-msg').length) {
                        jQuery(this).after('<span class="error-msg" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">Phone number must be 10 digits</span>');
                    } else {
                        jQuery(this).next('.error-msg').text('Phone number must be 10 digits');
                    }
                } else if (phone.length === 10) {
                    jQuery(this).css('border-color', '#28a745');
                    jQuery(this).next('.error-msg').remove();
                } else {
                    jQuery(this).val(phone.substring(0, 10));
                }
            });

            // First Name validation - only letters and spaces
            jQuery('#playerFirstName').on('input', function() {
                let value = jQuery(this).val().replace(/[^a-zA-Z\s]/g, '');
                jQuery(this).val(value);
                
                if (value.length === 0) {
                    jQuery(this).css('border-color', '#dc3545');
                    if (!jQuery(this).next('.error-msg').length) {
                        jQuery(this).after('<span class="error-msg" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">First name is required</span>');
                    }
                } else if (value.length < 2) {
                    jQuery(this).css('border-color', '#dc3545');
                    if (!jQuery(this).next('.error-msg').length) {
                        jQuery(this).after('<span class="error-msg" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">First name must be at least 2 characters</span>');
                    } else {
                        jQuery(this).next('.error-msg').text('First name must be at least 2 characters');
                    }
                } else {
                    jQuery(this).css('border-color', '#28a745');
                    jQuery(this).next('.error-msg').remove();
                }
            });

            // Last Name validation - only letters and spaces
            jQuery('#playerLastName').on('input', function() {
                let value = jQuery(this).val().replace(/[^a-zA-Z\s]/g, '');
                jQuery(this).val(value);
                
                if (value.length === 0) {
                    jQuery(this).css('border-color', '#dc3545');
                    if (!jQuery(this).next('.error-msg').length) {
                        jQuery(this).after('<span class="error-msg" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">Last name is required</span>');
                    }
                } else if (value.length < 2) {
                    jQuery(this).css('border-color', '#dc3545');
                    if (!jQuery(this).next('.error-msg').length) {
                        jQuery(this).after('<span class="error-msg" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">Last name must be at least 2 characters</span>');
                    } else {
                        jQuery(this).next('.error-msg').text('Last name must be at least 2 characters');
                    }
                } else {
                    jQuery(this).css('border-color', '#28a745');
                    jQuery(this).next('.error-msg').remove();
                }
            });

            // T-Shirt validations...
            // [Keep all your existing T-shirt validation code]
            
            // Reset validation on modal close
            jQuery('.close-modal, .btn-cancel').on('click', function() {
                jQuery('.form-input').css('border-color', '#ddd');
                jQuery('.error-msg').remove();
            });

        });
    </script>

    <script>        
        const SEASON_ID = <?= $sea_id ?>;
        
        function loadPlayers() {
            console.log('Loading players for season:', SEASON_ID); // DEBUG
            
            jQuery.ajax({
                url: 'player.php',
                type: 'POST',
                data: { 
                    action: 'fetch_players', 
                    season_id: SEASON_ID 
                },
                dataType: 'json',
                success: function(players) {
                    console.log('Players loaded:', players); // DEBUG
                    
                    let html = '';
                    jQuery('#totalPlayersCount').text(players.length);

                    if (players.length === 0) {
                        html = '<div style="text-align:center;padding:20px;color:#999;">No players found. Add your first player!</div>';
                    } else {
                        players.forEach((p, i) => {
                            let roleDisplay = '';
                            if (p.role) {
                                let roles = p.role.split(',');
                                roleDisplay = roles.map(role => `⊙ ${role.trim()}`).join('<br>');
                            }
                            
                            let skillsDisplay = '';
                            if (p.batstyle && p.bowlstyle) {
                                skillsDisplay = `⊙ ${p.batstyle} | ${p.bowlstyle}`;
                            } else if (p.batstyle) {
                                skillsDisplay = `⊙ ${p.batstyle}`;
                            } else if (p.bowlstyle) {
                                skillsDisplay = `⊙ ${p.bowlstyle}`;
                            }
                            
                            html += `
                            <div class="player-card">
                                <div class="player-number">${i + 1}</div>
                                <div class="player-avatar">
                                    ${p.logo ? `<img src="../assets/images/${p.logo}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">` : `<i class="fas fa-user"></i>`}
                                </div>
                                <div class="player-info">
                                    <div class="player-name">${p.fname} ${p.lname}</div>
                                    <div class="player-detail-item">
                                        <i class="fas fa-phone"></i> ${p.number}
                                    </div>
                                    <div class="player-detail-item" style="line-height: 1.6;">
                                        ${roleDisplay}
                                        ${skillsDisplay ? '<br>' + skillsDisplay : ''}
                                    </div>
                                </div>
                                <div class="player-actions">
                                    <div class="player-actions">
                                        <button class="btn-player-action btn-edit" data-id="${p.id}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="btn-player-action btn-delete" data-id="${p.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                        });
                    }

                    jQuery('#playersGrid').html(html);
                    console.log('Players grid updated!'); // DEBUG
                },
                error: function(xhr, status, error) {
                    console.error('Load Players Error:', error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        // Load players when page loads
        jQuery(document).ready(function() {
            console.log('Document ready, loading players...'); // DEBUG
            loadPlayers();
        });
    </script>

    <script>
        function savePlayer() {
            console.log('Save clicked');

            const phone = jQuery('#playerPhone').val().trim();
            const fname = jQuery('#playerFirstName').val().trim();
            const lname = jQuery('#playerLastName').val().trim();

            if (phone.length !== 10) {
                Swal.fire('Error', 'Phone must be 10 digits', 'error');
                return;
            }

            if (fname.length < 2 || lname.length < 2) {
                Swal.fire('Error', 'Enter valid first & last name', 'error');
                return;
            }

            const roleChecked =
                jQuery('input[name="roles[]"]:checked').length ||
                jQuery('input[name="bat_type[]"]:checked').length ||
                jQuery('input[name="bowl_type[]"]:checked').length ||
                jQuery('input[name="wk_type[]"]:checked').length;

            if (!roleChecked) {
                Swal.fire('Error', 'Select at least one skill', 'error');
                return;
            }

            let roles = [];
            jQuery('input[name="roles[]"]:checked').each(function () {
                roles.push(this.value);
            });

            let fd = new FormData();
            
            // Check if edit mode
            let editId = jQuery('#editPlayerId').val();
            if (editId) {
                fd.append('action', 'update_player');
                fd.append('player_id', editId);
            } else {
                fd.append('action', 'add_player');
                fd.append('season_id', SEASON_ID);
            }
            
            fd.append('fname', fname);
            fd.append('lname', lname);
            fd.append('phone', phone);
            
            roles.forEach(function(role) {
                fd.append('role[]', role);
            });
            
            fd.append('batstyle', jQuery('input[name="bat_type[]"]:checked').val() || '');
            fd.append('bowlstyle', jQuery('input[name="bowl_type[]"]:checked').val() || '');
            fd.append('tname', jQuery('#playerTshirtName').val());
            fd.append('tnumber', jQuery('#playerTshirtNumber').val());
            fd.append('tsize', jQuery('#playerTshirtSize').val());

            if (jQuery('#playerImageInput')[0].files.length) {
                fd.append('image', jQuery('#playerImageInput')[0].files[0]);
            }

            Swal.fire({
                title: editId ? 'Updating Player...' : 'Adding Player...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            jQuery.ajax({
                url: 'player.php',
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    console.log('Save Response:', res);
                    
                    if (res.status === 'success') {
                        jQuery('#addPlayerModal').hide();
                        resetForm();
                        isEditMode = false;
                        jQuery('#editPlayerId').val('');
                        jQuery('#modalTitle').text('Add Player Details');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: editId ? 'Player Updated Successfully' : 'Player Added Successfully',
                            timer: 1500,
                            showConfirmButton: false,
                            timerProgressBar: true,
                        }).then(() => {
                            loadPlayers();
                        });
                        
                    } else {
                        Swal.fire('Error', res.message || 'Operation failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    Swal.fire('Error', 'Request failed. Check console.', 'error');
                }
            });
        }
    </script>

    <script>
        jQuery(document).on('click', '.btn-delete', function () {
            let pid = jQuery(this).data('id');

            Swal.fire({
                title: 'Delete Player?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    jQuery.post('player.php', {
                        action: 'delete_player',
                        player_id: pid
                    }, function () {
                        Swal.fire('Deleted!', 'Player removed', 'success');
                        loadPlayers();
                    });
                }
            });
        });
    </script>
    <script>
        let isEditMode = false;

        // Handle Edit Button Click
        jQuery(document).on('click', '.btn-edit', function () {
            let pid = jQuery(this).data('id');
            isEditMode = true;
            
            jQuery('#modalTitle').text('Edit Player Details');
            jQuery('#editPlayerId').val(pid);
            
            // Fetch player data
            jQuery.ajax({
                url: 'player.php',
                type: 'POST',
                data: { 
                    action: 'get_player', 
                    player_id: pid 
                },
                dataType: 'json',
                success: function(player) {
                    // Fill form with player data
                    jQuery('#playerPhone').val(player.number);
                    jQuery('#playerFirstName').val(player.fname);
                    jQuery('#playerLastName').val(player.lname);
                    jQuery('#playerTshirtName').val(player.tname);
                    jQuery('#playerTshirtNumber').val(player.tnumber);
                    jQuery('#playerTshirtSize').val(player.tsize);
                    
                    // Show existing image
                    if (player.logo) {
                        jQuery('#playerImagePreview').html(
                            `<img src="../assets/images/${player.logo}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
                        );
                    }
                    
                    // Check roles
                    if (player.role) {
                        let roles = player.role.split(',');
                        roles.forEach(function(role) {
                            role = role.trim();
                            if (role === 'All Rounder') jQuery('#modal-allrounder').prop('checked', true);
                            if (role === 'Batsman') jQuery('#modal-batsman').prop('checked', true);
                            if (role === 'Bowler') jQuery('#modal-bowler').prop('checked', true);
                            if (role === 'WK') jQuery('#modal-wk').prop('checked', true);
                            if (role === 'WK-Batsman') jQuery('#modal-wkbat').prop('checked', true);
                        });
                    }
                    
                    // Check bat style
                    if (player.batstyle) {
                        if (player.batstyle === 'RHB') jQuery('#modal-rightbat').prop('checked', true);
                        if (player.batstyle === 'LHB') jQuery('#modal-leftbat').prop('checked', true);
                    }
                    
                    // Check bowl style
                    if (player.bowlstyle) {
                        jQuery('input[name="bowl_type[]"]').each(function() {
                            if (this.value === player.bowlstyle) {
                                jQuery(this).prop('checked', true);
                            }
                        });
                    }
                    
                    // Show modal
                    jQuery('#addPlayerModal').show();
                },
                error: function(xhr, status, error) {
                    console.error('Get Player Error:', error);
                    Swal.fire('Error', 'Could not load player data', 'error');
                }
            });
        });
    </script>
    <script>
        
        // Search functionality with AJAX
        jQuery(document).ready(function() {
            let searchTimeout;
            let allPlayers = []; // Cache all players
            
            // Load and cache players initially
            function cacheAndDisplayPlayers() {
                jQuery.ajax({
                    url: 'player.php',
                    type: 'POST',
                    data: { 
                        action: 'fetch_players', 
                        season_id: SEASON_ID 
                    },
                    dataType: 'json',
                    success: function(players) {
                        allPlayers = players; // Cache the players
                        displayPlayers(players);
                    },
                    error: function(xhr, status, error) {
                        console.error('Load Players Error:', error);
                    }
                });
            }
            
            // Display players function
            function displayPlayers(players, searchTerm = '') {
                let html = '';
                jQuery('#totalPlayersCount').text(players.length);

                if (players.length === 0) {
                    if (searchTerm) {
                        html = '<div style="text-align:center;padding:40px;color:#999;"><i class="fas fa-search" style="font-size:48px;opacity:0.3;"></i><br><br>No players found matching "' + searchTerm + '"</div>';
                    } else {
                        html = '<div style="text-align:center;padding:20px;color:#999;">No players found. Add your first player!</div>';
                    }
                } else {
                    players.forEach((p, i) => {
                        let roleDisplay = '';
                        if (p.role) {
                            let roles = p.role.split(',');
                            roleDisplay = roles.map(role => `⊙ ${role.trim()}`).join('<br>');
                        }
                        
                        let skillsDisplay = '';
                        if (p.batstyle && p.bowlstyle) {
                            skillsDisplay = `⊙ ${p.batstyle} | ${p.bowlstyle}`;
                        } else if (p.batstyle) {
                            skillsDisplay = `⊙ ${p.batstyle}`;
                        } else if (p.bowlstyle) {
                            skillsDisplay = `⊙ ${p.bowlstyle}`;
                        }
                        
                        // Prepare display names
                        let displayName = p.fname + ' ' + p.lname;
                        let displayPhone = p.number;
                        
                        // Highlight matching text if searching
                        if (searchTerm) {
                            let regex = new RegExp('(' + searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                            displayName = displayName.replace(regex, '<mark style="background-color:#ffd54f;padding:2px 4px;border-radius:3px;">$1</mark>');
                            displayPhone = displayPhone.replace(regex, '<mark style="background-color:#ffd54f;padding:2px 4px;border-radius:3px;">$1</mark>');
                        }
                        
                        html += `
                        <div class="player-card">
                            <div class="player-number">${i + 1}</div>
                            <div class="player-avatar">
                                ${p.logo ? `<img src="../assets/images/${p.logo}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">` : `<i class="fas fa-user"></i>`}
                            </div>
                            <div class="player-info">
                                <div class="player-name">${displayName}</div>
                                <div class="player-detail-item">
                                    <i class="fas fa-phone"></i> ${displayPhone}
                                </div>
                                <div class="player-detail-item" style="line-height: 1.6;">
                                    ${roleDisplay}
                                    ${skillsDisplay ? '<br>' + skillsDisplay : ''}
                                </div>
                            </div>
                            <div class="player-actions">
                                <button class="btn-player-action btn-edit" data-id="${p.id}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn-player-action btn-delete" data-id="${p.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>`;
                    });
                }

                jQuery('#playersGrid').html(html);
            }
            
            // Search input handler
            jQuery('#searchBox').on('input', function() {
                clearTimeout(searchTimeout);
                
                let searchTerm = jQuery(this).val().trim();
                
                // Debounce search - wait 300ms after user stops typing
                searchTimeout = setTimeout(function() {
                    
                    if (searchTerm === '') {
                        // If search is empty, show all cached players
                        displayPlayers(allPlayers);
                        return;
                    }
                    
                    console.log('Searching for:', searchTerm); // DEBUG
                    
                    // Filter cached players
                    let searchLower = searchTerm.toLowerCase();
                    let filteredPlayers = allPlayers.filter(function(p) {
                        let fname = (p.fname || '').toLowerCase();
                        let lname = (p.lname || '').toLowerCase();
                        let fullName = fname + ' ' + lname;
                        let phone = (p.number || '').toLowerCase();
                        
                        console.log('Checking:', fullName, 'against', searchLower); // DEBUG
                        
                        return fullName.includes(searchLower) || 
                            fname.includes(searchLower) || 
                            lname.includes(searchLower) || 
                            phone.includes(searchLower);
                    });
                    
                    console.log('Found players:', filteredPlayers.length); // DEBUG
                    
                    // Display filtered results
                    displayPlayers(filteredPlayers, searchTerm);
                    
                }, 300); // Wait 300ms after user stops typing
            });
            
            // Clear search on ESC key
            jQuery('#searchBox').on('keyup', function(e) {
                if (e.keyCode === 27) { // ESC key
                    jQuery(this).val('');
                    displayPlayers(allPlayers);
                }
            });
            
            // Override the global loadPlayers function to use caching
            window.originalLoadPlayers = window.loadPlayers;
            window.loadPlayers = function() {
                cacheAndDisplayPlayers();
            };
            
            // Initial load
            cacheAndDisplayPlayers();
        });
    </script>    
    <?php ob_end_flush();?>
</body>
</html>