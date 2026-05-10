<?php
require_once '../config.php';
require_once '../include/header.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

require_connexion();
require_admin();

require_once '../include/menu.php';

$tous_trajets = get_all_trajets($pdo);

$total_places_prises = 0;
$total_places_proposees = 0;
$nb_trajets_confirmes = 0;
$nb_trajets_renforcer = 0;

for ($i = 0; $i < count($tous_trajets); $i++) {
    $trajet = $tous_trajets[$i];
    $places_prises = get_places_prises($pdo, $trajet['id_trajet']);
    $total_places_prises += $places_prises;
    $total_places_proposees += $trajet['places_proposees'];
    if ($places_prises > 0) { $nb_trajets_confirmes++; }
    if ($places_prises >= $trajet['places_proposees']) { $nb_trajets_renforcer++; }
}

$taux_remplissage = 0;
if ($total_places_proposees > 0) {
    $taux_remplissage = round(($total_places_prises / $total_places_proposees) * 100);
}

$demandes_attente = get_demandes_attente($pdo);
$nb_attente = count($demandes_attente);
?>

<div class="container">
    <h1><i class="fa-solid fa-gauge"></i> Tableau de bord - Gestionnaire</h1>

    <div class="grille_stats">
        <div class="carte_stat">
            <div class="stat_icone icone_vert"><i class="fa-solid fa-bus"></i></div>
            <div><strong><?php echo $taux_remplissage; ?>%</strong><span>Taux de remplissage moyen</span></div>
        </div>
        <div class="carte_stat">
            <div class="stat_icone icone_orange"><i class="fa-solid fa-hourglass-half"></i></div>
            <div><strong><?php echo $nb_attente; ?></strong><span>Demandes en attente</span></div>
        </div>
        <div class="carte_stat">
            <div class="stat_icone icone_rouge"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div><strong><?php echo $nb_trajets_renforcer; ?></strong><span>Trajets à renforcer</span></div>
        </div>
        <div class="carte_stat">
            <div class="stat_icone icone_bleu"><i class="fa-solid fa-circle-check"></i></div>
            <div><strong><?php echo $nb_trajets_confirmes; ?></strong><span>Trajets confirmés</span></div>
        </div>
    </div>

    <div class="bloc_dashboard">
        <h2><i class="fa-solid fa-chart-bar"></i> Taux de remplissage par trajet</h2>

        <?php if (count($tous_trajets) == 0): ?>
            <p>Aucun trajet créé. <a href="trajets.php">Créer un trajet</a></p>
        <?php else: ?>
        <table>
            <tr>
                <th>Trajet</th><th>Conducteur</th><th>Horaire</th><th>Capacité</th><th>Remplissage</th><th>Statut</th><th>Actions</th>
            </tr>
            <?php for ($i = 0; $i < count($tous_trajets); $i++): ?>
                <?php
                $trajet = $tous_trajets[$i];
                $places_prises = get_places_prises($pdo, $trajet['id_trajet']);
                $places_dispo = $trajet['places_proposees'] - $places_prises;
                $pourcentage = 0;
                if ($trajet['places_proposees'] > 0) {
                    $pourcentage = round(($places_prises / $trajet['places_proposees']) * 100);
                }
                $couleur_barre = "#52b788";
                if ($pourcentage >= 75) { $couleur_barre = "#f77f00"; }
                if ($pourcentage >= 100) { $couleur_barre = "#d62828"; }
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($trajet['point_depart']); ?></strong> → <?php echo htmlspecialchars($trajet['destination']); ?></td>
                    <td><?php echo htmlspecialchars($trajet['prenom'] . " " . $trajet['nom']); ?></td>
                    <td><?php echo $trajet['horaire']; ?></td>
                    <td><?php echo $places_prises . "/" . $trajet['places_proposees']; ?></td>
                    <td>
                        <div class="barre_fond">
                            <div class="barre_remplissage" style="width:<?php echo $pourcentage; ?>%;background-color:<?php echo $couleur_barre; ?>;"></div>
                        </div>
                    </td>
                    <td>
                        <?php if ($places_dispo <= 0): ?>
                            <span class="badge_rouge">Complet</span>
                        <?php elseif ($places_dispo == 1): ?>
                            <span class="badge_orange">1 place</span>
                        <?php else: ?>
                            <span class="badge_vert">Disponible</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($places_dispo <= 0): ?>
                            <a class="btn btn_orange" href="demandes.php"><i class="fa-solid fa-plus"></i> Renforcer</a>
                        <?php else: ?>
                            <a class="btn" href="demandes.php"><i class="fa-solid fa-eye"></i> Détails</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>
        <?php endif; ?>
    </div>

    <div class="bloc_dashboard">
        <h2><i class="fa-solid fa-gears"></i> Gestion</h2>
        <div class="liens_gestion">
            <a class="btn" href="demandes.php"><i class="fa-solid fa-list-check"></i> Demandes en attente (<?php echo $nb_attente; ?>)</a>
            <a class="btn" href="trajets.php"><i class="fa-solid fa-route"></i> Créer un trajet</a>
            <a class="btn" href="conducteurs.php"><i class="fa-solid fa-id-card"></i> Ajouter un conducteur</a>
            <a class="btn" href="vehicules.php"><i class="fa-solid fa-car"></i> Ajouter un véhicule</a>
        </div>
    </div>
</div>

<?php require_once '../include/footer.php'; ?>