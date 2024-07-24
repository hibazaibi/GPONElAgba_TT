<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

global $db;
include 'config db.php';

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmt = $db->query("SELECT `Réf. Demande` FROM demandes");
    $references = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$details = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ref_demande'])) {
    $refDemande = $_POST['ref_demande'];
    try {
        $stmt = $db->prepare("SELECT `Réf. Demande`, `Client`, `N°VoIP`, `Adresse Installation`, `FSI`, `Débit`, `NAT SERVICE` FROM demandes WHERE `Réf. Demande` = :refDemande");
        $stmt->bindParam(':refDemande', $refDemande);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Référence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mainpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include "sidebar.html"; ?>
<div class="container">
    <div class="col-12 px-0 mb-4"></div>
    <div class="col-12 px-0 mb-4">
        <div class="container-box d-flex" style="margin-left:150px;">
            <div class="col-xl-4">
                <div class="card mb-4" style="width:370px;">
                    <div class="card-header py-3" style="background-color:#034D89">
                        <h6 style="padding-top: 9px;color: white;font-size: 15px">Recherche par Référence</h6>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="row gx-3 mb-3">
                                <div class="col-md-6">
                                    <label class="small mb-1" for="ref_demande">Référence TT<span style="color: #D72A12">*</span></label>
                                    <select name="ref_demande" id="ref_demande" class="form-control" style="width:200px;">
                                        <option value="" selected disabled>--Choisir la référence--</option>
                                        <?php
                                        foreach ($references as $reference) {
                                            echo '<option value="' . htmlspecialchars($reference['Réf. Demande'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($reference['Réf. Demande'], ENT_QUOTES, 'UTF-8') . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary" style="background-color:#034D89;border-color:#034D89;margin-left:40px;">Afficher</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php if ($details): ?>
                <div class="col-xl-8"style="margin-left:100px;">
                    <div class="card mb-4" >
                        <div class="card-header py-3" style="background-color:#034D89">
                            <h6 style="padding-top: 9px;color: white;font-size: 15px">Détails de la Référence</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Réf. Demande:</strong> <?php echo htmlspecialchars($details['Réf. Demande'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Client:</strong> <?php echo htmlspecialchars($details['Client'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>N°VoIP:</strong> <?php echo htmlspecialchars($details['N°VoIP'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Adresse Installation:</strong> <?php echo htmlspecialchars($details['Adresse Installation'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>FSI:</strong> <?php echo htmlspecialchars($details['FSI'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Débit:</strong> <?php echo htmlspecialchars($details['Débit'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>NAT SERVICE:</strong> <?php echo htmlspecialchars($details['NAT SERVICE'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
