<?php 
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); 
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'db.php';
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Aucune donnée reçue"]);
        exit;
    }


    $nom = htmlspecialchars($data['nom'] ?? '');
    $prenom = htmlspecialchars($data['prenom'] ?? '');
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($data['telephone'] ?? '');
    $adresse = htmlspecialchars($data['adresse'] ?? '');

   
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "L'email fourni est invalide."]);
        exit;
    }

    try {
        $sql = $pdo->prepare("INSERT INTO client (nom, prenom, email, Telephone, adresse) 
                              VALUES (:nom, :prenom, :email, :telephone, :adresse)");
        
        $sql->bindParam(':nom', $nom);
        $sql->bindParam(':prenom', $prenom);
        $sql->bindParam(':email', $email);
        $sql->bindParam(':telephone', $telephone);
        $sql->bindParam(':adresse', $adresse);

        if ($sql->execute()) {
            http_response_code(201);
            echo json_encode(["success" => "Client ajouté avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de l'ajout du client"]);
        }
        
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erreur lors de l'insertion : " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Méthode non autorisée"]);
}
?>
