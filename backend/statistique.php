<?php
require 'db.php';
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $sql_total = "SELECT COUNT(*) AS total_factures FROM facture";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute();
    $total_factures = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_factures'];

    $sql_impayees = "SELECT COUNT(*) AS factures_impayees FROM facture WHERE etat='impayé'";
    $stmt_impayees = $pdo->prepare($sql_impayees);
    $stmt_impayees->execute();
    $factures_impayees = $stmt_impayees->fetch(PDO::FETCH_ASSOC)['factures_impayees'];

    $sql_payes = "SELECT COUNT(*) AS factures_payes FROM facture WHERE etat='payé'";
    $stmt_payes = $pdo->prepare($sql_payes);
    $stmt_payes->execute();
    $facture_payes = $stmt_payes->fetch(PDO::FETCH_ASSOC)['factures_payes'];

    $sql_montant = "SELECT SUM(montant) AS montant_total FROM facture";
    $stmt_montant = $pdo->prepare($sql_montant);
    $stmt_montant->execute();
    $montant = $stmt_montant->fetch(PDO::FETCH_ASSOC)['montant_total'];

    $sql_factures_mensuelles = "
        SELECT MONTH(date_creation) AS mois, COUNT(*) AS nombre_factures
        FROM facture
        GROUP BY MONTH(date_creation)
        ORDER BY MONTH(date_creation)
    ";
    $stmt_factures_mensuelles = $pdo->prepare($sql_factures_mensuelles);
    $stmt_factures_mensuelles->execute();
    $factures_mensuelles = $stmt_factures_mensuelles->fetchAll(PDO::FETCH_ASSOC);

    $sql_chiffre_affaires_mensuel = "
        SELECT MONTH(date_creation) AS mois, SUM(montant) AS chiffre_affaires
        FROM facture
        GROUP BY MONTH(date_creation)
        ORDER BY MONTH(date_creation)
    ";
    $stmt_chiffre_affaires_mensuel = $pdo->prepare($sql_chiffre_affaires_mensuel);
    $stmt_chiffre_affaires_mensuel->execute();
    $chiffre_affaires_mensuel = $stmt_chiffre_affaires_mensuel->fetchAll(PDO::FETCH_ASSOC);

    $mois_complets = array_fill(1, 12, ['nombre_factures' => 0, 'chiffre_affaires' => 0]);

    foreach ($factures_mensuelles as $facture) {
        $mois = (int)$facture['mois'];
        if ($mois >= 1 && $mois <= 12) {
            $mois_complets[$mois]['nombre_factures'] = (int)$facture['nombre_factures'];
        }
    }

    foreach ($chiffre_affaires_mensuel as $chiffre) {
        $mois = (int)$chiffre['mois'];
        if ($mois >= 1 && $mois <= 12) {
            $mois_complets[$mois]['chiffre_affaires'] = (float)$chiffre['chiffre_affaires'];
        }
    }

    $factures_mensuelles_formatees = [];
    $chiffre_affaires_mensuel_formate = [];

    for ($mois = 1; $mois <= 12; $mois++) {
        $factures_mensuelles_formatees[] = [
            'mois' => $mois,
            'nombre_factures' => $mois_complets[$mois]['nombre_factures']
        ];
        $chiffre_affaires_mensuel_formate[] = [
            'mois' => $mois,
            'chiffre_affaires' => $mois_complets[$mois]['chiffre_affaires']
        ];
    }

    $resultat = [
        'total_factures' => $total_factures,
        'factures_impayees' => $factures_impayees,
        'factures_payes' => $facture_payes,
        'montant_total' => $montant ?? 0,
        'factures_mensuelles' => $factures_mensuelles_formatees,
        'chiffre_affaires_mensuel' => $chiffre_affaires_mensuel_formate
    ];
    header('Content-Type: application/json');
    echo json_encode($resultat);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
}
?>