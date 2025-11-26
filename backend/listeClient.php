<?php
require 'db.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

try {
    if (!isset($pdo)) {
        throw new Exception("Erreur de connexion à la base de données.");
    }

    $sql = $pdo->query("SELECT id_client, nom FROM client");
    $clients = $sql->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["clients" => $clients ?: []]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des clients: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
