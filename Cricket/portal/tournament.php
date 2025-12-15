<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournaments | CrickFolio Portal</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <link rel="stylesheet" href="../assets/css/tournament-style.css">
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
            <h1 class="page-title">Tournaments</h1>
            <button class="add-tournament-btn" onclick="openAddModal()">
                <i class="fas fa-plus-circle"></i> ADD TOURNAMENT
            </button>
        </div>

        <!-- Tournament Cards Grid -->
        <div class="tournament-grid" id="tournamentGrid">
            <!-- Cards will be added here dynamically -->
        </div>
    </main>

    <!-- Add Tournament Modal -->
    <div id="addTournamentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-trophy"></i> Add New Tournament</h2>
                <button class="close-btn" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addTournamentForm" onsubmit="addTournament(event)">
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-trophy"></i> Tournament Name *</label>
                            <input type="text" id="addTournamentName" placeholder="Enter tournament name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Tournament Type *</label>
                            <select id="addTournamentType" required>
                                <option value="">Select Type</option>
                                <option value="T20">T20</option>
                                <option value="ODI">ODI</option>
                                <option value="Test">Test</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-icons"></i> Icon *</label>
                            <select id="addTournamentIcon" required>
                                <option value="fa-trophy">🏆 Trophy</option>
                                <option value="fa-star">⭐ Star</option>
                                <option value="fa-medal">🏅 Medal</option>
                                <option value="fa-crown">👑 Crown</option>
                                <option value="fa-fire">🔥 Fire</option>
                                <option value="fa-bolt">⚡ Bolt</option>
                                <option value="fa-gem">💎 Gem</option>
                                <option value="fa-shield-alt">🛡️ Shield</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Number of Teams *</label>
                            <input type="number" id="addNumTeams" placeholder="e.g. 8" min="2" max="100" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user-friends"></i> Number of Players *</label>
                            <input type="number" id="addNumPlayers" placeholder="e.g. 120" min="1" max="10000" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Number of Matches *</label>
                            <input type="number" id="addNumMatches" placeholder="e.g. 24" min="1" max="1000" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Prize Pool *</label>
                            <input type="text" id="addPrizePool" placeholder="e.g. 50K, 1M, 100K" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeAddModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-plus-circle"></i> Add Tournament
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Tournament Modal -->
    <div id="editTournamentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Tournament</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editTournamentForm" onsubmit="updateTournament(event)">
                    <input type="hidden" id="editTournamentIndex">
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-trophy"></i> Tournament Name *</label>
                            <input type="text" id="editTournamentName" placeholder="Enter tournament name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Tournament Type *</label>
                            <select id="editTournamentType" required>
                                <option value="">Select Type</option>
                                <option value="T20">T20</option>
                                <option value="ODI">ODI</option>
                                <option value="Test">Test</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-icons"></i> Icon *</label>
                            <select id="editTournamentIcon" required>
                                <option value="fa-trophy">🏆 Trophy</option>
                                <option value="fa-star">⭐ Star</option>
                                <option value="fa-medal">🏅 Medal</option>
                                <option value="fa-crown">👑 Crown</option>
                                <option value="fa-fire">🔥 Fire</option>
                                <option value="fa-bolt">⚡ Bolt</option>
                                <option value="fa-gem">💎 Gem</option>
                                <option value="fa-shield-alt">🛡️ Shield</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Number of Teams *</label>
                            <input type="number" id="editNumTeams" placeholder="e.g. 8" min="2" max="100" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user-friends"></i> Number of Players *</label>
                            <input type="number" id="editNumPlayers" placeholder="e.g. 120" min="1" max="10000" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Number of Matches *</label>
                            <input type="number" id="editNumMatches" placeholder="e.g. 24" min="1" max="1000" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Prize Pool *</label>
                            <input type="text" id="editPrizePool" placeholder="e.g. 50K, 1M, 100K" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeEditModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Update Tournament
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Tournament Modal -->
    <div id="viewTournamentModal" class="modal">
        <div class="modal-content view-modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-eye"></i> Tournament Details</h2>
                <button class="close-btn" onclick="closeViewModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-tournament-details" id="viewTournamentDetails">
                    <!-- Details will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message Toast -->
    <div id="successToast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Tournament added successfully!</span>
    </div>

    <script>
        // ================================================
        // TOURNAMENT MANAGEMENT JAVASCRIPT
        // ================================================

        // Sample tournaments data
        let tournaments = [
            {
                name: "Dream Classes Premier League",
                type: "Other",
                icon: "fa-trophy",
                teams: 8,
                players: 120,
                matches: 24,
                prizePool: "50K"
            },
            {
                name: "Champions Trophy 2025",
                type: "T20",
                icon: "fa-star",
                teams: 12,
                players: 180,
                matches: 36,
                prizePool: "75K"
            },
            {
                name: "Super League Season 5",
                type: "ODI",
                icon: "fa-medal",
                teams: 10,
                players: 150,
                matches: 30,
                prizePool: "60K"
            },
            {
                name: "City Cricket Championship",
                type: "Test",
                icon: "fa-crown",
                teams: 6,
                players: 90,
                matches: 18,
                prizePool: "40K"
            }
        ];

        // Current tournament index being edited
        let currentEditIndex = -1;

        // ===== INITIALIZATION =====
        window.onload = function() {
            renderTournaments();
        };

        // ===== RENDER FUNCTIONS =====

        // Render all tournaments
        function renderTournaments() {
            const grid = document.getElementById('tournamentGrid');
            grid.innerHTML = '';
            
            tournaments.forEach((tournament, index) => {
                const card = createTournamentCard(tournament, index);
                grid.innerHTML += card;
            });
        }

        // Create tournament card HTML
        function createTournamentCard(tournament, index) {
            return `
                <div class="tournament-card">
                    <div class="tournament-header">
                        <div class="tournament-logo">
                            <i class="fas ${tournament.icon}"></i>
                        </div>
                        <div class="tournament-info">
                            <h3 class="tournament-name" title="${tournament.name}">${tournament.name}</h3>
                            <span class="tournament-badge">
                                <i class="fas fa-award"></i> ${tournament.type}
                            </span>
                        </div>
                    </div>

                    <div class="tournament-stats">
                        <div class="stat-box">
                            <i class="fas fa-users"></i>
                            <span class="stat-value">${tournament.teams}</span>
                            <span class="stat-label">Teams</span>
                        </div>
                        <div class="stat-box">
                            <i class="fas fa-user-friends"></i>
                            <span class="stat-value">${tournament.players}</span>
                            <span class="stat-label">Players</span>
                        </div>
                        <div class="stat-box">
                            <i class="fas fa-calendar-check"></i>
                            <span class="stat-value">${tournament.matches}</span>
                            <span class="stat-label">Matches</span>
                        </div>
                        <div class="stat-box">
                            <i class="fas fa-dollar-sign"></i>
                            <span class="stat-value">${tournament.prizePool}</span>
                            <span class="stat-label">Prize Pool</span>
                        </div>
                    </div>

                    <div class="tournament-actions">
                        <button class="action-btn btn-view" onclick="viewTournament(${index})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="action-btn btn-edit" onclick="editTournament(${index})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn btn-delete" onclick="deleteTournament(${index})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        }

        // ===== ADD TOURNAMENT MODAL =====

        // Open Add Modal
        function openAddModal() {
            document.getElementById('addTournamentModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close Add Modal
        function closeAddModal() {
            document.getElementById('addTournamentModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('addTournamentForm').reset();
        }

        // Add tournament function
        function addTournament(event) {
            event.preventDefault();
            
            // Get form values
            const name = document.getElementById('addTournamentName').value.trim();
            const type = document.getElementById('addTournamentType').value;
            const icon = document.getElementById('addTournamentIcon').value;
            const teams = parseInt(document.getElementById('addNumTeams').value);
            const players = parseInt(document.getElementById('addNumPlayers').value);
            const matches = parseInt(document.getElementById('addNumMatches').value);
            const prizePool = document.getElementById('addPrizePool').value.trim();

            // Validate inputs
            if (!name || !type || !icon || !teams || !players || !matches || !prizePool) {
                showToast('Please fill all required fields!', 'error');
                return;
            }

            // Create new tournament object
            const newTournament = {
                name: name,
                type: type,
                icon: icon,
                teams: teams,
                players: players,
                matches: matches,
                prizePool: prizePool
            };

            // Add to tournaments array
            tournaments.push(newTournament);
            
            // Re-render tournaments
            renderTournaments();
            
            // Close modal
            closeAddModal();
            
            // Show success message
            showToast(`"${name}" tournament added successfully!`, 'success');
        }

        // ===== EDIT TOURNAMENT MODAL =====

        // Open Edit Modal
        function editTournament(index) {
            currentEditIndex = index;
            const tournament = tournaments[index];
            
            // Populate form fields
            document.getElementById('editTournamentIndex').value = index;
            document.getElementById('editTournamentName').value = tournament.name;
            document.getElementById('editTournamentType').value = tournament.type;
            document.getElementById('editTournamentIcon').value = tournament.icon;
            document.getElementById('editNumTeams').value = tournament.teams;
            document.getElementById('editNumPlayers').value = tournament.players;
            document.getElementById('editNumMatches').value = tournament.matches;
            document.getElementById('editPrizePool').value = tournament.prizePool;
            
            // Show modal
            document.getElementById('editTournamentModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close Edit Modal
        function closeEditModal() {
            document.getElementById('editTournamentModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('editTournamentForm').reset();
            currentEditIndex = -1;
        }

        // Update tournament function
        function updateTournament(event) {
            event.preventDefault();
            
            const index = currentEditIndex;
            
            if (index < 0 || index >= tournaments.length) {
                showToast('Invalid tournament index!', 'error');
                return;
            }
            
            // Get form values
            const name = document.getElementById('editTournamentName').value.trim();
            const type = document.getElementById('editTournamentType').value;
            const icon = document.getElementById('editTournamentIcon').value;
            const teams = parseInt(document.getElementById('editNumTeams').value);
            const players = parseInt(document.getElementById('editNumPlayers').value);
            const matches = parseInt(document.getElementById('editNumMatches').value);
            const prizePool = document.getElementById('editPrizePool').value.trim();

            // Validate inputs
            if (!name || !type || !icon || !teams || !players || !matches || !prizePool) {
                showToast('Please fill all required fields!', 'error');
                return;
            }

            // Update tournament object
            tournaments[index] = {
                name: name,
                type: type,
                icon: icon,
                teams: teams,
                players: players,
                matches: matches,
                prizePool: prizePool
            };
            
            // Re-render tournaments
            renderTournaments();
            
            // Close modal
            closeEditModal();
            
            // Show success message
            showToast(`"${name}" tournament updated successfully!`, 'success');
        }

        // ===== VIEW TOURNAMENT MODAL =====

        // View tournament
        function viewTournament(index) {
            const tournament = tournaments[index];
            
            // Get icon emoji based on icon class
            const iconMap = {
                'fa-trophy': '🏆',
                'fa-star': '⭐',
                'fa-medal': '🏅',
                'fa-crown': '👑',
                'fa-fire': '🔥',
                'fa-bolt': '⚡',
                'fa-gem': '💎',
                'fa-shield-alt': '🛡️'
            };
            
            const iconEmoji = iconMap[tournament.icon] || '🏆';
            
            // Create detailed view HTML
            const detailsHTML = `
                <div class="view-detail-row">
                    <div class="view-detail-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Tournament Name</div>
                        <div class="view-detail-value">${iconEmoji} ${tournament.name}</div>
                    </div>
                </div>
                
                <div class="view-detail-row">
                    <div class="view-detail-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Tournament Type</div>
                        <div class="view-detail-value">${tournament.type}</div>
                    </div>
                </div>
                
                <div class="view-detail-row">
                    <div class="view-detail-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Number of Teams</div>
                        <div class="view-detail-value">${tournament.teams} Teams</div>
                    </div>
                </div>
                
                <div class="view-detail-row">
                    <div class="view-detail-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Number of Players</div>
                        <div class="view-detail-value">${tournament.players} Players</div>
                    </div>
                </div>
                
                <div class="view-detail-row">
                    <div class="view-detail-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Number of Matches</div>
                        <div class="view-detail-value">${tournament.matches} Matches</div>
                    </div>
                </div>
                
                <div class="view-detail-row">
                    <div class="view-detail-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Prize Pool</div>
                        <div class="view-detail-value">💰 ${tournament.prizePool}</div>
                    </div>
                </div>
            `;
            
            // Populate and show view modal
            document.getElementById('viewTournamentDetails').innerHTML = detailsHTML;
            document.getElementById('viewTournamentModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close View Modal
        function closeViewModal() {
            document.getElementById('viewTournamentModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // ===== DELETE TOURNAMENT =====

        // Delete tournament
        function deleteTournament(index) {
            const tournament = tournaments[index];
            
            if (confirm(`Are you sure you want to delete "${tournament.name}"?\n\nThis action cannot be undone.`)) {
                tournaments.splice(index, 1);
                renderTournaments();
                showToast(`"${tournament.name}" deleted successfully!`, 'success');
            }
        }

        // ===== UTILITY FUNCTIONS =====

        // Show toast notification
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

        // ===== EVENT LISTENERS =====

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addTournamentModal');
            const editModal = document.getElementById('editTournamentModal');
            const viewModal = document.getElementById('viewTournamentModal');
            
            if (event.target == addModal) {
                closeAddModal();
            } else if (event.target == editModal) {
                closeEditModal();
            } else if (event.target == viewModal) {
                closeViewModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAddModal();
                closeEditModal();
                closeViewModal();
            }
        });
    </script>
</body>
</html>