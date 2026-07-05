<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>NURISK Command Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: monospace; }
        .map-container { height: 100vh; background-color: #2a2a2a; border-right: 1px solid #444; }
        .feed-container { height: 100vh; overflow-y: auto; padding: 20px; }
        .incident-card { background-color: #1e1e1e; border-left: 4px solid #dc3545; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Left: Live Map -->
            <div class="col-md-8 map-container d-flex align-items-center justify-content-center">
                <h1 class="text-muted">LIVE MAP (Leaflet.js Placeholder)</h1>
            </div>
            
            <!-- Right: Feed -->
            <div class="col-md-4 feed-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-danger fw-bold m-0">NURISK COMMAND CENTER</h4>
                    <span class="badge bg-secondary" id="sync-status">Syncing...</span>
                </div>

                <h6 class="text-uppercase text-muted mb-3">Live Incident Feed</h6>
                
                <div id="incident-feed">
                    <div class="card incident-card border-0 rounded-0 p-3">
                        <small class="text-muted">14:02 WIB</small>
                        <p class="mb-0 fw-bold">Posko Alpha: Butuh Evakuasi Medis Segera.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Simulasi Polling 30 Detik
        setInterval(() => {
            const status = document.getElementById('sync-status');
            status.textContent = "Syncing...";
            status.classList.replace('bg-secondary', 'bg-warning');
            
            setTimeout(() => {
                status.textContent = "Live";
                status.classList.replace('bg-warning', 'bg-success');
            }, 1000);
        }, 30000);
    </script>
</body>
</html>
