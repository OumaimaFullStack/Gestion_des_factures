<?php
require 'db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

header('Content-Type: application/json');

try {
    if (!isset($pdo)) {
        throw new Exception("Erreur de connexion à la base de données.");
    }

    if (isset($_GET['id_client']) && is_numeric($_GET['id_client'])) {
        $id_client = intval($_GET['id_client']);
        $sql = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
        $sql->execute([$id_client]);
        $client = $sql->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            echo json_encode($client);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Client non trouvé"]);
        }
    } else {
        // Récupération de tous les clients
        $sql = $pdo->query("SELECT * FROM client");
        $clients = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (!$clients) {
            http_response_code(404);
            echo json_encode(["error" => "Aucun client trouvé"]);
        } else {
            echo json_encode($clients);
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des clients: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
