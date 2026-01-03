<?php
    session_start();
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard | CrickFolio</title>
    <link rel="stylesheet" href="assets/css/leaderboard.css">
</head>

<?php
    include "header.php";
?>

<body>
    <div class="container">
        <div class="page-header">
            <h1>CRICKET LEADERBOARD</h1>
            <p><i class="fas fa-trophy"></i>Don't miss these amazing performers</p>
        </div>

        <div class="grid" id="leaderboard"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const players = [{
                rank: 1,
                name: 'MANAV BAGADIA',
                score: '36.5 LAKH POINT',
                team: 'Cloud9 Dragons',
                league: 'Cloud9 Premier League - Season 3',
                location: 'Mumbai',
                date: '30 Nov 2025',
                time: '5:00 PM',
                medal: 'gold'
            },
            {
                rank: 2,
                name: 'DANE CHAITANYA HARISHCHANDRA',
                score: '3.5 LAKH RS',
                team: 'THE FLAMES XI',
                league: 'PPL - 2025',
                location: 'Udaipur',
                date: '29 Nov 2025',
                time: '2:00 PM',
                medal: 'silver'
            },
            {
                rank: 3,
                name: 'NAVIN BHAVSAR',
                score: '70,000 POINT',
                team: 'Lalo Warriors Navsari',
                league: 'SBSPL 2026 - Season 3',
                location: 'Navsari',
                date: '30 Nov 2025',
                time: '5:00 PM',
                medal: 'bronze'
            },
            {
                rank: 4,
                name: 'RATHOD HIREN',
                score: '3.3 LAKH RS',
                team: 'Warriors United',
                league: 'Premier League - 2025',
                location: 'Ahmedabad',
                date: '28 Nov 2025',
                time: '6:00 PM',
                medal: null
            },
            {
                rank: 5,
                name: 'BHAVESH VISAVE',
                score: '65,000 POINT',
                team: 'Thunder Strikers',
                league: 'Championship Series - Season 2',
                location: 'Surat',
                date: '01 Dec 2025',
                time: '4:00 PM',
                medal: null
            },
            {
                rank: 6,
                name: 'FAIYYAZ VOHARA',
                score: '65 LAKH',
                team: 'Royal Challengers',
                league: 'Elite Cup - 2025',
                location: 'Bangalore',
                date: '02 Dec 2025',
                time: '7:00 PM',
                medal: null
            }
        ];

        document.getElementById('leaderboard').innerHTML = players.map(p => `
            <div class="card">
                <div class="card-header-section">
                    <span class="league-badge">${p.league.split(' ')[0]}</span>
                    <div class="rank-badge">${p.rank}</div>
                    <h3 class="player-title">${p.name}</h3>
                </div>

                ${p.medal ? `
                    <div class="medal-section">
                        <div class="medal-icon ${p.medal}">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="score-display">${p.score}</div>
                    </div>
                ` : ''}

                <div class="info-section">
                    ${!p.medal ? `
                        <div class="info-row">
                            <div class="icon-wrapper">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Score:</span>
                                <span class="info-value">${p.score}</span>
                            </div>
                        </div>
                    ` : ''}
                    <div class="info-row">
                        <div class="icon-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Location:</span>
                            <span class="info-value">${p.location}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="icon-wrapper">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Date:</span>
                            <span class="info-value">${p.date}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="icon-wrapper">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Time:</span>
                            <span class="info-value">${p.time}</span>
                        </div>
                    </div>
                </div>

                <div class="medal-section">
                    <div class="crick-badge">
                        <div class="badge-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span class="badge-text">Crick Hunt</span>
                    </div>
                </div>
            </div>
        `).join('');
    </script>
</body>

</html>