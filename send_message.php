<?php
require_once 'config.php';
require_once 'include/connexion.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// On indique qu'on va renvoyer du JSON pour le JavaScript
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_parent'])) {
    
    $id_parent = $_SESSION['id_parent'];
    $message = trim($_POST['message']);
    // On récupère l'ID du conducteur depuis le champ caché envoyé par le JS
    $id_conducteur = intval($_POST['id_destinataire']); 

    if (!empty($message) && $id_conducteur > 0) {
        try {
            // 1. Chercher si une conversation existe DÉJÀ entre ce parent et ce conducteur
            $sqlCheck = "SELECT id_conversation FROM discussion_membres WHERE id_parent = ? AND id_conducteur = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$id_parent, $id_conducteur]);
            $conv = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($conv) {
                $id_conversation = $conv['id_conversation'];
            } else {
                // 2. Si elle n'existe pas, on CRÉE une nouvelle conversation
                $pdo->exec("INSERT INTO conversations () VALUES ()");
                $id_conversation = $pdo->lastInsertId();

                // On lie le parent et le conducteur à cette nouvelle conversation
                $sqlLink = "INSERT INTO discussion_membres (id_conversation, id_parent, id_conducteur) VALUES (?, ?, ?)";
                $stmtLink = $pdo->prepare($sqlLink);
                $stmtLink->execute([$id_conversation, $id_parent, $id_conducteur]);
            }

            // 3. On insère le message (avec id_parent_expediteur)
            $sqlMsg = "INSERT INTO messages (id_conversation, id_parent_expediteur, message) VALUES (?, ?, ?)";
            $stmtMsg = $pdo->prepare($sqlMsg);
            $stmtMsg->execute([$id_conversation, $id_parent, $message]);

            // 4. On met à jour l'heure du dernier message de la conversation
            $pdo->prepare("UPDATE conversations SET date_dernier_message = NOW() WHERE id = ?")->execute([$id_conversation]);

            echo json_encode(['succes' => true]);

        } catch (PDOException $e) {
            echo json_encode(['succes' => false, 'message' => 'Erreur BDD: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['succes' => false, 'message' => 'Message vide ou destinataire manquant']);
    }
} else {
    echo json_encode(['succes' => false, 'message' => 'Non autorisé. Veuillez vous connecter.']);
}
?>