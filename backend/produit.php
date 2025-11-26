<?php
require 'db.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

header('Content-Type: application/json');

try {
    if (!isset($pdo)) {
        throw new Exception("Erreur de connexion à la base de données.");
    }

    $sql = $pdo->query("SELECT nom FROM produit");
    $produits = $sql->fetchAll(PDO::FETCH_ASSOC);

    if (!$produits) {
        http_response_code(404);
        echo json_encode(["error" => "Aucun produit trouvé"]);
    } else {
        echo json_encode($produits);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des produits: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
