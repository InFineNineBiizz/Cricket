<?php
    session_start();
    include "connection.php";

    $aname=$venue=$sdate=$cr_type=$edate=$bidamt=$min=$max=$tname=$sname=$mnumber=$mfname=$mlname=$lfname=$llname=$lnumber=$camt=$bidamt=$bprice="";

    if(isset($_GET['id']))
    {
        $sid=$_GET['id'];
        $auction_id = $_SESSION['add_auction'];
        
        $s="select t.name as tname,s.name as sname,a.*,am.number as mnumber,am.lname as lastname,am.fname as firstname,la.fname as fname,la.lname as lname,la.number as lnumber 
        from tournaments t,seasons s,auctions a,auc_man am,lead_auc la where t.tid=a.tour_id and s.id=a.sea_id and a.id=am.aid and a.id=la.aid and a.id='".$auction_id."'";

        $a=mysqli_query($conn,$s);        
        if($a && mysqli_num_rows($a) > 0) {
            $q=mysqli_fetch_array($a);
            $aname=$q['name'];
            $venue=$q['venue'];
            $sdate=$q['sdate'];
            $edate=$q['edate'];
            $cr_type=$q['credit_type'];
            $bidamt=$q['bidamt'];
            $min=$q['minplayer'];
            $max=$q['maxplayer'];
            $camt=$q['camt'];
            $bprice=$q['bprice'];

            $mfname=$q['firstname'];
            $mlname=$q['lastname'];
            $mnumber=$q['mnumber'];

            $lfname=$q['fname'];
            $llname=$q['lname'];
            $lnumber=$q['lnumber'];

            $tname=$q['tname'];
            $sname=$q['sname'];
        }
    }

    if(isset($_POST['confirm']))
    {
        header("location:tour-manage.php?id=$sid");
    }

    if(isset($_POST['prevBtn']))
    {
        $_SESSION['cnf']=true;
        header("location:management-details.php?id=$sid");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Auction | <?php echo $title_name;?></title>

    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <script src="../assets/script/jquery.min.js"></script>
    <style>
        .auction-form-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-card-header {
            padding: 24px 32px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-card-header h1 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
        }

        /* Stepper */
        .form-stepper {
            padding: 40px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .form-stepper::before {
            content: '';
            position: absolute;
            top: 60px;
            left: 20%;
            right: 20%;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
        }

        .stepper-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .stepper-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #a0aec0;
            font-size: 15px;
            transition: all 0.3s;
        }

        .stepper-step.active .stepper-circle {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-color: #f59e0b;
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .stepper-step.completed .stepper-circle {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .stepper-step.completed .stepper-circle::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .stepper-step.completed .stepper-circle span {
            display: none;
        }

        .stepper-label {
            font-size: 14px;
            font-weight: 500;
            color: #718096;
            text-align: center;
        }

        .stepper-step.active .stepper-label {
            color: #f59e0b;
            font-weight: 600;
        }

        /* Form Content */
        .form-card-body {
            padding: 32px;
        }

        /* Summary */
        .summary-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
        }

        .btn-edit {
            background: none;
            border: none;
            color: #f59e0b;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-edit:hover {
            color: #d97706;
            transform: scale(1.1);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .summary-grid.cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .summary-item label {
            font-size: 12px;
            font-weight: 600;
            color: #718096;
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-item p {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .user-display {
            display: flex;
            gap: 14px;
            align-items: center;
            background: white;
            padding: 14px;
            border-radius: 10px;
        }

        .user-display-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .user-display-info .name {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .user-display-info .phone {
            font-size: 13px;
            color: #718096;
        }

        .btn-submit-final {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            padding: 16px 48px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            display: inline-flex;            
            gap: 10px;
            margin: 28px auto;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            transition: all 0.3s;
        }

        .btn-submit-final:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        .text-center {
            text-align: center;
        }

        /* Card Footer */
        .form-card-footer {
            padding: 24px 32px;
            border-top: 1px solid #e2e8f0;
            background: #f7fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-previous {
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-previous:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .summary-grid,
            .summary-grid.cols-2 {
                grid-template-columns: 1fr;
            }

            .form-stepper::before {
                left: 15%;
                right: 15%;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation (from your home-style.css) -->
    <?php include "topbar.php";?>
    
    <!-- Sidebar (from your home-style.css) -->
    <?php include "sidebar.php";?>
    
    <!-- Main Content (using main-wrapper from your CSS) -->
    <div class="main-wrapper">
        <div class="auction-form-card">
            <!-- Header -->
            <div class="form-card-header">
                <h1>Auction / Confirm</h1>
            </div>

            <!-- Stepper -->
            <div class="form-stepper">
                <div class="stepper-step completed">
                    <div class="stepper-circle"><span>1</span></div>
                    <div class="stepper-label">Basic Details</div>
                </div>
                <div class="stepper-step completed">
                    <div class="stepper-circle"><span>2</span></div>
                    <div class="stepper-label">Management Details</div>
                </div>
                <div class="stepper-step active">
                    <div class="stepper-circle"><span>3</span></div>
                    <div class="stepper-label">Confirm</div>
                </div>
            </div>

            <!-- Form Body -->
            <div class="form-card-body">
                <form method="POST">
                    <div class="summary-box">
                        <div class="summary-header">
                            <h3>Basic Details</h3>
                            <button type="button" class="btn-edit" onclick="window.location.href='add-auction.php?<?php echo $sid;?>'">
                                <i class="fas fa-pen"></i>
                            </button>
                        </div>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <label>Tournament Name</label>
                                <p id="displayTournament"><?php echo $tname;?></p>
                            </div>
                            <div class="summary-item">
                                <label>Season Name</label>
                                <p id="displaySeason"><?php echo $sname;?></p>
                            </div>
                            <div class="summary-item">
                                <label>Auction Name</label>
                                <p id="displayAuction"><?php echo $aname;?></p>
                            </div>
                            <div class="summary-item">
                                <label>Auction Venue</label>
                                <p id="displayVenue"><?php echo $venue;?></p>
                            </div>
                        </div>
                        <div class="summary-item" style="margin-top: 20px;">
                            <label>Expected Date</label>
                            <p id="displayDates"><?php echo date("d M Y h:i A", strtotime($sdate)) ." - ". date("d M Y h:i A", strtotime($edate));?></p>
                        </div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-header">
                            <h3>Management Detail</h3>
                            <button type="button" class="btn-edit" onclick="window.location.href='management-details.php'">
                                <i class="fas fa-pen"></i>
                            </button>
                        </div>
                        <div class="summary-grid cols-2">
                            <div class="summary-item">
                                <label>Auction Manager</label>
                                <div class="user-display">
                                    <div class="user-display-avatar"><i class="fas fa-user"></i></div>
                                    <div class="user-display-info">
                                        <div class="name"><?php echo $mfname ." ". $mlname;?></div>
                                        <div class="phone" id="displayManagerPhone">Number: <?php echo $mnumber;?></div>                                    
                                    </div>
                                </div>
                            </div>
                            <div class="summary-item">
                                <label>Lead Auctioneer</label>
                                <div class="user-display">
                                    <div class="user-display-avatar"><i class="fas fa-user"></i></div>
                                    <div class="user-display-info">
                                        <div class="name"><?php echo $lfname ." ". $llname;?></div>
                                        <div class="phone" id="displayAuctioneerPhone">Number: <?php echo $lnumber;?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-header">
                            <h3>Information</h3>                        
                        </div>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <label>Auction Credit Type</label>
                                <p id="displayCreditType"><?php echo $cr_type;?></p>
                            </div>
                            <div class="summary-item">
                                <label>Credit Available Per Team</label>
                                <p id="displayCreditTeam"><?php echo $camt;?></p>
                            </div>
                            <div class="summary-item">
                                <label>Min Players</label>
                                <p id="displayMinPlayers"><?php echo $min;?></p>
                            </div>
                            <div class="summary-item">
                                <label>Max Players</label>
                                <p id="displayMaxPlayers"><?php echo $max;?></p>
                            </div>
                        </div>
                        <div class="summary-grid">
                            <div class="summary-item" style="margin-top: 20px;">
                                <label>Bid Increase</label>
                                <p id="displayBidIncrease"><?php echo $bidamt;?></p>
                            </div>
                            <div class="summary-item" style="margin-top: 20px;">
                                <label>Base Price</label>
                                <p id="displayBasePrice"><?php echo $bprice;?></p>
                            </div>
                        </div>
                    </div>                
                    
                    <div class="form-card-footer">
                        <button type="submit" name="prevBtn" class="btn-previous" id="prevBtn">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>                                
                        <button type="submit" name="confirm" class="btn-submit-final" id="submitBtn">
                            <i class="fas fa-check-circle"></i>Confirm
                        </button>                        
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Navigation
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPage = window.location.pathname.split('/').pop();
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('data-page');
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
        });

        // Load data from sessionStorage and display
        window.addEventListener('DOMContentLoaded', function() {
            // Load auction form data
            const auctionData = sessionStorage.getItem('auctionFormData');
            if (auctionData) {
                const data = JSON.parse(auctionData);
                
                document.getElementById('displayTournament').textContent = data.tournamentName || '-';
                document.getElementById('displaySeason').textContent = data.seasonName || '-';
                document.getElementById('displayAuction').textContent = data.auctionName || '-';
                document.getElementById('displayVenue').textContent = data.auctionVenue || '-';
                
                // Format dates
                if (data.startDate && data.endDate) {
                    const startDate = new Date(data.startDate).toLocaleDateString('en-GB');
                    const endDate = new Date(data.endDate).toLocaleDateString('en-GB');
                    document.getElementById('displayDates').innerHTML = 
                        `Start Date: ${startDate}<br>Expected End Date: ${endDate}`;
                }
                
                document.getElementById('displayCreditType').textContent = data.creditType || '-';
                document.getElementById('displayBidIncrease').textContent = 
                    data.bidIncrease ? `${data.bidIncrease} ${data.creditType}` : '-';
                document.getElementById('displayMinPlayers').textContent = data.minPlayers || '-';
                document.getElementById('displayMaxPlayers').textContent = data.maxPlayers || '-';
            }

            // Load management data
            const managerPhone = sessionStorage.getItem('managerPhone');
            const auctioneerPhone = sessionStorage.getItem('auctioneerPhone');
            
            if (managerPhone) {
                document.getElementById('displayManagerPhone').textContent = `Number: ${managerPhone}`;
            }
            
            if (auctioneerPhone) {
                document.getElementById('displayAuctioneerPhone').textContent = `Number: ${auctioneerPhone}`;
            }
        });

        // Submit button
        document.getElementById('submitBtn').addEventListener('click', function() {
            // Get all data from sessionStorage
            const auctionData = sessionStorage.getItem('auctionFormData');
            const managerPhone = sessionStorage.getItem('managerPhone');
            const auctioneerPhone = sessionStorage.getItem('auctioneerPhone');

            if (!auctionData || !managerPhone || !auctioneerPhone) {
                alert('Missing form data. Please complete all steps.');
                return;
            }

            // Prepare final data object
            const finalData = {
                ...JSON.parse(auctionData),
                managerPhone,
                auctioneerPhone
            };

            // Here you would send the data to your server
            console.log('Submitting auction data:', finalData);

            // For now, just show success message
            alert('Auction created successfully!');
            
            // Clear sessionStorage
            sessionStorage.removeItem('auctionFormData');
            sessionStorage.removeItem('managerPhone');
            sessionStorage.removeItem('auctioneerPhone');
        });
    </script>
</body>
</html>