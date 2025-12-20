<?php
    session_start();
    include "connection.php";

    if (isset($_GET['delete'])) 
    {
        $id = (int)$_GET['delete'];

        $q = mysqli_query($conn, "SELECT logo FROM tournaments WHERE tid=$id");
        $r = mysqli_fetch_assoc($q);

        if (!empty($r['logo'])) {
            $path = "../assets/images/" . $r['logo'];
            if (file_exists($path)) unlink($path);
        }

        mysqli_query($conn, "DELETE FROM tournaments WHERE tid=$id");
        header("Location: tournament.php");
        exit;
    }
    $res = mysqli_query($conn, "SELECT * FROM tournaments ORDER BY tid ");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournaments | CrickFolio Portal</title>

    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="../assets/css/tournament-style.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">    
    <script src="../assets/script/sweetalert2.js"></script>

    <style>
        /* SweetAlert Custom Styling */
        .swal-custom-popup {
            border-radius: 15px !important;
            font-family: inherit !important;
        }

        .swal-confirm-btn {
            padding: 10px 24px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .swal-cancel-btn {
            padding: 10px 24px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
        }

        .swal2-title {
            font-size: 1.75rem !important;
            font-weight: 700 !important;
        }

        .swal2-html-container {
            font-size: 1rem !important;
        }

        .tournament-grid{
            display:grid;
            grid-template-columns: repeat(auto-fill, minmax(340px,1fr));
            gap:20px;
        }

        .tournament-card{
            background:#fff;
            border-radius:12px;
            padding:18px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 4px 10px rgba(0,0,0,.05);
        }

        .tournament-left{
            display:flex;
            gap:15px;
            align-items:center;
        }

        .tournament-icon{
            width:60px;
            height:60px;
            background:#1f2937;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .tournament-icon i{
            color:#f59e0b;
            font-size:30px;
        }

        .tournament-icon img{
            width:100%;
            height:100%;
            border-radius:12px;
            object-fit:cover;
        }

        .tournament-text h3{
            margin:0;
            font-size:16px;
            font-weight:700;
            color:#111827;
        }

        .tournament-text span{
            font-size:13px;
            color:#6b7280;
            display:flex;
            gap:6px;
            align-items:center;
        }

        .tournament-actions{
            display:flex;
            gap:8px;
        }

        .tournament-actions .btn{
            width:34px;
            height:34px;
            border-radius:6px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            text-decoration:none;
        }

        .btn.view{ background:#2563eb; }
        .btn.edit{ background:#16a34a; }
        .btn.delete{ background:#dc2626; }
    </style>
</head>

<body>

    <?php include 'topbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-wrapper">

        <div class="page-header">
            <h1 class="page-title">Tournaments</h1>
            <button class="add-tournament-btn" onclick="location.href='add-tournament.php'">
                <i class="fas fa-plus-circle"></i> ADD TOURNAMENT
            </button>
        </div>

        <div class="tournament-grid">
        <?php while ($row = mysqli_fetch_assoc($res)) { ?>
            
            <div class="tournament-card">

                <div class="tournament-left">
                    <div class="tournament-icon">
                        <?php if (!empty($row['logo'])) { ?>
                            <img src="../assets/images/<?php echo $row['logo']; ?>" alt="Tournament Logo">
                        <?php } else { ?>
                            <i class="fas fa-trophy"></i>
                        <?php } ?>
                    </div>

                    <div class="tournament-text">
                        <h3><?php echo $row['name']; ?></h3>
                        <span>
                            <i class="fas fa-award"></i>
                            <?php echo $row['category']; ?>
                        </span>
                    </div>
                </div>

                <div class="tournament-actions">
                    <a href="view-tournament.php?tid=<?php echo $row['tid']; ?>" class="btn view">
                        <i class="fas fa-eye"></i>
                    </a>

                    <a href="add-tournament.php?tid=<?php echo $row['tid']; ?>" class="btn edit">
                        <i class="fas fa-pen"></i>
                    </a>

                    <a href="javascript:void(0);" class="btn delete" onclick="deleteTournament(<?php echo $row['tid']; ?>, '<?php echo addslashes($row['name']); ?>')"><i class="fas fa-trash"></i></a>
                </div>

            </div>

        <?php } ?>
        </div>

    </main>

    <div id="successToast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage"></span>
    </div>

    <script>
        
        function showToast(msg){
            const toast = document.getElementById("successToast");
            document.getElementById("toastMessage").innerText = msg;
            toast.classList.add("show");
            setTimeout(()=>toast.classList.remove("show"),3000);
        }

        function deleteTournament(tid, name) {
            Swal.fire({
                title: 'Are you sure?',
                html: `Do you want to delete <b>${name}</b> Tournament?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete it!',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                focusCancel: true,
                customClass: {
                    popup: 'swal-custom-popup',
                    confirmButton: 'swal-confirm-btn',
                    cancelButton: 'swal-cancel-btn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon:'success',
                        title: 'Delete Success...',
                        text: 'Tournament Deleted Successfully!',                
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        willClose: () => {                    
                            window.location.href = "?delete=" + tid;
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>