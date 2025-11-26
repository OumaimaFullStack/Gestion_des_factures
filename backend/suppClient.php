<?php
require 'db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id_client'])) {
        $id_client = filter_var($data['id_client'], FILTER_VALIDATE_INT);

        if ($id_client === false) {
            echo json_encode(["status" => "error", "message" => "ID du client invalide."]);
            exit();
        }

        try {
            $sql = "DELETE FROM client WHERE id_client = :id_client";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_client' => $id_client]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "success", "message" => "Client supprimé avec succès."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Aucun client trouvé avec cet ID."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Erreur lors de la suppression du client : " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "ID du client manquant."]);
    }
} else {
    
    echo json_encode(["status" => "error", "message" => "Méthode de requête invalide."]);
}
?>
