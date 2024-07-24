<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


global $db;
include 'config db.php';

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$totalRequests = $db->query("SELECT COUNT(*) as count FROM demandes")->fetch(PDO::FETCH_ASSOC)['count'];

$data = [
    'total_requests' => $totalRequests
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="dashboard.css">
</head>
<body>
<?php include "sidebar.html"; ?>

<div class="container mt-4" style="margin-left: 300px;">
    <h1 class="mb-4">Dashboard</h1>
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title" >Total des demandes</h5>
                    <p class="card-text"><?php echo $data['total_requests']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
