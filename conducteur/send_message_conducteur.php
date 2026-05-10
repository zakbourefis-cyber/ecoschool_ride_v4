<?php
session_start();
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_conducteur'])) {
    echo json_encode(['succes' => false, 'erreur' => 'Non autorisé']);
    exit;
}

$id_conducteur = $_SESSION['id_conducteur'];
$id_parent     = isset($_POST['id_destinataire']) ? intval($_POST['id_destinataire']) : 0;
$message       = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$id_parent || $message === '') {
    echo json_encode(['succes' => false, 'erreur' => 'Données manquantes']);
    exit;
}

try {
    // Chercher ou créer la conversation
    $sql_conv = "SELECT id_conversation FROM discussion_membres
                 WHERE id_conducteur = ? AND id_parent = ? LIMIT 1";
    $stmt = $pdo->prepare($sql_conv);
    $stmt->execute([$id_conducteur, $id_parent]);
    $conv = $stmt->fetch();

    if (!$conv) {
        // Créer la conversation
        $pdo->prepare("INSERT INTO conversations (date_creation, date_dernier_message) VALUES (NOW(), NOW())")
            ->execute();
        $id_conv = $pdo->lastInsertId();

        // Lier les membres
        $pdo->prepare("INSERT INTO discussion_membres (id_conversation, id_parent, id_conducteur) VALUES (?, ?, ?)")
            ->execute([$id_conv, $id_parent, $id_conducteur]);
    } else {
        $id_conv = $conv['id_conversation'];
        // Mettre à jour la date du dernier message
        $pdo->prepare("UPDATE conversations SET date_dernier_message = NOW() WHERE id = ?")
            ->execute([$id_conv]);
    }

    // Insérer le message (expéditeur = conducteur)
    $sql_msg = "INSERT INTO messages (id_conversation, id_conducteur_expediteur, message, date_envoi)
                VALUES (?, ?, ?, NOW())";
    $pdo->prepare($sql_msg)->execute([$id_conv, $id_conducteur, $message]);

    echo json_encode(['succes' => true]);
} catch (Exception $e) {
    echo json_encode(['succes' => false, 'erreur' => $e->getMessage()]);
}
exit;
