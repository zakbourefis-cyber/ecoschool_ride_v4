<?php
require_once '../include/header.php';
require_once '../config.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

require_connexion();
require_admin();

$succes = "";
$erreur = "";

// approuver ou refuser une demande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_inscription = $_POST['id_inscription'];
    $action = $_POST['action'];

    if ($action == "approuver") {
        // on verifie que la place est encore dispo
        $sqlGetInscription = "SELECT * FROM inscriptions WHERE id_inscription = ?";
        $stmtGet = $pdo->prepare($sqlGetInscription);
        $stmtGet->execute([$id_inscription]);
        $inscription = $stmtGet->fetch(PDO::FETCH_ASSOC);

        $places_prises = get_places_prises($pdo, $inscription['id_trajet']);

        // on recupere la capacite du trajet
        $sqlCapacite = "SELECT t.places_proposees FROM trajets t WHERE t.id_trajet = ?";
        $stmtCap = $pdo->prepare($sqlCapacite);
        $stmtCap->execute([$inscription['id_trajet']]);
        $capacite = $stmtCap->fetchColumn();

        if ($places_prises >= $capacite) {
            $erreur = "Plus de place disponible sur ce trajet !";
        } else {
            $sql = "UPDATE inscriptions SET statut = 'VALIDE' WHERE id_inscription = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_inscription]);
            $succes = "Demande approuvée !";
        }

    } else if ($action == "refuser") {
        // on supprime l inscription
        $sql = "DELETE FROM inscriptions WHERE id_inscription = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_inscription]);
        $succes = "Demande refusée et supprimée.";
    }
}

// on recupere les demandes en attente
$demandes = get_demandes_attente($pdo);

// on recupere aussi les trajets complets pour la section "à renforcer"
$tous_trajets = get_all_trajets($pdo);
$trajets_complets = [];

for ($i = 0; $i < count($tous_trajets); $i++) {
    $trajet = $tous_trajets[$i];
    $places_prises = get_places_prises($pdo, $trajet['id_trajet']);

    if ($places_prises >= $trajet['places_proposees']) {
        // on compte combien sont en attente sur ce trajet
        $sqlAttente = "SELECT COUNT(*) FROM inscriptions WHERE id_trajet = ? AND statut = 'EN_ATTENTE'";
        $stmtA = $pdo->prepare($sqlAttente);
        $stmtA->execute([$trajet['id_trajet']]);
        $nb_en_attente = $stmtA->fetchColumn();

        $trajet['places_prises'] = $places_prises;
        $trajet['nb_en_attente'] = $nb_en_attente;
        $trajets_complets[] = $trajet;
    }
}

require_once '../include/menu.php';
?>

<div class="container">

    <?php if ($succes != ""): ?>
        <p class="message_succes"><?php echo $succes; ?></p>
    <?php endif; ?>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>

    <!-- demandes en attente -->
    <div class="bloc_dashboard">
        <h2><i class="fa-solid fa-hourglass-half"></i> Demandes en attente
            <span class="badge_orange"><?php echo count($demandes); ?> en attente</span>
        </h2>

        <?php if (count($demandes) == 0): ?>
            <p>Aucune demande en attente <i class="fa-solid fa-check-circle"></i></p>
        <?php else: ?>

        <table>
            <tr>
                <th>Enfant</th>
                <th>Parent</th>
                <th>Trajet demandé</th>
                <th>Date demande</th>
                <th>Position</th>
                <th>Actions</th>
            </tr>

            <?php
            // variable pour compter la position par trajet
            $compteur_position = [];

            for ($i = 0; $i < count($demandes); $i++):
                $demande = $demandes[$i];
                $id_trajet = $demande['id_trajet'];

                // on incremente le compteur pour ce trajet
                if (!isset($compteur_position[$id_trajet])) {
                    $compteur_position[$id_trajet] = 1;
                } else {
                    $compteur_position[$id_trajet]++;
                }
                $position = $compteur_position[$id_trajet];
            ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($demande['prenom_enfant']); ?></strong></td>
                    <td><?php echo htmlspecialchars($demande['prenom_parent'] . " " . $demande['nom_parent']); ?></td>
                    <td><?php echo htmlspecialchars($demande['point_depart']); ?> → <?php echo htmlspecialchars($demande['destination']); ?> (<?php echo $demande['horaire']; ?>)</td>
                    <td><?php echo $demande['date_demande']; ?></td>
                    <td>#<?php echo $position; ?></td>
                    <td>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="id_inscription" value="<?php echo $demande['id_inscription']; ?>">
                            <input type="hidden" name="action" value="approuver">
                            <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Approuver</button>
                        </form>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="id_inscription" value="<?php echo $demande['id_inscription']; ?>">
                            <input type="hidden" name="action" value="refuser">
                            <button type="submit" class="btn btn_rouge"><i class="fa-solid fa-xmark"></i> Refuser</button>
                        </form>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>

        <?php endif; ?>
    </div>

    <!-- trajets a renforcer -->
    <?php if (count($trajets_complets) > 0): ?>
    <div class="bloc_dashboard">
        <h2><i class="fa-solid fa-triangle-exclamation"></i> Trajets à renforcer
            <span class="badge_rouge">Action requise</span>
        </h2>
        <p class="info_orange"><i class="fa-solid fa-triangle-exclamation"></i> Ces trajets nécessitent l'ajout d'un véhicule supplémentaire en raison d'une forte demande.</p>

        <div class="grille_trajets_renforcer">
            <?php for ($i = 0; $i < count($trajets_complets); $i++): ?>
                <?php $tc = $trajets_complets[$i]; ?>
                <div class="carte_renforcer">
                    <h3><i class="fa-solid fa-bus"></i> <?php echo htmlspecialchars($tc['point_depart']); ?> → <?php echo htmlspecialchars($tc['destination']); ?></h3>
                    <p><i class="fa-solid fa-clock"></i> <?php echo $tc['horaire']; ?> &nbsp; <i class="fa-solid fa-users"></i> <?php echo $tc['places_prises']; ?>/<?php echo $tc['places_proposees']; ?> + <?php echo $tc['nb_en_attente']; ?> en attente</p>
                    <p class="alerte_rouge"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $tc['nb_en_attente']; ?> enfant(s) en liste d'attente</p>
                    <a class="btn" href="vehicules.php">+ Ajouter un véhicule</a>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>

    <a class="btn" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord</a>
</div>

<?php require_once '../include/footer.php'; ?>