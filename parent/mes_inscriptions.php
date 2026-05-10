<?php
require_once '../config.php';
require_once '../include/header.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

require_connexion();

$id_parent = $_SESSION['id_parent'];
$succes = "";
$erreur = "";

// traitement desinscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'desinscrire') {

    $id_inscription = $_POST['id_inscription'];
    $resultat = desinscrire_enfant($pdo, $id_inscription, $id_parent);

    if ($resultat === false) {
        $erreur = "Impossible de supprimer cette inscription.";
    } else if ($resultat === "libere") {
        $succes = "Inscription annulée. La place a été libérée et attribuée au prochain enfant en attente.";
    } else {
        $succes = "Inscription annulée avec succès.";
    }
}

// on recupere tous les enfants du parent
$liste_enfants = get_enfants_parent($pdo, $id_parent);

require_once '../include/menu.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-list-check"></i> Mes inscriptions</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>
    <?php if ($succes != ""): ?>
        <p class="message_succes"><?php echo $succes; ?></p>
    <?php endif; ?>

    <?php if (count($liste_enfants) == 0): ?>
        <p>Aucun enfant enregistré. <a href="mes_enfants.php"><i class="fa-solid fa-plus"></i> Ajouter un enfant</a></p>
    <?php else: ?>

        <?php for ($i = 0; $i < count($liste_enfants); $i++): ?>
            <?php
            $enfant = $liste_enfants[$i];
            $inscriptions_enfant = get_inscriptions_enfant($pdo, $enfant['id_enfant']);
            ?>

            <div class="bloc_enfant">
                <h2><i class="fa-solid fa-child"></i> <?php echo htmlspecialchars($enfant['prenom']); ?></h2>

                <?php if (count($inscriptions_enfant) == 0): ?>
                    <p>Aucune inscription pour cet enfant. <a href="trajets.php"><i class="fa-solid fa-check"></i> Réserver un trajet</a></p>
                <?php else: ?>

                    <?php for ($j = 0; $j < count($inscriptions_enfant); $j++): ?>
                        <?php $insc = $inscriptions_enfant[$j]; ?>

                        <div class="carte_inscription <?php echo $insc['statut'] == 'VALIDE' ? 'carte_insc_valide' : 'carte_insc_attente'; ?>">

                            <!-- header de la carte -->
                            <div class="carte_insc_header">
                                <div class="carte_insc_trajet">
                                    <span class="trajet_depart"><?php echo htmlspecialchars($insc['point_depart']); ?></span>
                                    <span class="trajet_fleche">→</span>
                                    <span class="trajet_dest"><?php echo htmlspecialchars($insc['destination']); ?></span>
                                </div>
                                <?php if ($insc['statut'] == 'VALIDE'): ?>
                                    <span class="badge_vert"><i class="fa-solid fa-circle-check"></i> Validé</span>
                                <?php else: ?>
                                    <span class="badge_orange"><i class="fa-solid fa-hourglass-half"></i> En attente</span>
                                <?php endif; ?>
                            </div>

                            <!-- details du trajet -->
                            <div class="carte_insc_details">

                                <div class="detail_item">
                                    <span class="detail_label"><i class="fa-solid fa-clock"></i> Horaire</span>
                                    <span class="detail_valeur"><?php echo $insc['horaire']; ?></span>
                                </div>

                                <div class="detail_item">
                                    <span class="detail_label"><i class="fa-solid fa-id-card"></i> Conducteur</span>
                                    <span class="detail_valeur">
                                        <?php echo htmlspecialchars($insc['conducteur_prenom'] . " " . $insc['conducteur_nom']); ?>
                                    </span>
                                </div>

                                <?php if ($insc['conducteur_tel'] != null): ?>
                                <div class="detail_item">
                                    <span class="detail_label"><i class="fa-solid fa-phone"></i> Téléphone</span>
                                    <span class="detail_valeur">
                                        <a href="tel:<?php echo $insc['conducteur_tel']; ?>">
                                            <?php echo htmlspecialchars($insc['conducteur_tel']); ?>
                                        </a>
                                    </span>
                                </div>
                                <?php endif; ?>

                                <?php if ($insc['vehicule_modele'] != null): ?>
                                <div class="detail_item">
                                    <span class="detail_label"><i class="fa-solid fa-car"></i> Véhicule</span>
                                    <span class="detail_valeur">
                                        <?php echo htmlspecialchars($insc['vehicule_modele']); ?>
                                        (<?php echo $insc['capacite_totale']; ?> places)
                                    </span>
                                </div>
                                <?php endif; ?>

                                <div class="detail_item">
                                    <span class="detail_label"><i class="fa-solid fa-calendar"></i> Demande le</span>
                                    <span class="detail_valeur">
                                        <?php
                                        // on formate la date proprement
                                        $date_obj = new DateTime($insc['date_demande']);
                                        echo $date_obj->format('d/m/Y à H:i');
                                        ?>
                                    </span>
                                </div>

                            </div>

                            <!-- bouton desinscription -->
                            <div class="carte_insc_footer">
                                <form method="POST" action=""
                                      onsubmit="return confirm('Annuler l\'inscription de <?php echo htmlspecialchars($enfant['prenom']); ?> sur ce trajet ?')">
                                    <input type="hidden" name="action" value="desinscrire">
                                    <input type="hidden" name="id_inscription" value="<?php echo $insc['id_inscription']; ?>">
                                    <button type="submit" class="btn btn_rouge btn_petit"><i class="fa-solid fa-xmark"></i> Annuler l'inscription</button>
                                </form>
                            </div>

                        </div>

                    <?php endfor; ?>

                <?php endif; ?>
            </div>

        <?php endfor; ?>

    <?php endif; ?>

    <a class="btn" href="trajets.php"><i class="fa-solid fa-plus"></i> <i class="fa-solid fa-check"></i> Réserver un trajet</a>
</div>

<?php require_once '../include/footer.php'; ?>