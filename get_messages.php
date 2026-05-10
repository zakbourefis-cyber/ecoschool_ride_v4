<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (isset($_GET['id_conducteur'])) {
    $id_parent = $_SESSION['id_parent'] ?? null;
    $id_conducteur = intval($_GET['id_conducteur']);

    if (!$id_parent) {
        echo json_encode([["message" => "Erreur : Session expirée ou invalide. Veuillez vous reconnecter.", "id_parent_expediteur" => null]]);
        exit;
    }

    try {
        // On récupère l'id de la conversation
        $sql_conv = "SELECT id_conversation FROM discussion_membres
                     WHERE id_parent = ? AND id_conducteur = ? LIMIT 1";
        $stmt = $pdo->prepare($sql_conv);
        $stmt->execute([$id_parent, $id_conducteur]);
        $conv = $stmt->fetch();

        if ($conv) {
            // Récupère les messages de cette conversation
            $sql_msg = "SELECT message, date_envoi, id_parent_expediteur
                        FROM messages
                        WHERE id_conversation = ?
                        ORDER BY date_envoi ASC";
            $stmt_msg = $pdo->prepare($sql_msg);
            $stmt_msg->execute([$conv['id_conversation']]);
            $messages = $stmt_msg->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($messages);
         } else {
            // Aucun message encore, retourner un tableau vide
            echo json_encode([]); 
        }
    } catch(Exception $e) {
        echo json_encode([["message" => "Erreur SQL : " . $e->getMessage(), "id_parent_expediteur" => null]]);
    }
}
exit;