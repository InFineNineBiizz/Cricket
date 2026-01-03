<?php
    session_start();
    include "connection.php";

    // ================= AJAX HANDLER =================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        
        $action = $_POST['action'];

        // FETCH ORGANIZERS
        if ($action === 'fetch_organizers') {
            $season_id = intval($_POST['season_id']);
            
            $query = "SELECT o.*, so.id as so_id 
                    FROM season_organizer so 
                    INNER JOIN organizers o ON o.id = so.organizer_id 
                    WHERE so.season_id = '$season_id'
                    ORDER BY o.id";
            
            $result = mysqli_query($conn, $query);
            
            if (!$result) {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
                exit;
            }
            
            $organizers = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $organizers[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $organizers]);
            exit;
        }

        // ADD ORGANIZER
        if ($action === 'add_organizer') {
            $season_id = intval($_POST['season_id']);
            $name = mysqli_real_escape_string($conn, trim($_POST['name']));
            $email = mysqli_real_escape_string($conn, trim($_POST['email']));
            $number = mysqli_real_escape_string($conn, trim($_POST['number']));
            
            // Validate inputs
            if (empty($name) || strlen($name) < 2) {
                echo json_encode(['status' => 'error', 'message' => 'Name must be at least 2 characters']);
                exit;
            }
            
            if (empty($number) || strlen($number) != 10) {
                echo json_encode(['status' => 'error', 'message' => 'Phone number must be 10 digits']);
                exit;
            }
            
            // Check if phone number already exists
            $check_query = "SELECT id FROM organizers WHERE number = '$number'";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Phone number already exists']);
                exit;
            }
            
            // Insert into organizers table
            $insert_query = "INSERT INTO organizers (name, email, number) 
                            VALUES ('$name', '$email', '$number')";
            
            if (mysqli_query($conn, $insert_query)) {
                $organizer_id = mysqli_insert_id($conn);
                
                // Insert into season_organizer table
                $link_query = "INSERT INTO season_organizer (organizer_id, season_id) 
                            VALUES ('$organizer_id', '$season_id')";
                
                if (mysqli_query($conn, $link_query)) {
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Organizer added successfully',
                        'organizer_id' => $organizer_id
                    ]);
                } else {
                    // Rollback: Delete the organizer if linking fails
                    mysqli_query($conn, "DELETE FROM organizers WHERE id = '$organizer_id'");
                    echo json_encode(['status' => 'error', 'message' => 'Failed to link organizer to season: ' . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add organizer: ' . mysqli_error($conn)]);
            }
            exit;
        }

        // GET SINGLE ORGANIZER
        if ($action === 'get_organizer') {
            $organizer_id = intval($_POST['organizer_id']);
            
            $query = "SELECT * FROM organizers WHERE id = '$organizer_id'";
            $result = mysqli_query($conn, $query);
            
            if ($row = mysqli_fetch_assoc($result)) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Organizer not found']);
            }
            exit;
        }

        // UPDATE ORGANIZER
        if ($action === 'update_organizer') {
            $organizer_id = intval($_POST['organizer_id']);
            $name = mysqli_real_escape_string($conn, trim($_POST['name']));
            $email = mysqli_real_escape_string($conn, trim($_POST['email']));
            $number = mysqli_real_escape_string($conn, trim($_POST['number']));
            
            // Validate inputs
            if (empty($name) || strlen($name) < 2) {
                echo json_encode(['status' => 'error', 'message' => 'Name must be at least 2 characters']);
                exit;
            }
            
            if (empty($number) || strlen($number) != 10) {
                echo json_encode(['status' => 'error', 'message' => 'Phone number must be 10 digits']);
                exit;
            }
            
            // Check if phone number already exists for other organizers
            $check_query = "SELECT id FROM organizers WHERE number = '$number' AND id != '$organizer_id'";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Phone number already exists']);
                exit;
            }
            
            // Update organizer
            $update_query = "UPDATE organizers 
                            SET name = '$name', email = '$email', number = '$number' 
                            WHERE id = '$organizer_id'";
            
            if (mysqli_query($conn, $update_query)) {
                echo json_encode(['status' => 'success', 'message' => 'Organizer updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update organizer: ' . mysqli_error($conn)]);
            }
            exit;
        }

        // DELETE ORGANIZER
        if ($action === 'delete_organizer') {
            $organizer_id = intval($_POST['organizer_id']);
            $season_id = intval($_POST['season_id']);
                     
            $delete_link_query = "DELETE FROM season_organizer 
                                WHERE organizer_id = '$organizer_id' AND season_id = '$season_id'";
            
            // Then delete from organizers table
            $delete_org_query = "DELETE FROM organizers WHERE id = '$organizer_id'";
            
            if (mysqli_query($conn, $delete_link_query) && mysqli_query($conn, $delete_org_query)) {
                echo json_encode(['status' => 'success', 'message' => 'Organizer deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete organizer: ' . mysqli_error($conn)]);
            }
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

    // ================= PAGE LOAD =================
    $id = $email = $number = "";

    if(isset($_GET['id'])) {
        $id = $_GET['id'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizers | <?php echo $title_name;?></title>

    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
    <script src="../assets/script/sweetalert2.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fef6e4;
            min-height: 100vh;
        }

        /* Main Container */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content-wrapper {
            flex: 1;
            margin-left: 0;
            width: 100%;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Tournament Top Bar */
        .tournament-topbar {
            background: white;
            border-bottom: 2px solid #e0e0e0;
            padding: 0;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .tournament-topbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 0;
            padding: 0 2rem;
        }

        .topbar-tab {
            padding: 1.25rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: #95a5a6;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .topbar-tab:hover {
            color: #7f8c8d;
            background: #fef6e4;
        }

        .topbar-tab.active {
            color: #2c3e50;
            border-bottom-color: #f5a623;
        }

        /* Add Organizer Button */
        .add-organizer-btn {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
        }

        .btn-add-organizer {
            background: #5b7fd6;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-add-organizer i {
            background: white;
            color: #5b7fd6;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .btn-add-organizer:hover {
            background: #4a6bc5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 127, 214, 0.3);
        }

        /* Organizers Grid */
        .organizers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        /* Organizer Card */
        .organizer-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: relative;
            transition: all 0.3s ease;
        }

        .organizer-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .card-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .btn-card-action {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-edit-card {
            background: #4caf50;
        }

        .btn-edit-card:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .btn-delete-card {
            background: #f44336;
        }

        .btn-delete-card:hover {
            background: #da190b;
            transform: translateY(-2px);
        }

        .organizer-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }

        .organizer-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #7f8c8d;
            font-size: 1rem;
        }

        .detail-row i {
            width: 24px;
            font-size: 1.1rem;
            color: #95a5a6;
        }

        .detail-row span {
            color: #5a6c7d;
        }

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            grid-column: 1 / -1;
        }

        .empty-state-text {
            color: #7f8c8d;
            font-size: 1rem;
        }

        /* Modal Overlay */
        .modal-overlay {
            display: none !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999999;
            padding: 20px;
            overflow-y: auto;
        }

        .modal-overlay.show {
            display: flex !important;
            align-items: flex-start;
            justify-content: center;
            padding-top: 50px;
            padding-bottom: 50px;
        }

        /* Modal */
        .modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            position: relative;
        }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #7f8c8d;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s;
            line-height: 1;
            padding: 0;
        }

        .btn-close:hover {
            color: #2c3e50;
        }

        .modal-body {
            padding: 2rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .form-group label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-group label .required {
            color: #e74c3c;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            background: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #5b7fd6;
        }

        .form-control::placeholder {
            color: #bdc3c7;
        }

        /* Validation Styles */
        .form-control.valid {
            border-color: #28a745;
        }

        .form-control.invalid {
            border-color: #dc3545;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Modal Footer */
        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            position: sticky;
            bottom: 0;
            background: white;
        }

        .btn-footer {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-close-footer {
            background: #e67e22;
            color: white;
        }

        .btn-close-footer:hover {
            background: #d35400;
        }

        .btn-submit {
            background: #5b7fd6;
            color: white;
        }

        .btn-submit:hover {
            background: #4a6bc5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 127, 214, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .tournament-topbar-container {
                padding: 0 1rem;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .topbar-tab {
                padding: 1rem 1.5rem;
                font-size: 0.9rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .modal {
                width: 95%;
                margin: 10px auto;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1rem;
            }

            .organizers-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <?php include 'topbar.php'; ?>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Tournament Top Bar -->
            <div class="tournament-topbar">
                <div class="tournament-topbar-container">
                    <a href="sea-detail.php?id=<?php echo $id;?>" class="topbar-tab">
                        Season Detail
                    </a>
                    <a href="organizers.php?id=<?php echo $id;?>" class="topbar-tab active">
                        Organizers
                    </a>
                    <a href="sponsors.php?id=<?php echo $id;?>" class="topbar-tab">
                        Sponsors
                    </a>
                    <a href="tour-manage.php?id=<?php echo $id;?>" class="topbar-tab">
                        Auction
                    </a>
                </div>
            </div>

            <div class="container">
                <!-- Add Organizer Button -->
                <div class="add-organizer-btn">
                    <button class="btn-add-organizer" onclick="openModal()">
                        <i class="fas fa-plus"></i>
                        ADD ORGANIZER
                    </button>
                </div>

                <!-- Organizers Grid -->
                <div class="organizers-grid" id="organizersGrid">
                    <!-- Will be populated via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="organizerModal" onclick="closeModalOnOutside(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modalTitle">Create organizer</h2>
                <button class="btn-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="organizerForm" onsubmit="submitForm(event)">
                    <input type="hidden" id="organizerId">
                    <div class="form-grid">                        
                        <div class="form-group">
                            <label>Organizer Name<span class="required">*</span></label>
                            <input type="text" class="form-control" id="organizerName" placeholder="Enter Organizer name">
                            <span class="error-message" id="nameError">Name must be at least 2 characters</span>
                        </div>
                        <div class="form-group">
                            <label>Organizer Number<span class="required">*</span></label>
                            <input type="text" class="form-control" id="organizerNumber" placeholder="Enter Organizer number" maxlength="10">
                            <span class="error-message" id="numberError">Phone number must be 10 digits</span>
                        </div>
                        <div class="form-group">
                            <label>Organizer Email</label>
                            <input type="email" class="form-control" id="organizerEmail" placeholder="Enter Organizer email">
                            <span class="error-message" id="emailError">Please enter a valid email address</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-footer btn-close-footer" onclick="closeModal()">Close</button>
                <button type="submit" class="btn-footer btn-submit" id="submitBtn" form="organizerForm">Add Organizer</button>
            </div>
        </div>
    </div>

    <script>
        const SEASON_ID = <?php echo $id; ?>;
        let editingId = null;

        // Load organizers on page load
        $(document).ready(function() {
            console.log('Page loaded, SEASON_ID:', SEASON_ID);
            loadOrganizers();
        });

        // ============== LOAD ORGANIZERS ==============
        function loadOrganizers() {
            console.log('Loading organizers...');
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: { 
                    action: 'fetch_organizers',
                    season_id: SEASON_ID
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Load response:', response);
                    if (response.status === 'success') {
                        renderOrganizers(response.data);
                    } else {
                        console.error('Error loading organizers:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        // ============== RENDER ORGANIZERS ==============
        function renderOrganizers(organizers) {
            const grid = $('#organizersGrid');
            grid.empty();
            
            if (organizers.length === 0) {
                grid.html(`
                    <div class="empty-state">
                        <p class="empty-state-text">No Available Organizer</p>
                    </div>
                `);
            } else {
                organizers.forEach(function(organizer) {
                    const card = $(`
                        <div class="organizer-card">
                            <div class="card-actions">
                                <button class="btn-card-action btn-edit-card" data-id="${organizer.id}">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="btn-card-action btn-delete-card" data-id="${organizer.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <h3 class="organizer-name">${organizer.name}</h3>
                            <div class="organizer-details">
                                <div class="detail-row">
                                    <i class="fas fa-envelope"></i>
                                    <span>${organizer.email || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-phone"></i>
                                    <span>${organizer.number}</span>
                                </div>
                            </div>
                        </div>
                    `);
                    grid.append(card);
                });
            }
        }

        // ============== OPEN MODAL ==============
        function openModal(id = null) {
            const modal = $('#organizerModal');
            const modalTitle = $('#modalTitle');
            const submitBtn = $('#submitBtn');
            
            // Clear validation
            $('.form-control').removeClass('valid invalid');
            $('.error-message').removeClass('show');
            
            if (id) {
                editingId = id;
                
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { 
                        action: 'get_organizer',
                        organizer_id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.status === 'success') {
                            const organizer = response.data;
                            
                            $('#organizerId').val(organizer.id);
                            $('#organizerName').val(organizer.name);
                            $('#organizerEmail').val(organizer.email || '');
                            $('#organizerNumber').val(organizer.number);
                            
                            modalTitle.text('Edit organizer');
                            submitBtn.text('Update Organizer');
                            
                            modal.addClass('show');
                            $('body').css('overflow', 'hidden');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire('Error', 'Failed to load organizer data', 'error');
                        console.error('Error:', error);
                    }
                });
            } else {
                editingId = null;
                $('#organizerForm')[0].reset();
                $('#organizerId').val('');
                
                modalTitle.text('Create organizer');
                submitBtn.text('Add Organizer');
                
                modal.addClass('show');
                $('body').css('overflow', 'hidden');
            }
        }

        // ============== CLOSE MODAL ==============
        function closeModal() {
            $('#organizerModal').removeClass('show');
            $('body').css('overflow', 'auto');
            editingId = null;
            
            $('#organizerForm')[0].reset();
            $('.form-control').removeClass('valid invalid');
            $('.error-message').removeClass('show');
        }

        function closeModalOnOutside(event) {
            if (event.target.id === 'organizerModal') {
                closeModal();
            }
        }

        // ============== SUBMIT FORM ==============
        function submitForm(event) {
            event.preventDefault();
            
            const name = $('#organizerName').val().trim();
            const email = $('#organizerEmail').val().trim();
            const number = $('#organizerNumber').val().trim();
            
            console.log('Submitting:', { name, email, number, season_id: SEASON_ID });
            
            // Validate all fields
            let isValid = true;
            
            if (name.length < 2) {
                $('#organizerName').addClass('invalid').removeClass('valid');
                $('#nameError').text('Name must be at least 2 characters').addClass('show');
                isValid = false;
            }
            
            if (number.length !== 10 || number.charAt(0) < '6' || number.charAt(0) > '9') {
                $('#organizerNumber').addClass('invalid').removeClass('valid');
                $('#numberError').text('Valid 10-digit phone number is required').addClass('show');
                isValid = false;
            }
            
            if (email.length > 0) {
                let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailPattern.test(email)) {
                    $('#organizerEmail').addClass('invalid').removeClass('valid');
                    $('#emailError').addClass('show');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fix the errors before submitting'
                });
                return false;
            }
            
            // Show loading
            Swal.fire({
                title: editingId ? 'Updating...' : 'Adding...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Prepare data
            const formData = {
                action: editingId ? 'update_organizer' : 'add_organizer',
                season_id: SEASON_ID,
                name: name,
                email: email,
                number: number
            };
            
            if (editingId) {
                formData.organizer_id = editingId;
            }
            
            console.log('Sending data:', formData);
            
            // Submit via AJAX
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log('Raw response:', response);
                    console.log('Response type:', typeof response);
                    
                    // Try to parse if it's a string
                    let data = response;
                    if (typeof response === 'string') {
                        try {
                            data = JSON.parse(response);
                            console.log('Parsed response:', data);
                        } catch(e) {
                            console.error('JSON Parse Error:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid response from server',
                                footer: '<pre style="text-align:left;max-height:200px;overflow:auto;">' + response.substring(0, 500) + '</pre>'
                            });
                            return;
                        }
                    }
                    
                    if (data.status === 'success') {
                        closeModal();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                            timerProgressBar:true,
                        }).then(() => {                        
                            loadOrganizers();
                        });
                    } else {
                        closeModal();
                        Swal.fire({                            
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Unknown error occurred'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response Text:', xhr.responseText);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: 'Error: ' + error,
                        footer: '<pre style="text-align:left;max-height:200px;overflow:auto;">' + xhr.responseText.substring(0, 500) + '</pre>'
                    });
                }
            });
        }

        // ============== EDIT ORGANIZER ==============
        $(document).on('click', '.btn-edit-card', function() {
            const id = $(this).data('id');
            openModal(id);
        });

        // ============== DELETE ORGANIZER ==============
        $(document).on('click', '.btn-delete-card', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Delete Organizer?',
                text: 'Organizer will be removed from this season!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: window.location.href,
                        type: 'POST',
                        data: { 
                            action: 'delete_organizer',
                            organizer_id: id,
                            season_id: SEASON_ID
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false,
                                    timerProgressBar:true,
                                }).then(() => {
                                    loadOrganizers();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Failed to delete organizer', 'error');
                            console.error('Error:', error);
                        }
                    });
                }
            });
        });

        // Close modal on Escape key
        $(document).keydown(function(e) {
            if (e.key === 'Escape') {
                if ($('#organizerModal').hasClass('show')) {
                    closeModal();
                }
            }
        });

        // ============== VALIDATION ==============
        $(document).ready(function() {
            
            // ORGANIZER NAME VALIDATION
            $('#organizerName').on('input', function() {
                let name = $(this).val();
                let $input = $(this);
                let $error = $('#nameError');
                
                let cleanName = name.replace(/[^a-zA-Z\s]/g, '');
                if (cleanName !== name) {
                    $(this).val(cleanName);
                    name = cleanName;
                }
                
                if (name.length === 0) {
                    $input.removeClass('valid').addClass('invalid');
                    $error.text('Organizer name is required').addClass('show');
                } else if (name.length < 2) {
                    $input.removeClass('valid').addClass('invalid');
                    $error.text('Name must be at least 2 characters').addClass('show');
                } else if (name.length > 50) {
                    $input.removeClass('valid').addClass('invalid');
                    $error.text('Name must not exceed 50 characters').addClass('show');
                } else {
                    $input.removeClass('invalid').addClass('valid');
                    $error.removeClass('show');
                }
            });

            // ORGANIZER NUMBER VALIDATION
            $('#organizerNumber').on('input', function() {
                let number = $(this).val();
                let $input = $(this);
                let $error = $('#numberError');
                
                let cleanNumber = number.replace(/\D/g, '');
                if (cleanNumber !== number) {
                    $(this).val(cleanNumber);
                    number = cleanNumber;
                }
                
                if (number.length > 10) {
                    number = number.substring(0, 10);
                    $(this).val(number);
                }
                
                if (number.length === 0) {
                    $input.removeClass('valid').addClass('invalid');
                    $error.text('Phone number is required').addClass('show');
                } else if (number.length < 10) {
                    $input.removeClass('valid').addClass('invalid');
                    $error.text('Phone number must be 10 digits (currently ' + number.length + ')').addClass('show');
                } else if (number.length === 10) {
                    let firstDigit = number.charAt(0);
                    if (firstDigit >= '6' && firstDigit <= '9') {
                        $input.removeClass('invalid').addClass('valid');
                        $error.removeClass('show');
                    } else {
                        $input.removeClass('valid').addClass('invalid');
                        $error.text('Phone number must start with 6, 7, 8, or 9').addClass('show');
                    }
                }
            });

            // ORGANIZER EMAIL VALIDATION
            $('#organizerEmail').on('input', function() {
                let email = $(this).val().trim();
                let $input = $(this);
                let $error = $('#emailError');
                
                if (email.length === 0) {
                    $input.removeClass('valid invalid');
                    $error.removeClass('show');
                    return;
                }
                
                let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                
                if (!emailPattern.test(email)) {
                    $input.removeClass('valid').addClass('invalid');
                    $error.text('Please enter a valid email address').addClass('show');
                } else {
                    $input.removeClass('invalid').addClass('valid');
                    $error.removeClass('show');
                }
            });
        });
    </script>
</body>
</html>