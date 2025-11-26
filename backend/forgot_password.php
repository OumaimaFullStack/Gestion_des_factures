<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email invalide");
    }
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_factures", "root", "");

  
    
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        
        $token = bin2hex(random_bytes(16));
        $expiration = date('Y-m-d H:i:s', time() + 3600); 

       
        $update = $pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_expiration = ? WHERE id_utilisateur = ?");
        $update->execute([$token, $expiration, $user['id_utilisateur']]);
        $reset_link = "http://localhost:5173/reset-password/$token";

        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = 2; 
            $mail->Debugoutput = 'html';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->AuthType = 'LOGIN'; 
            $mail->Username = 'saksomed001@gmail.com';
            $mail->Password = 'ymevsekbbhejezvf'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('saksomed001@gmail.com', 'Gestion des Factures');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reinitialisation de votre mot de passe';
            $mail->Body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href='$reset_link'>$reset_link</a>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi de l'email : {$mail->ErrorInfo}");
        }

        echo "Si cet email existe, un lien de réinitialisation a été envoyé.";
    }
}
?>
