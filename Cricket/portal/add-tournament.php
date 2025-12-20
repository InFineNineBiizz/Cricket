<?php     
    session_start();
    include "connection.php";
    $tname=$cat=$logo=$img="";

    if(isset($_GET['tid']))
    {
        $id=$_GET['tid'];
        $str="select * from tournaments where tid=".$id."";
        $res=mysqli_query($conn,$str);
        $row=mysqli_fetch_array($res);
        $tname=$row['name'];
        $cat=$row['category'];
        $logo=$row['logo'];
    }

    if(isset($_POST['btn']))
    {
        if(empty($_GET['tid']))
        {        
            move_uploaded_file($_FILES['tlogo']['tmp_name'],"../assets/images/".$_FILES['tlogo']['name']);
            $img=$_FILES['tlogo']['name'];                
            
            $str="insert into tournaments(name,category,logo) values('".$_POST['tname']."','".$_POST['tcategory']."','".$img."')";
            $res=mysqli_query($conn,$str);            
            $log=true;
        }
        else
        {
            move_uploaded_file($_FILES['tlogo']['tmp_name'],"../assets/images/".$_FILES['tlogo']['name']);
            $img=$_FILES['tlogo']['name'];

            $str="update tournaments set name='".$_POST['tname']."',category='".$_POST['tcategory']."',logo='".$img."' where tid=".$id."";
            $res=mysqli_query($conn,$str);
            header("location:tournament.php");
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tournament | CrickFolio Portal</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">    
    <script src="../assets/script/sweetalert2.js"></script>

    <style>

          /* ===== jQuery Validation ===== */
        .error-text{
            color:#ef4444;
            font-size:12px;
            display:none;
            margin-top:5px;
        }

        .error-border{
            border:1.5px solid #ef4444 !important;
        }

        .valid-border{
            border:1.5px solid #10b981 !important;
        }

        /* ================================================
        ADD TOURNAMENT PAGE STYLES
        ================================================ */

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            color: white;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title i {
            font-size: 2rem;
        }

        .page-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0.5rem 0 0 0;
        }

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Form Section */
        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-header i {
            font-size: 1.5rem;
            color: #f59e0b;
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4b5563;
            margin: 0;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        /* Form Labels */
        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group label i {
            color: #f59e0b;
            font-size: 1rem;
        }

        /* Form Inputs */
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23f59e0b' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f7fafc;
            position: relative;
            overflow: hidden;
        }

        .upload-area:hover {
            border-color: #f59e0b;
            background: #edf2f7;
        }

        .upload-area.dragover {
            border-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            transform: scale(1.02);
        }

        .upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .upload-icon {
            font-size: 3rem;
            color: #f59e0b;
            margin-bottom: 0.5rem;
        }

        .upload-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4b5563;
            margin: 0;
        }

        .upload-hint {
            font-size: 0.9rem;
            color: #718096;
            margin: 0;
        }

        /* Image Preview */
        .image-preview-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
        }

        .image-preview-wrapper img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            object-fit: contain;
        }

        .remove-image-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-image-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding-top: 2rem;
            border-top: 2px solid #f0f0f0;
            margin-top: 2rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: inherit;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-cancel:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(245, 158, 11, 0.4);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            font-weight: 600;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .toast i {
            font-size: 1.25rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .page-title i {
                font-size: 1.5rem;
            }

            .form-container {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .toast {
                bottom: 1rem;
                right: 1rem;
                left: 1rem;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .form-container {
                padding: 1rem;
            }

            .upload-area {
                padding: 1.5rem;
            }

            .upload-icon {
                font-size: 2rem;
            }

            .upload-text {
                font-size: 1rem;
            }

            .section-header h2 {
                font-size: 1.25rem;
            }
        }

        .was-validated input[type="file"]:invalid ~ .error-text {
            display: block;
        }

        .was-validated input[type="file"]:invalid {
            outline: 2px dashed #ef4444;
        }


        /* Placeholder styles */
        ::placeholder {
            color: #a0aec0;
            opacity: 1;
        }

        /* Loading state for submit button */
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

    </style>
</head>
<body>
    <!-- Top Navigation -->
    <?php include 'topbar.php'; ?>
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-wrapper">
        <!-- Page Header -->
        
        <!-- Add Tournament Form -->
        <div class="form-container">
            <form id="addTournamentForm"  method="POST" enctype="multipart/form-data" novalidate>
                <!-- Tournament Basic Information -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>ADD NEW TOURNAMENT </h2>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-trophy"></i> Tournament Name *
                            </label>
                            <input type="text" id="name" name="tname" value="<?php echo $tname;?>" placeholder="Enter tournament name (e.g., Dream Classes Premier League)">
                            <small  id="name_error" class="error-text"></small>
                        </div>

                        <div class="form-group">    
                            <label for="category">
                                <i class="fas fa-tag"></i> Tournament Category *
                            </label>
                            <select id="category" name="tcategory">
                                <option value="" selected disabled>Select Category</option>
                                <option value="College" <?php if($cat == "College") echo "selected";?>>College</option>
                                <option value="District" <?php if($cat == "District") echo "selected";?>>District</option>
                                <option value="National" <?php if($cat == "National") echo "selected";?>>National</option>
                                <option value="Open" <?php if($cat == "Open") echo "selected";?>>Open</option>
                                <option value="Other" <?php if($cat == "Other") echo "selected";?>>Other</option>
                            </select>
                            <small id="category_error" class="error-text"></small>
                        </div>

                        <!-- Upload Logo Section -->
                        <div class="form-group full-width">
                            <label for="logo">
                                <i class="fas fa-image"></i> Upload Logo *
                            </label>
                            
                            <!-- Drag and Drop Upload Area -->
                            <div class="upload-area" id="uploadArea">
                                <input type="file" id="logo" name="tlogo" accept="image/png, image/jpg, image/jpeg" hidden>
                                
                                <div class="upload-content" id="uploadContent">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <p class="upload-text">Click to upload or drag and drop</p>
                                    <p class="upload-hint">PNG, JPG or JPEG (Max 2MB)</p>
                                </div>

                                <!-- Image Preview -->
                                <div class="image-preview-wrapper" id="imagePreviewWrapper" style="display: none;">
                                    <img id="imagePreview" src="" alt="Tournament Logo Preview">
                                    <button type="button" class="remove-image-btn" id="removeImageBtn">
                                        <i class="fas fa-times"></i> Remove Image
                                    </button>
                                </div>
                            </div>
                            <small id="logo_error" class="error-text"></small>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="tournament.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="btn" class="btn btn-submit">
                        <i class="fas fa-plus-circle"></i> <?php if(isset($id)){ echo "Update Tournament";}else{echo "Create Tournament";}?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Success Toast -->
    <div id="successToast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Tournament added successfully!</span>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function () {

            const name     = $("#name");
            const category = $("#category");
            const logo     = $("#logo");

            const nameErr     = $("#name_error");
            const categoryErr = $("#category_error");
            const logoErr     = $("#logo_error");

            const oldLogo = "<?php echo $logo; ?>";

            let isNameValid = false;
            let isCategoryValid = false;
            let isLogoValid = false;

            // Check if editing existing tournament
            <?php if(!empty($name)) { ?>
                isNameValid = true;
                name.addClass("valid-border");
            <?php } ?>

            <?php if(!empty($cat)) { ?>
                isCategoryValid = true;
                category.addClass("valid-border");
            <?php } ?>

            <?php if(!empty($logo)) { ?>
                isLogoValid = true;
                // Show existing logo preview
                const existingLogoPath = "../images/<?php echo $logo; ?>";
                $("#imagePreview").attr("src", existingLogoPath);
                $("#uploadContent").hide();
                $("#imagePreviewWrapper").show();
            <?php } ?>

            /* NAME VALIDATION */
            name.on("input blur", function () {
                const value = $(this).val().trim();
                
                if (value === "") {
                    nameErr.text("Please enter tournament name").show();
                    $(this).removeClass("valid-border").addClass("error-border");
                    isNameValid = false;
                } else if (value.length < 3) {
                    nameErr.text("Tournament name must be at least 3 characters").show();
                    $(this).removeClass("valid-border").addClass("error-border");
                    isNameValid = false;
                } else {
                    nameErr.hide();
                    $(this).removeClass("error-border").addClass("valid-border");
                    isNameValid = true;
                }
            });

            /* CATEGORY VALIDATION */
            category.on("change blur", function () {
                const value = $(this).val();
                
                if (!value || value === "") {
                    categoryErr.text("Please select tournament category").show();
                    $(this).removeClass("valid-border").addClass("error-border");
                    isCategoryValid = false;
                } else {
                    categoryErr.hide();
                    $(this).removeClass("error-border").addClass("valid-border");
                    isCategoryValid = true;
                }
            });

            /* LOGO VALIDATION */
            logo.on("change", function () {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const validTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                    
                    if (!validTypes.includes(file.type)) {
                        logoErr.text("Only PNG, JPG, JPEG files are allowed").show();
                        $(this).val("");
                        isLogoValid = false;
                        return;
                    }
                    
                    if (file.size > 2 * 1024 * 1024) {
                        logoErr.text("File size must be less than 2MB").show();
                        $(this).val("");
                        isLogoValid = false;
                        return;
                    }
                    
                    logoErr.hide();
                    isLogoValid = true;
                } else if (oldLogo === "") {
                    logoErr.text("Please choose tournament logo").show();
                    isLogoValid = false;
                } else {
                    logoErr.hide();
                    isLogoValid = true;
                }
            });

            /* FORM SUBMIT VALIDATION */
            $("#addTournamentForm").on("submit", function (e) {
                
                // Trigger validation on all fields
                name.trigger("blur");
                category.trigger("blur");
                
                // Check logo validation
                if (logo[0].files.length === 0 && oldLogo === "") {
                    logoErr.text("Please choose tournament logo").show();
                    isLogoValid = false;
                }

                // Prevent form submission if any field is invalid
                if (!isNameValid || !isCategoryValid || !isLogoValid) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    $(".error-border").first().focus();
                    
                    return false;
                }
            });

        });

        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('logo');
        const uploadContent = document.getElementById('uploadContent');
        const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');
        const imagePreview = document.getElementById('imagePreview');
        const removeImageBtn = document.getElementById('removeImageBtn');

        // Click upload
        uploadArea.addEventListener('click', () => fileInput.click());

        // File select
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert("Max 2MB allowed");
                fileInput.value = "";
                return;
            }

            const validTypes = ['image/png', 'image/jpg', 'image/jpeg'];
            if (!validTypes.includes(file.type)) {
                alert("Only PNG, JPG, JPEG allowed");
                fileInput.value = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                imagePreview.src = reader.result;
                uploadContent.style.display = "none";
                imagePreviewWrapper.style.display = "flex";
            };
            reader.readAsDataURL(file);
        });

        // Remove image
        removeImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = "";
            uploadContent.style.display = "block";
            imagePreviewWrapper.style.display = "none";
        });
    </script>
    <script>
        <?php if($log == true){?>
            Swal.fire({
                icon:'success',
                title: 'Success!',
                text: 'Tournament Created Successfully!',                
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                willClose: () => {                    
                    window.location.href = "tournament.php";
                }
            });
        <?php }?>
    </script>
</body>
</html>