<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$pdo = new PDO('mysql:host=localhost;dbname=gestion_factures', 'root', ''); 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    
    if (!$token) {
        die("Token manquant");
    }

    $stmt = $pdo->prepare("SELECT id_utilisateur, reset_expiration FROM utilisateur WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user || strtotime($user['reset_expiration']) < time()) {
        die("Token invalide ou expiré");
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$token || !$password) {
        die("Données manquantes");
    }

    $stmt = $pdo->prepare("SELECT id_utilisateur, reset_expiration FROM utilisateur WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user || strtotime($user['reset_expiration']) < time()) {
        die("Token invalide ou expiré");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $update = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ?, reset_token = NULL, reset_expiration = NULL WHERE id_utilisateur = ?");
    $update->execute([$password_hash, $user['id_utilisateur']]);

    echo "Mot de passe réinitialisé avec succès.";
}
?>
