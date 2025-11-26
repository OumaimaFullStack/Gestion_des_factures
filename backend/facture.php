<?php
require 'db.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$key = "my_key";
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';
$token = trim(str_replace('Bearer ', '', $authHeader));

if (!$token) {
    exit(json_encode(["error" => "Token manquant ou mal formé"]));
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_utilisateur = $decoded->id_utilisateur ?? null;
    $role = $decoded->role ?? null;

    if (!$id_utilisateur || !$role) {
        exit(json_encode(["error" => "Utilisateur non valide dans le token"]));
    };

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true);
    $inTransaction = false;

    switch ($method) {

        case 'POST':
            if (!isset($data['nom_client'], $data['produits'], $data['date_echeance'])) {
                exit(json_encode(["error" => "Données manquantes : nom_client, produits ou date_echeance"]));
            }

            $nom_client = $data['nom_client'];
            $produits = $data['produits'];
            $etat = $data['etat'] ?? 'en attente';
            $methode_paiement = $data['methode_paiement'] ?? 'espèces';
            $date_echeance = $data['date_echeance'];
            if (empty($produits)) {
                exit(json_encode(["error" => "Aucun produit fourni"]));
            }

            $noms_produits = array_column($produits, "nom");
            

            $stmt = $pdo->prepare("SELECT id_produit, prix_unitaire, nom FROM produit WHERE nom IN ($placeholders)");
            $stmt->execute($noms_produits);
            $produits_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $prix_produits = [];
            foreach ($produits_data as $prod) {
                $prix_produits[$prod["nom"]] = [
                    "id_produit" => $prod["id_produit"],
                    "prix_unitaire" => $prod["prix_unitaire"]
                ];
            }

            $montant_total = 0;
            foreach ($produits as $produit) {
                if (!isset($prix_produits[$produit["nom"]])) {
                    exit(json_encode(["error" => "Produit '" . $produit["nom"] . "' introuvable"]));
                }
                if (!isset($produit['quantite'])) {
                    exit(json_encode(["error" => "Quantité manquante pour le produit '" . $produit['nom'] . "'"]));
                }
                $montant_total += $prix_produits[$produit["nom"]]["prix_unitaire"] * $produit["quantite"];
            }
            try {
                $pdo->beginTransaction();
                $inTransaction = true;

                $stmt = $pdo->prepare("SELECT id_client FROM client WHERE nom = ?");
                $stmt->execute([$nom_client]);
                $client = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$client) {
                    throw new Exception("Client '$nom_client' introuvable");
                }
                $id_client = $client['id_client'];

                $stmt = $pdo->prepare("INSERT INTO facture (id_client, id_utilisateur, montant, etat, methode_paiement, date_echeance) 
                                       VALUES (:id_client, :id_utilisateur, :montant, :etat, :methode_paiement, :date_echeance)");
                $stmt->execute([
                    'id_client' => $id_client,
                    'id_utilisateur' => $id_utilisateur,
                    'montant' => $montant_total,
                    'etat' => $etat,
                    'methode_paiement' => $methode_paiement,
                    'date_echeance' => $date_echeance
                ]);

                $num_facture = $pdo->lastInsertId();

                $stmt_insert = $pdo->prepare("INSERT INTO facture_produit (num_facture, id_produit, quantite_total, prix_total)
                                              VALUES (:num_facture, :id_produit, :quantite_total, :prix_total)");
                foreach ($produits as $produit) {
                    $id_produit = $prix_produits[$produit["nom"]]["id_produit"];
                    $prix_unitaire = $prix_produits[$produit["nom"]]["prix_unitaire"];
                    $quantite = $produit["quantite"];
                    $prix_total = $prix_unitaire * $quantite;

                    $stmt_insert->execute([
                        ":num_facture" => $num_facture,
                        ":id_produit" => $id_produit,
                        ":quantite_total" => $produit["quantite"],
                        ":prix_total" => $prix_total
                    ]);
                }

                $pdo->commit();
                $inTransaction = false;

                $stmt = $pdo->prepare("
                    SELECT f.num_facture, f.montant, f.etat, f.methode_paiement, f.date_echeance,
                           c.nom AS nom_client, GROUP_CONCAT(p.nom SEPARATOR ', ') AS produits
                    FROM facture f
                    JOIN client c ON f.id_client = c.id_client
                    JOIN facture_produit fp ON f.num_facture = fp.num_facture
                    JOIN produit p ON fp.id_produit = p.id_produit
                    WHERE f.num_facture = ?
                    GROUP BY f.num_facture
                ");
                $stmt->execute([$num_facture]);
                $facture = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode(["message" => "Facture créée avec succès", "facture" => $facture]);

            } catch (Exception $e) {
                if ($inTransaction) $pdo->rollBack();
                exit(json_encode(["error" => "Erreur lors de la création", "details" => $e->getMessage()]));
            };

            break;

        case 'GET':
            $stmt = $pdo->prepare("
    SELECT f.num_facture, f.montant, f.etat, f.methode_paiement, f.date_echeance, f.date_creation,
           c.nom AS nom_client, 
           GROUP_CONCAT(p.nom SEPARATOR ', ') AS produits,
           GROUP_CONCAT(fp.quantite_total SEPARATOR ', ') AS quantites
    FROM facture f
    LEFT JOIN client c ON f.id_client = c.id_client
    LEFT JOIN facture_produit fp ON f.num_facture = fp.num_facture
    LEFT JOIN produit p ON fp.id_produit = p.id_produit
    GROUP BY f.num_facture
");
            $stmt->execute();
            $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["factures" => $factures ?: []]);
            break;

            case 'PUT':
                if (!isset($data['num_facture'], $data['nom_client'], $data['produits'], $data['etat'], $data['methode_paiement'], $data['date_echeance'])) {
                    exit(json_encode(["error" => "Champs manquants"]));
                }
            
                $num_facture = $data['num_facture'];
                $nom_client = $data['nom_client'];
                $produits = $data['produits'];
                $etat = $data['etat'];
                $methode_paiement = $data['methode_paiement'];
                $date_echeance = $data['date_echeance'];
    
                $stmt = $pdo->prepare("SELECT id_utilisateur FROM facture WHERE num_facture = ?");
                $stmt->execute([$num_facture]);
                $facture = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$facture) exit(json_encode(["error" => "Facture introuvable"]));
                if ($role !== 'administrateur' && $facture['id_utilisateur'] !== $id_utilisateur) {
                    exit(json_encode(["error" => "Accès refusé"]));
                }
            
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("SELECT id_client FROM client WHERE nom = ?");
                    $stmt->execute([$nom_client]);
                    $client = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$client) throw new Exception("Client introuvable");
            
                    $id_client = $client['id_client'];
            
                   
                    $noms = array_column($produits, 'nom');
                    $placeholders = implode(',', array_fill(0, count($noms), '?'));
                    $stmt = $pdo->prepare("SELECT id_produit, nom, prix_unitaire FROM produit WHERE nom IN ($placeholders)");
                    $stmt->execute($noms);
                    $liste = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                   
                    $infos = [];
                    foreach ($liste as $prod) {
                        $infos[$prod['nom']] = [
                            'id' => $prod['id_produit'],
                            'prix' => $prod['prix_unitaire']
                        ];
                    }
            
                    $total = 0;
                    foreach ($produits as $p) {
                        if (!isset($infos[$p['nom']])) throw new Exception("Produit {$p['nom']} introuvable");
                        if (!isset($p['quantite'])) throw new Exception("Quantité manquante pour {$p['nom']}");
                        $total += $infos[$p['nom']]['prix'] * $p['quantite'];
                    }
            
                   
                    $stmt = $pdo->prepare("UPDATE facture SET id_client=?, etat=?, methode_paiement=?, date_echeance=?, montant=? WHERE num_facture=?");
                    $stmt->execute([$id_client, $etat, $methode_paiement, $date_echeance, $total, $num_facture]);
            
                    $pdo->prepare("DELETE FROM facture_produit WHERE num_facture=?")->execute([$num_facture]);
            
                    $stmt = $pdo->prepare("INSERT INTO facture_produit (num_facture, id_produit, quantite_total, prix_total) VALUES (?, ?, ?, ?)");
                    foreach ($produits as $p) {
                        $id_prod = $infos[$p['nom']]['id'];
                        $qte = $p['quantite'];
                        $prix_total = $infos[$p['nom']]['prix'] * $qte;
                        $stmt->execute([$num_facture, $id_prod, $qte, $prix_total]);
                    }
            
                    $pdo->commit();
                    echo json_encode(["message" => "Facture mise à jour"]);
            
                } catch (Exception $e) {
                    $pdo->rollBack();
                    exit(json_encode(["error" => "Erreur", "details" => $e->getMessage()]));
                }
                break;        
       case 'DELETE':
          $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['num_facture'])) {
        exit(json_encode(["error" => "Numéro de facture manquant"]));
    }
        $num_facture = $data['num_facture'];

    $stmt = $pdo->prepare("SELECT id_utilisateur FROM facture WHERE num_facture = ?");
  $stmt->execute([$num_facture]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$facture) exit(json_encode(["error" => "Facture introuvable"]));
    if ($role !== 'administrateur' && $facture['id_utilisateur'] !== $id_utilisateur) {
        exit(json_encode(["error" => "Accès refusé"]));
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM facture_produit WHERE num_facture = ?")->execute([$num_facture]);
        $pdo->prepare("DELETE FROM facture WHERE num_facture = ?")->execute([$num_facture]);
        $pdo->commit();

        echo json_encode(["message" => "Facture supprimée"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        exit(json_encode(["error" => "Erreur lors de la suppression", "details" => $e->getMessage()]));
    }

    break;

        default:
            echo json_encode(["error" => "Méthode non autorisée"]);
            break;
    };
} 
catch (Exception $e) {
    if (!empty($inTransaction)) $pdo->rollBack();
    exit(json_encode(["error" => "Erreur interne", "details" => $e->getMessage()]));
}
