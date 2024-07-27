<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
global $db;
include 'config db.php';

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$serialNumbers = [];
try {
    $stmt = $db->query("SELECT `SerialNumber` FROM `demandes` WHERE `SerialNumber` IS NOT NULL AND `SerialNumber` != ''");
    $serialNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo "Error fetching serial numbers: " . $e->getMessage();
}

$requests = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['serial_number'])) {
    $serialNumber = "%" . $_POST['serial_number'] . "%";
    try {
        $stmt = $db->prepare("SELECT `Réf. Demande`, `Client`, `SerialNumber`, `N°VoIP` FROM `demandes` WHERE `SerialNumber` LIKE :serialNumber");
        $stmt->bindParam(':serialNumber', $serialNumber);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching requests: " . $e->getMessage();
    }
} else {
    try {
        $stmt = $db->query("SELECT `Réf. Demande`, `Client`, `SerialNumber`, `N°VoIP` FROM `demandes`");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching requests: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_serial']) && isset($_POST['new_serial_number'])) {
    $refDemande = $_POST['update_serial'];
    $newSerialNumber = $_POST['new_serial_number'];

    try {
        $stmt = $db->prepare("UPDATE `demandes` SET `SerialNumber` = :serialNumber WHERE `Réf. Demande` = :refDemande");
        $stmt->bindParam(':serialNumber', $newSerialNumber);
        $stmt->bindParam(':refDemande', $refDemande);
        $stmt->execute();

        $_SESSION['success_message'] = "Serial number ajouté/mis à jour avec succès";

        header("Location: liste_demandes.php");
        exit();
    } catch (PDOException $e) {
        echo "Error updating serial number: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Liste demandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="listedem.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .autocomplete-suggestions {
            border: 1px solid #ddd;
            max-height: 150px;
            overflow-y: auto;
            background: #fff;
            position: absolute;
            z-index: 1000;
            width: calc(100% - 2px);
        }
        .autocomplete-suggestion {
            padding: 8px;
            cursor: pointer;
        }
        .autocomplete-suggestion:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
<?php include "sidebar.html"; ?>
<div class="container mt-4" style="width:1070px;margin-left:260px;">
    <h1 class="mb-4">Listes des demandes</h1>

    <!-- Search form -->
    <form method="post" action="">
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label" for="serial_number">Recherche par Serial Number</label>
                <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="Enter Serial Number" value="48575443" autocomplete="off">
                <div id="autocomplete-results" class="autocomplete-suggestions"></div>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Recherche</button>
            </div>
        </div>
    </form>

    <!-- Table to display requests -->
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Réf. Demande</th>
            <th>Client</th>
            <th>N°VoIP</th>
            <th>Serial Number</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $request): ?>
            <tr>
                <td><?php echo htmlspecialchars($request['Réf. Demande'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($request['Client'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($request['N°VoIP'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <?php if (empty($request['SerialNumber'])): ?>
                        <form method="post" action="">
                            <div class="input-group">
                                <input type="text" name="new_serial_number" class="form-control" placeholder="Enter Serial Number" required>
                                <input type="hidden" name="update_serial" value="<?php echo htmlspecialchars($request['Réf. Demande'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <form method="post" action="">
                            <div class="input-group">
                                <input type="text" name="new_serial_number" class="form-control" value="<?php echo htmlspecialchars($request['SerialNumber'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter Serial Number" required>
                                <input type="hidden" name="update_serial" value="<?php echo htmlspecialchars($request['Réf. Demande'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-primary">Mise à jour</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        var serialNumbers = <?php echo json_encode($serialNumbers); ?>;
        var fixedPart = "48575443";
        var serialInput = $("#serial_number");

        serialInput.on("input", function() {
            var query = fixedPart + $(this).val().substring(fixedPart.length).toUpperCase();
            var suggestions = serialNumbers.filter(function(serial) {
                return serial.includes(query);
            });

            var suggestionsHtml = suggestions.map(function(suggestion) {
                return '<div class="autocomplete-suggestion" onclick="selectSuggestion(\'' + suggestion + '\')">' + suggestion + '</div>';
            }).join('');

            $("#autocomplete-results").html(suggestionsHtml);
        });
    });

    function selectSuggestion(suggestion) {
        var fixedPart = "48575443";
        $("#serial_number").val(suggestion.replace(fixedPart, ""));
        $("#autocomplete-results").empty();
    }
</script>
</body>
</html>



