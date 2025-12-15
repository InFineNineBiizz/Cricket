<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction & Seasons | CrickFolio Portal</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="../assets/css/auction-style.css">    
</head>
<body>
    <!-- Top Navigation -->
    <?php
        include 'topbar.php';
    ?>

    <!-- Sidebar -->
    <?php
        include 'sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="main-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Seasons</h1>
            <button class="add-season-btn" onclick="openModal()">
                <i class="fas fa-plus-circle"></i> ADD SEASON
            </button>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <button class="tab-btn active" onclick="switchTab('ongoing')">
                <i class="fas fa-play-circle"></i> Ongoing
                <span class="tab-count" id="ongoingCount">0</span>
            </button>
            <button class="tab-btn" onclick="switchTab('upcoming')">
                <i class="fas fa-clock"></i> Upcoming
                <span class="tab-count" id="upcomingCount">0</span>
            </button>
            <button class="tab-btn" onclick="switchTab('completed')">
                <i class="fas fa-check-circle"></i> Completed
                <span class="tab-count" id="completedCount">0</span>
            </button>
        </div>

        <!-- Ongoing Seasons -->
        <div class="tab-content active" id="ongoingTab">
            <div class="seasons-grid" id="ongoingGrid">
                <!-- Ongoing seasons will be rendered here -->
            </div>
        </div>

        <!-- Upcoming Seasons -->
        <div class="tab-content" id="upcomingTab">
            <div class="seasons-grid" id="upcomingGrid">
                <!-- Upcoming seasons will be rendered here -->
            </div>
        </div>

        <!-- Completed Seasons -->
        <div class="tab-content" id="completedTab">
            <div class="seasons-grid" id="completedGrid">
                <!-- Completed seasons will be rendered here -->
            </div>
        </div>
    </main>

    <!-- Add Season Modal -->
    <div id="addSeasonModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-gavel"></i> Add New Season</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="seasonForm" onsubmit="addSeason(event)">
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-heading"></i> Season Name *</label>
                            <input type="text" id="seasonName" placeholder="Enter season name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-trophy"></i> Tournament *</label>
                            <select id="tournamentSelect" required>
                                <option value="">Select Tournament</option>
                                <option value="Dream Classes Premier League">Dream Classes Premier League</option>
                                <option value="Champions Trophy 2025">Champions Trophy 2025</option>
                                <option value="Super League Season 5">Super League Season 5</option>
                                <option value="City Cricket Championship">City Cricket Championship</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-info-circle"></i> Status *</label>
                            <select id="seasonStatus" required>
                                <option value="">Select Status</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Start Date *</label>
                            <input type="date" id="startDate" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> End Date *</label>
                            <input type="date" id="endDate" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Total Teams *</label>
                            <input type="number" id="totalTeams" placeholder="e.g. 8" min="2" max="100" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-baseball-ball"></i> Ball Type *</label>
                            <select id="ballType" required>
                                <option value="">Select</option>
                                <option value="Tennis Ball">Tennis Ball</option>
                                <option value="Leather Ball">Leather Ball</option>
                                <option value="Rubber Ball">Rubber Ball</option>
                                <option value="Season Ball">Season Ball</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> Ground Type *</label>
                            <select id="groundType" required>
                                <option value="">Select</option>
                                <option value="Turf">Turf</option>
                                <option value="Grass">Grass</option>
                                <option value="Concrete">Concrete</option>
                                <option value="Matting">Matting</option>
                                <option value="Indoor">Indoor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-chess"></i> Match Type *</label>
                            <select id="matchType" required>
                                <option value="">Select</option>
                                <option value="T20">T20</option>
                                <option value="T10">T10</option>
                                <option value="One Day">One Day</option>
                                <option value="Box Cricket">Box Cricket</option>
                                <option value="Test Match">Test Match</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-image"></i> Upload Logo</label>
                            <div class="upload-area" id="uploadArea" onclick="document.getElementById('logoInput').click()">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <p class="upload-text">Click to upload or drag and drop</p>
                                <p class="upload-info">PNG, JPG or JPEG (Max 2MB)</p>
                            </div>
                            <input type="file" id="logoInput" accept="image/png,image/jpeg,image/jpg" onchange="handleLogoUpload(event)">
                            <div class="logo-preview" id="logoPreview">
                                <img id="previewImage" src="" alt="Logo Preview">
                                <br>
                                <button type="button" class="remove-logo" onclick="removeLogo()">
                                    <i class="fas fa-times"></i> Remove Logo
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-plus-circle"></i> Add Season
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Message Toast -->
    <div id="successToast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Season added successfully!</span>
    </div>
    
    <script>
        // Store uploaded logo
        let uploadedLogo = null;

        // Sample seasons data
        let seasons = [
            {
                name: "IPL 2024 Season",
                tournament: "Champions Trophy 2025",
                status: "ongoing",
                startDate: "2024-03-15",
                endDate: "2024-05-30",
                totalTeams: 10,
                ballType: "Leather Ball",
                groundType: "Turf",
                matchType: "T20",
                description: "The biggest cricket auction of the year"
            },
            {
                name: "Summer League 2024",
                tournament: "Super League Season 5",
                status: "upcoming",
                startDate: "2024-06-01",
                endDate: "2024-08-15",
                totalTeams: 8,
                ballType: "Tennis Ball",
                groundType: "Grass",
                matchType: "T10",
                description: "Exciting summer cricket tournament"
            },
            {
                name: "Winter Championship 2023",
                tournament: "Dream Classes Premier League",
                status: "completed",
                startDate: "2023-11-01",
                endDate: "2024-01-15",
                totalTeams: 12,
                ballType: "Leather Ball",
                groundType: "Turf",
                matchType: "One Day",
                description: "Successfully completed winter season"
            },
            {
                name: "Pro League Season 3",
                tournament: "City Cricket Championship",
                status: "completed",
                startDate: "2023-09-01",
                endDate: "2023-11-30",
                totalTeams: 6,
                ballType: "Tennis Ball",
                groundType: "Concrete",
                matchType: "Box Cricket",
                description: "Thrilling cricket season completed"
            }
        ];

        // Current active tab
        let activeTab = 'ongoing';

        // Render all seasons on page load
        window.onload = function() {
            renderAllSeasons();
            updateTabCounts();
            setupDragAndDrop();
        };

        // Setup drag and drop
        function setupDragAndDrop() {
            const uploadArea = document.getElementById('uploadArea');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.remove('dragover');
                }, false);
            });

            uploadArea.addEventListener('drop', handleDrop, false);
        }

        // Handle drag and drop
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                document.getElementById('logoInput').files = files;
                handleLogoUpload({ target: { files: files } });
            }
        }

        // Handle logo upload
        function handleLogoUpload(event) {
            const file = event.target.files[0];
            
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    showToast('File size must be less than 2MB', 'error');
                    return;
                }
                
                // Validate file type
                if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
                    showToast('Only PNG, JPG and JPEG files are allowed', 'error');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    uploadedLogo = e.target.result;
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('logoPreview').style.display = 'block';
                    document.getElementById('uploadArea').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }

        // Remove logo
        function removeLogo() {
            uploadedLogo = null;
            document.getElementById('logoInput').value = '';
            document.getElementById('logoPreview').style.display = 'none';
            document.getElementById('uploadArea').style.display = 'block';
        }

        // Render all seasons
        function renderAllSeasons() {
            renderSeasonsByStatus('ongoing');
            renderSeasonsByStatus('upcoming');
            renderSeasonsByStatus('completed');
        }

        // Render seasons by status
        function renderSeasonsByStatus(status) {
            const grid = document.getElementById(`${status}Grid`);
            const filteredSeasons = seasons.filter(s => s.status === status);
            
            if (filteredSeasons.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No ${status} seasons</h3>
                        <p>There are no ${status} seasons at the moment.</p>
                    </div>
                `;
            } else {
                grid.innerHTML = filteredSeasons.map((season, index) => 
                    createSeasonCard(season, seasons.indexOf(season))
                ).join('');
            }
        }

        // Create season card HTML
        function createSeasonCard(season, index) {
            const statusColors = {
                ongoing: 'status-ongoing',
                upcoming: 'status-upcoming',
                completed: 'status-completed'
            };

            const statusIcons = {
                ongoing: 'fa-play-circle',
                upcoming: 'fa-clock',
                completed: 'fa-check-circle'
            };

            return `
                <div class="season-card">
                    <div class="season-header">
                        <div class="season-info">
                            <h3 class="season-name" title="${season.name}">${season.name}</h3>
                            <span class="season-badge ${statusColors[season.status]}">
                                <i class="fas ${statusIcons[season.status]}"></i> 
                                ${season.status.charAt(0).toUpperCase() + season.status.slice(1)}
                            </span>
                        </div>
                    </div>

                    <div class="season-details">
                        <div class="detail-item">
                            <i class="fas fa-trophy"></i>
                            <div class="detail-content">
                                <span class="detail-label">Tournament</span>
                                <span class="detail-value">${season.tournament}</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <div class="detail-content">
                                <span class="detail-label">Teams</span>
                                <span class="detail-value">${season.totalTeams}</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div class="detail-content">
                                <span class="detail-label">Duration</span>
                                <span class="detail-value">${formatDate(season.startDate)} - ${formatDate(season.endDate)}</span>
                            </div>
                        </div>
                        ${season.ballType ? `
                        <div class="detail-item">
                            <i class="fas fa-baseball-ball"></i>
                            <div class="detail-content">
                                <span class="detail-label">Ball Type</span>
                                <span class="detail-value">${season.ballType}</span>
                            </div>
                        </div>
                        ` : ''}
                        ${season.matchType ? `
                        <div class="detail-item">
                            <i class="fas fa-chess"></i>
                            <div class="detail-content">
                                <span class="detail-label">Match Type</span>
                                <span class="detail-value">${season.matchType}</span>
                            </div>
                        </div>
                        ` : ''}
                    </div>

                    ${season.description ? `
                        <div class="season-description">
                            <i class="fas fa-info-circle"></i>
                            <p>${season.description}</p>
                        </div>
                    ` : ''}

                    <div class="season-actions">
                        <button class="action-btn btn-view" onclick="viewSeason(${index})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="action-btn btn-edit" onclick="editSeason(${index})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn btn-delete" onclick="deleteSeason(${index})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            return date.toLocaleDateString('en-GB', options);
        }

        // Switch tabs
        function switchTab(tabName) {
            // Update active tab
            activeTab = tabName;

            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.tab-btn').classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${tabName}Tab`).classList.add('active');
        }

        // Update tab counts
        function updateTabCounts() {
            document.getElementById('ongoingCount').textContent = 
                seasons.filter(s => s.status === 'ongoing').length;
            document.getElementById('upcomingCount').textContent = 
                seasons.filter(s => s.status === 'upcoming').length;
            document.getElementById('completedCount').textContent = 
                seasons.filter(s => s.status === 'completed').length;
        }

        // Open modal
        function openModal() {
            document.getElementById('addSeasonModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close modal
        function closeModal() {
            document.getElementById('addSeasonModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('seasonForm').reset();
            removeLogo();
        }

        // Add season
        function addSeason(event) {
            event.preventDefault();
            
            const newSeason = {
                name: document.getElementById('seasonName').value.trim(),
                tournament: document.getElementById('tournamentSelect').value,
                status: document.getElementById('seasonStatus').value,
                startDate: document.getElementById('startDate').value,
                endDate: document.getElementById('endDate').value,
                totalTeams: parseInt(document.getElementById('totalTeams').value),
                ballType: document.getElementById('ballType').value,
                groundType: document.getElementById('groundType').value,
                matchType: document.getElementById('matchType').value,
                logo: uploadedLogo,
                description: ''
            };

            seasons.push(newSeason);
            renderAllSeasons();
            updateTabCounts();
            closeModal();
            showToast(`"${newSeason.name}" season added successfully!`, 'success');
        }

        // Show toast
        function showToast(message, type = 'success') {
            const toast = document.getElementById('successToast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            
            if (type === 'error') {
                toast.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            } else {
                toast.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            }
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // View season
        function viewSeason(index) {
            const season = seasons[index];
            alert(`Season: ${season.name}\nTournament: ${season.tournament}\nStatus: ${season.status}\nTeams: ${season.totalTeams}\nBall Type: ${season.ballType}\nGround: ${season.groundType}\nMatch Type: ${season.matchType}`);
        }

        // Edit season
        function editSeason(index) {
            showToast('Edit functionality - Coming soon!', 'success');
        }

        // Delete season
        function deleteSeason(index) {
            const season = seasons[index];
            
            if (confirm(`Are you sure you want to delete "${season.name}"?\n\nThis action cannot be undone.`)) {
                seasons.splice(index, 1);
                renderAllSeasons();
                updateTabCounts();
                showToast(`"${season.name}" deleted successfully!`, 'success');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addSeasonModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>