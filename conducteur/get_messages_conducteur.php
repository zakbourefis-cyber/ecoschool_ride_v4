<?php
session_start();
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_conducteur'])) {
    echo json_encode([["message" => "Non autorisé", "id_conducteur_expediteur" => null]]);
    exit;
}

$id_conducteur = $_SESSION['id_conducteur'];
$id_parent     = isset($_GET['id_parent']) ? intval($_GET['id_parent']) : 0;

if (!$id_parent) {
    echo json_encode([]);
    exit;
}

try {
    // Trouver la conversation entre ce conducteur et ce parent
    $sql_conv = "SELECT id_conversation FROM discussion_membres
                 WHERE id_conducteur = ? AND id_parent = ? LIMIT 1";
    $stmt = $pdo->prepare($sql_conv);
    $stmt->execute([$id_conducteur, $id_parent]);
    $conv = $stmt->fetch();

    if ($conv) {
        $sql_msg = "SELECT message, date_envoi, id_conducteur_expediteur, id_parent_expediteur
                    FROM messages
                    WHERE id_conversation = ?
                    ORDER BY date_envoi ASC";
        $stmt_msg = $pdo->prepare($sql_msg);
        $stmt_msg->execute([$conv['id_conversation']]);
        echo json_encode($stmt_msg->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    echo json_encode([["message" => "Erreur : " . $e->getMessage(), "id_conducteur_expediteur" => null]]);
}
exit;
