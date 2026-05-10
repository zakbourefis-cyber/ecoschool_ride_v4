<?php
require_once '../config.php';
require_once '../include/header.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

if (!isset($_SESSION['id_conducteur'])) {
    header("Location: " . BASE_URL . "/conducteur/login.php");
    exit();
}

$id_conducteur  = $_SESSION['id_conducteur'];
$prenom         = $_SESSION['prenom_conducteur'];

// Récupérer les parents qui ont des enfants sur mes trajets
$sql_parents = "SELECT DISTINCT p.id_parent, p.nom, p.prenom, t.destination
                FROM parents p
                JOIN enfants e ON e.id_parent = p.id_parent
                JOIN inscriptions i ON i.id_enfant = e.id_enfant
                JOIN trajets t ON i.id_trajet = t.id_trajet
                WHERE t.id_conducteur = ?
                ORDER BY p.prenom ASC";
$stmt = $pdo->prepare($sql_parents);
$stmt->execute([$id_conducteur]);
$liste_parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../include/menu_conducteur.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-comments"></i> Messagerie</h1>

    <div class="bloc_messagerie">

        <div class="messagerie_contacts">
            <span class="detail_label">Vos discussions</span>

            <?php if (!empty($liste_parents)): ?>
                <?php foreach ($liste_parents as $parent): ?>
                    <div class="contact_item"
                         data-id="<?php echo $parent['id_parent']; ?>"
                         onclick="selectionnerParent(<?php echo $parent['id_parent']; ?>, '<?php echo htmlspecialchars($parent['prenom'] . ' ' . $parent['nom']); ?>')">
                        <p class="detail_valeur">
                            <?php echo htmlspecialchars($parent['prenom'] . ' ' . $parent['nom']); ?>
                        </p>
                        <p class="detail_label trajet_nom">
                            Trajet : <?php echo htmlspecialchars($parent['destination']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="info_bulle">Aucun parent trouvé pour vos trajets.</p>
            <?php endif; ?>
        </div>

        <div class="messagerie_chat">
            <div class="chat_ecran" id="chat_ecran">
                <div class="chat_vide">
                    <i class="fa-solid fa-message"></i>
                    <p class="texte_bienvenue">
                        Bonjour <strong><?php echo htmlspecialchars($prenom); ?></strong>,<br>
                        Sélectionnez un parent pour voir vos messages.
                    </p>
                </div>
            </div>

            <form class="chat_form" id="chat_form_conducteur">
                <input type="hidden" name="id_destinataire" id="id_destinataire">
                <input type="text" name="message" id="champ_message" placeholder="Votre message..." required>
                <button type="submit" class="btn btn_envoyer">Envoyer</button>
            </form>
        </div>

    </div>

    <div class="retour_accueil">
        <a class="btn" href="dashboard.php">← Retour au tableau de bord</a>
    </div>
</div>

<script>
let idParentOuvert = null;
let timerRefresh   = null;

function selectionnerParent(idParent, nom) {
    idParentOuvert = idParent;
    document.getElementById('id_destinataire').value = idParent;

    document.querySelectorAll('.contact_item').forEach(function(el) {
        el.classList.remove('contact_actif');
    });
    document.querySelector('.contact_item[data-id="' + idParent + '"]').classList.add('contact_actif');

    document.getElementById('chat_ecran').innerHTML =
        '<div class="chat_header">Discussion avec <strong>' + nom + '</strong></div>' +
        '<div id="liste_messages" class="messages_container">Chargement...</div>';

    if (timerRefresh !== null) clearInterval(timerRefresh);
    rafraichirMessages();
    timerRefresh = setInterval(rafraichirMessages, 5000);
}

function rafraichirMessages() {
    fetch('<?php echo BASE_URL; ?>/conducteur/get_messages_conducteur.php?id_parent=' + idParentOuvert)
        .then(r => r.json())
        .then(function(messages) {
            var container = document.getElementById('liste_messages');

            if (!container) return;

            if (messages.length === 0) {
                container.innerHTML = '<p class="info_bulle">Aucun message. Envoyez le premier !</p>';
                return;
            }

            var html = '';
            for (var i = 0; i < messages.length; i++) {
                var msg = messages[i];
                var dateTexte = msg.date_envoi.replace(' ', 'T');
                var dateHeure = new Date(dateTexte).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});

                // Si id_conducteur_expediteur est non nul → c'est moi (conducteur) → bulle droite
                var cote = msg.id_conducteur_expediteur ? 'parent' : 'conducteur';

                html += `<div class="message_bulle ${cote}">
                    <p>${msg.message}</p>
                    <span class="date">${dateHeure}</span>
                </div>`;
            }

            container.innerHTML = html;
            container.scrollTop = container.scrollHeight;
        })
        .catch(console.error);
}

document.getElementById('chat_form_conducteur').addEventListener('submit', function(e) {
    e.preventDefault();

    var champ   = document.getElementById('champ_message');
    var contenu = champ.value.trim();

    if (contenu === '' || idParentOuvert === null) return;

    var donnees = new FormData();
    donnees.append('id_destinataire', idParentOuvert);
    donnees.append('message', contenu);

    fetch('<?php echo BASE_URL; ?>/conducteur/send_message_conducteur.php', {
        method: 'POST',
        body: donnees
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.succes) {
            champ.value = '';
            rafraichirMessages();
        }
    });
});
</script>

<?php require_once '../include/footer.php'; ?>
