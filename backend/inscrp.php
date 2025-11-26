<?php
require 'db.php';

header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
header("Content-Type:application/json");
$data=json_decode(file_get_contents("php://input"),true);
if(!isset($data['nom'],$data['prenom'],$data['email'],$data['mot_de_passe'],$data['role'])){
    die(json_encode(["error"=>"tous les champs doivent etre rempli"]));
}
$nom=$data['nom'];
$prenom=$data['prenom'];
$email=$data['email'];
$mot_de_passe=password_hash($data['mot_de_passe'],PASSWORD_BCRYPT);
$role=$data['role'];

$role_valide=['Administrateur','utilisateur standard'];
if(!in_array($role,$role_valide)){
    die(json_encode((["error" =>"role est invalide"])));
}

$query=$pdo->prepare("INSERT INTO utilisateur(nom,prenom,email,mot_de_passe,role) VALUES (:nom,:prenom,:email,:mot_de_passe,:role)");
try{
    $query->execute([
        "nom"=>$nom,
        "prenom"=>$prenom,
        "email"=>$email,
        "mot_de_passe"=>$mot_de_passe,
        "role"=>$role,
    ]);
    echo json_encode(["message"=> "l'inscription est reussi"]);
}catch(PDOException $e){
    die(json_encode("error => Une erreur est survenue"));
}
?>