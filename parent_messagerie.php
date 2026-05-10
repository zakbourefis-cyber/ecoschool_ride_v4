<?php
require_once 'config.php';
require_once 'include/header.php';
require_once 'include/fonctions.php';
require_once 'include/connexion.php';

require_connexion(); 

$id_parent = $_SESSION['id_parent'];
$prenom_parent = $_SESSION['prenom'];

/* L'envoi de message est maintenant géré par send_message.php via AJAX,
   il n'y a plus besoin de traiter le POST ici */

/* Trouve les conducteurs des trajets de ce parent */
$sql_conducteurs = "SELECT DISTINCT c.id_conducteur, c.nom, c.prenom, t.destination
                    FROM conducteurs c
                    JOIN trajets t ON c.id_conducteur = t.id_conducteur
                    JOIN inscriptions i ON t.id_trajet = i.id_trajet
                    JOIN enfants e ON i.id_enfant = e.id_enfant
                    WHERE e.id_parent = ?";
$stmt = $pdo->prepare($sql_conducteurs);
$stmt->execute([$_SESSION['id_parent']]);
$liste_contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'include/menu.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-comments"></i> Messagerie</h1>

    <div class="bloc_messagerie">

        <div class="messagerie_contacts">
            <span class="detail_label">Vos discussions</span>

            <?php if (!empty($liste_contacts)): ?>
                <?php foreach ($liste_contacts as $contact): ?>
                    <div class="contact_item" data-id="<?php echo $contact['id_conducteur']; ?>" onclick="selectionnerConducteur(<?php echo $contact['id_conducteur']; ?>, '<?php echo htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']); ?>')">
                        <p class="detail_valeur">
                            <?php echo htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']); ?>
                        </p> 
                        <p class="detail_label trajet_nom">
                            Trajet : <?php echo htmlspecialchars($contact['destination']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="info_bulle">Aucun conducteur actif trouvé.</p>
            <?php endif; ?>

            <p class="detail_label info_bulle">
                Seuls vos conducteurs actifs apparaissent ici
            </p>
        </div>

        <div class="messagerie_chat">
            <div class="chat_ecran" id="chat_ecran">
                <div class="chat_vide">
                    <i class="fa-solid fa-message"></i>
                    <p class="texte_bienvenue">
                        Bonjour <strong><?php echo htmlspecialchars($prenom_parent); ?></strong>,<br>
                        Sélectionnez un conducteur pour voir vos messages.
                    </p>
                </div>
            </div>

            <form class="chat_form" method="POST" action="parent_messagerie.php">
                <input type="hidden" name="id_destinataire" id="id_destinataire">
                <input type="text" name="message" placeholder="Votre message..." required>
                <button type="submit" class="btn btn_envoyer">Envoyer</button>
            </form>
        </div>

    </div>

    <div class="retour_accueil">
        <a class="btn" href="index.php">← Retour à l'accueil</a>
    </div>  
</div> 

<script src="js/messagerie.js?v=<?php echo time(); ?>"></script>
<?php require_once 'include/footer.php'; ?>