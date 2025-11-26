<?php
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type,Authorization");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'db.php';

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Aucune donnée reçue"]);
        exit;
    }

    $id_client = intval($data['id_client'] ?? 0);
    $nom = htmlspecialchars($data['nom'] ?? '');
    $prenom = htmlspecialchars($data['prenom'] ?? '');
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($data['telephone'] ?? '');
    $adresse = htmlspecialchars($data['adresse'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "L'email fourni est invalide."]);
        exit;
    }
    if ($id_client <= 0) {
        echo json_encode(["error" => "ID client invalide."]);
        exit;
    }

    try {
        $sql = $pdo->prepare("UPDATE client 
                              SET nom = :nom, prenom = :prenom, email = :email,telephone = :telephone, adresse = :adresse 
                              WHERE id_client = :id_client");

        $sql->bindParam(':nom', $nom);
        $sql->bindParam(':prenom', $prenom);
        $sql->bindParam(':email', $email);
        $sql->bindParam(':telephone', $telephone);
        $sql->bindParam(':adresse', $adresse);
        $sql->bindParam(':id_client', $id_client, PDO::PARAM_INT);

        if ($sql->execute()) {
            echo json_encode(["success" => "Le client a été mis à jour avec succès."]);
        } else {
            echo json_encode(["error" => "Erreur lors de la mise à jour du client."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Une erreur est survenue : " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Méthode non autorisée"]);
}
?>
