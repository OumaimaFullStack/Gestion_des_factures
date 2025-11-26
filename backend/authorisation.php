<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


function verificationRole($role_requis) {
    $key = "my_key"; 

    $headers = getallheaders();
    error_log(" Headers reçus : " . print_r($headers, true));

    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        die(json_encode(["error" => "Accès interdit : Aucun token fourni"]));
    }

    $authHeader = $headers['Authorization'];
    error_log(" Authorization Header: " . $authHeader);

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1]; 
        error_log("🔍 Token extrait : " . $token);
    } else {
        http_response_code(401);
        die(json_encode(["error" => "Format d'en-tête Authorization invalide"]));
    }

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        error_log(" Token décodé avec succès : " . print_r($decoded, true));

        if (!isset($decoded->id_utilisateur)) {
            error_log(" Claim id_utilisateur manquant dans le token");
            http_response_code(401);
            die(json_encode(["error" => "Claim id_utilisateur manquant"]));
        }

        if ($decoded->role !== $role_requis) {
            error_log(" Rôle incorrect : " . $decoded->role . " (Attendu : $role_requis)");
            http_response_code(403);
            die(json_encode(["error" => "Accès refusé : Vous n'avez pas les permissions nécessaires"]));
        }

        $_SESSION['id_utilisateur'] = $decoded->id_utilisateur;

    } catch (Exception $e) {
        error_log(" Erreur lors du décodage du token : " . $e->getMessage());
        http_response_code(401);
        die(json_encode([
            "error" => "Token invalide ou expiré",
            "details" => $e->getMessage(),
            "token" => $token
        ]));
    }
}
?>
