<?php
require 'db.php';

$nom_client = $_GET['nom_client'] ?? '';
$date_creation = $_GET['date_creation'] ?? '';
$date_echeance = $_GET['date_echeance'] ?? '';
$etat = $_GET['etat'] ?? '';

$sql = "SELECT * FROM facture WHERE 1=1";
$params = [];

if (!empty($nom_client)) {
    $sql .= " AND nom_client LIKE :client"; 
    $params[':client'] = "%$nom_client%";  
}
if (!empty($date_creation)) {
    $sql .= " AND DATE(date_creation) = :date_creation";
    $params[':date_creation'] = $date_creation;
}
if (!empty($date_echeance)) {
    $sql .= " AND DATE(date_echeance) = :date_echeance";
    $params[':date_echeance'] = $date_echeance;
}
if (!empty($etat)) {
    $sql .= " AND etat = :etat";
    $params[':etat'] = $etat;
}

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($factures);
?>
