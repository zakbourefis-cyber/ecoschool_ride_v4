<?php
require_once '../include/header.php';
require_once '../config.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

require_connexion();

$id_parent = $_SESSION['id_parent'];
$erreur = "";
$succes = "";

// on recupere les enfants pour le select
$liste_enfants = get_enfants_parent($pdo, $id_parent);

// inscription a un trajet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_enfant = $_POST['id_enfant'];
    $id_trajet = $_POST['id_trajet'];

    // verif que l enfant appartient bien au parent connecte
    $isEnfantValide = false;
    for ($i = 0; $i < count($liste_enfants); $i++) {
        if ($liste_enfants[$i]['id_enfant'] == $id_enfant) {
            $isEnfantValide = true;
        }
    }

    if (!$isEnfantValide) {
        $erreur = "Enfant non valide.";
    } else {
        // verif si deja inscrit a ce trajet
        $sqlCheck = "SELECT * FROM inscriptions WHERE id_enfant = ? AND id_trajet = ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$id_enfant, $id_trajet]);
        $dejaInscrit = $stmtCheck->fetch();

        if ($dejaInscrit) {
            $erreur = "Cet enfant est déjà inscrit à ce trajet.";
        } else {
            // on regarde si y a des places dispo
            $trajetInfo = null;
            $tous_trajets = get_all_trajets($pdo);
            for ($j = 0; $j < count($tous_trajets); $j++) {
                if ($tous_trajets[$j]['id_trajet'] == $id_trajet) {
                    $trajetInfo = $tous_trajets[$j];
                }
            }

            $places_prises = get_places_prises($pdo, $id_trajet);
            $statut_inscription = 'EN_ATTENTE';

            // si y a de la place on met VALIDE directement
            if ($trajetInfo != null && $places_prises < $trajetInfo['places_proposees']) {
                $statut_inscription = 'VALIDE';
            }

            $sql = "INSERT INTO inscriptions (id_enfant, id_trajet, statut) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_enfant, $id_trajet, $statut_inscription]);

            if ($statut_inscription == 'VALIDE') {
                $succes = "Inscription confirmée !";
            } else {
                $succes = "Trajet complet, vous êtes sur liste d'attente.";
            }
        }
    }
}

// recuperer tous les trajets avec infos
$tous_trajets = get_all_trajets($pdo);

require_once '../include/menu.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-map-location-dot"></i> <i class="fa-solid fa-check"></i> Réserver un trajet</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>

    <?php if ($succes != ""): ?>
        <p class="message_succes"><?php echo $succes; ?></p>
    <?php endif; ?>

    <?php if (count($liste_enfants) == 0): ?>
        <p>Vous devez d'abord <a href="mes_enfants.php">ajouter un enfant</a> avant de réserver un trajet.</p>
    <?php else: ?>

    <!-- liste des trajets disponibles -->
    <h2>Trajets disponibles</h2>

    <?php if (count($tous_trajets) == 0): ?>
        <p>Aucun trajet disponible pour le moment.</p>
    <?php else: ?>

        <table>
            <tr>
                <th>Départ</th>
                <th>Destination</th>
                <th>Horaire</th>
                <th>Conducteur</th>
                <th>Places</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>

            <?php for ($i = 0; $i < count($tous_trajets); $i++): ?>
                <?php
                $trajet = $tous_trajets[$i];
                $places_prises = get_places_prises($pdo, $trajet['id_trajet']);
                $places_dispo = $trajet['places_proposees'] - $places_prises;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($trajet['point_depart']); ?></td>
                    <td><?php echo htmlspecialchars($trajet['destination']); ?></td>
                    <td><?php echo $trajet['horaire']; ?></td>
                    <td><?php echo htmlspecialchars($trajet['prenom'] . " " . $trajet['nom']); ?></td>
                    <td><?php echo $places_prises . "/" . $trajet['places_proposees']; ?></td>
                    <td>
                        <?php if ($places_dispo > 0): ?>
                            <span class="badge_vert">Disponible</span>
                        <?php else: ?>
                            <span class="badge_rouge">Complet</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- petit formulaire pour chaque trajet -->
                        <form method="POST" action="">
                            <select name="id_enfant">
                                <?php for ($j = 0; $j < count($liste_enfants); $j++): ?>
                                    <option value="<?php echo $liste_enfants[$j]['id_enfant']; ?>">
                                        <?php echo htmlspecialchars($liste_enfants[$j]['prenom']); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <input type="hidden" name="id_trajet" value="<?php echo $trajet['id_trajet']; ?>">
                            <button type="submit">
                                <?php if ($places_dispo > 0): ?>
                                    <i class="fa-solid fa-check"></i> Réserver
                                <?php else: ?>
                                    <i class="fa-solid fa-hourglass-half"></i> Liste d'attente
                                <?php endif; ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>

    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once '../include/footer.php'; ?>