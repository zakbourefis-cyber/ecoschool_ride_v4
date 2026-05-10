<?php
require_once '../config.php';
require_once '../include/header.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

// Vérif session conducteur
if (!isset($_SESSION['id_conducteur'])) {
    header("Location: " . BASE_URL . "/conducteur/login.php");
    exit();
}

$id_conducteur  = $_SESSION['id_conducteur'];
$prenom         = $_SESSION['prenom_conducteur'];
$nom            = $_SESSION['nom_conducteur'];

// -------------------------------------------------------
// Traitement : valider / refuser une inscription
// -------------------------------------------------------
$message_action = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_inscription'])) {
    $id_inscription = intval($_POST['id_inscription']);
    $action         = $_POST['action'];

    // Vérifier que cette inscription concerne bien un trajet de CE conducteur
    $sqlCheck = "SELECT i.id_inscription, i.id_trajet, t.places_proposees
                 FROM inscriptions i
                 JOIN trajets t ON i.id_trajet = t.id_trajet
                 WHERE i.id_inscription = ? AND t.id_conducteur = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$id_inscription, $id_conducteur]);
    $insc = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($insc) {
        if ($action === 'valider') {
            // Vérifier qu'il reste de la place
            $places_prises = get_places_prises($pdo, $insc['id_trajet']);
            if ($places_prises < $insc['places_proposees']) {
                $pdo->prepare("UPDATE inscriptions SET statut = 'VALIDE' WHERE id_inscription = ?")
                    ->execute([$id_inscription]);
                $message_action = "✅ Inscription validée.";
            } else {
                $message_action = "⚠️ Plus de place disponible sur ce trajet.";
            }
        } elseif ($action === 'refuser') {
            $pdo->prepare("DELETE FROM inscriptions WHERE id_inscription = ?")
                ->execute([$id_inscription]);
            $message_action = "🗑️ Demande refusée et supprimée.";
        }
    }
}

// -------------------------------------------------------
// Récupérer les trajets du conducteur
// -------------------------------------------------------
$sqlTrajets = "SELECT t.*, v.modele, v.immatriculation, v.capacite_totale
               FROM trajets t
               LEFT JOIN vehicules v ON v.id_vehicule = (
                   SELECT id_vehicule FROM conducteurs WHERE id_conducteur = ?
               )
               WHERE t.id_conducteur = ?
               ORDER BY t.statut DESC, t.horaire ASC";
$stmtTrajets = $pdo->prepare($sqlTrajets);
$stmtTrajets->execute([$id_conducteur, $id_conducteur]);
$trajets = $stmtTrajets->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------------------------------
// Pour chaque trajet : passagers validés + en attente
// -------------------------------------------------------
function get_passagers_trajet($pdo, $id_trajet, $statut) {
    $sql = "SELECT e.prenom AS prenom_enfant, p.nom AS nom_parent, p.prenom AS prenom_parent,
                   p.telephone AS tel_parent, i.id_inscription, i.date_demande
            FROM inscriptions i
            JOIN enfants e ON i.id_enfant = e.id_enfant
            JOIN parents p ON e.id_parent = p.id_parent
            WHERE i.id_trajet = ? AND i.statut = ?
            ORDER BY i.date_demande ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_trajet, $statut]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer le véhicule du conducteur
$sqlVehicule = "SELECT v.* FROM vehicules v
                JOIN conducteurs c ON c.id_vehicule = v.id_vehicule
                WHERE c.id_conducteur = ?";
$stmtV = $pdo->prepare($sqlVehicule);
$stmtV->execute([$id_conducteur]);
$vehicule = $stmtV->fetch(PDO::FETCH_ASSOC);

require_once '../include/menu_conducteur.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-gauge"></i> Bonjour, <?php echo htmlspecialchars($prenom . ' ' . $nom); ?> !</h1>

    <!-- Navigation rapide conducteur -->
    <div class="nav_rapide_conducteur">
        <span class="nav_rapide_titre"><i class="fa-solid fa-bolt"></i> Accès rapide</span>
        <div class="nav_rapide_liens">
            <a href="<?php echo BASE_URL; ?>/conducteur/dashboard.php" class="nav_rapide_btn nav_rapide_actif">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/conducteur/messagerie.php" class="nav_rapide_btn">
                <i class="fa-solid fa-comments"></i>
                <span>Messagerie</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/conducteur/inscription.php" class="nav_rapide_btn">
                <i class="fa-solid fa-user-plus"></i>
                <span>Inscription</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/" class="nav_rapide_btn">
                <i class="fa-solid fa-house"></i>
                <span>Accueil</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/conducteur/logout.php" class="nav_rapide_btn nav_rapide_danger">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <?php if ($message_action !== ""): ?>
        <p class="message_succes"><?php echo $message_action; ?></p>
    <?php endif; ?>

    <!-- Infos véhicule -->
    <?php if ($vehicule): ?>
    <div class="bloc_dashboard" style="margin-bottom: 28px;">
        <h2 style="margin-bottom: 14px;"><i class="fa-solid fa-car"></i> Mon véhicule</h2>
        <div style="display: flex; gap: 32px; flex-wrap: wrap;">
            <div>
                <span class="detail_label">Modèle</span>
                <p class="detail_valeur"><?php echo htmlspecialchars($vehicule['modele']); ?></p>
            </div>
            <div>
                <span class="detail_label">Immatriculation</span>
                <p class="detail_valeur"><?php echo htmlspecialchars($vehicule['immatriculation']); ?></p>
            </div>
            <div>
                <span class="detail_label">Capacité totale</span>
                <p class="detail_valeur"><?php echo $vehicule['capacite_totale']; ?> places</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Mes trajets -->
    <h2 style="margin-bottom: 18px;"><i class="fa-solid fa-route"></i> Mes trajets</h2>

    <?php if (empty($trajets)): ?>
        <p class="info_bulle">Aucun trajet ne vous est assigné pour le moment.</p>
    <?php endif; ?>

    <?php foreach ($trajets as $trajet):
        $passagers_valides = get_passagers_trajet($pdo, $trajet['id_trajet'], 'VALIDE');
        $passagers_attente = get_passagers_trajet($pdo, $trajet['id_trajet'], 'EN_ATTENTE');
        $nb_valides        = count($passagers_valides);
        $places_max        = $trajet['places_proposees'];
    ?>
    <div class="bloc_dashboard" style="margin-bottom: 28px;">

        <!-- En-tête trajet -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
            <div>
                <h3 style="font-size: 1.1em; margin-bottom: 4px;">
                    <?php echo htmlspecialchars($trajet['point_depart']); ?>
                    <i class="fa-solid fa-arrow-right" style="color: var(--vert-clair); margin: 0 6px;"></i>
                    <?php echo htmlspecialchars($trajet['destination']); ?>
                </h3>
                <span class="detail_label">Horaire : <?php echo substr($trajet['horaire'], 0, 5); ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Jauge places -->
                <div style="text-align: center;">
                    <span class="badge <?php echo $nb_valides >= $places_max ? 'badge_orange' : 'badge_vert'; ?>">
                        <?php echo $nb_valides; ?> / <?php echo $places_max; ?> places
                    </span>
                </div>
                <span class="statut_badge <?php echo $trajet['statut'] === 'ACTIF' ? 'statut_valide' : 'statut_attente'; ?>">
                    <?php echo $trajet['statut']; ?>
                </span>
            </div>
        </div>

        <!-- Passagers validés -->
        <h4 style="margin-bottom: 10px; color: var(--vert-moyen);"><i class="fa-solid fa-check-circle"></i> Passagers confirmés</h4>
        <?php if (empty($passagers_valides)): ?>
            <p class="info_bulle" style="margin-bottom: 16px;">Aucun passager confirmé.</p>
        <?php else: ?>
        <table class="table_admin" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th>Enfant</th>
                    <th>Parent</th>
                    <th>Téléphone</th>
                    <th>Inscrit le</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passagers_valides as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['prenom_enfant']); ?></td>
                    <td><?php echo htmlspecialchars($p['prenom_parent'] . ' ' . $p['nom_parent']); ?></td>
                    <td>
                        <a href="tel:<?php echo htmlspecialchars($p['tel_parent']); ?>">
                            <?php echo htmlspecialchars($p['tel_parent']); ?>
                        </a>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($p['date_demande'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Demandes en attente -->
        <?php if (!empty($passagers_attente)): ?>
        <h4 style="margin-bottom: 10px; color: var(--orange);"><i class="fa-solid fa-clock"></i> Demandes en attente (<?php echo count($passagers_attente); ?>)</h4>
        <table class="table_admin">
            <thead>
                <tr>
                    <th>Enfant</th>
                    <th>Parent</th>
                    <th>Téléphone</th>
                    <th>Demandé le</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passagers_attente as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['prenom_enfant']); ?></td>
                    <td><?php echo htmlspecialchars($p['prenom_parent'] . ' ' . $p['nom_parent']); ?></td>
                    <td>
                        <a href="tel:<?php echo htmlspecialchars($p['tel_parent']); ?>">
                            <?php echo htmlspecialchars($p['tel_parent']); ?>
                        </a>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($p['date_demande'])); ?></td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <form method="POST">
                                <input type="hidden" name="id_inscription" value="<?php echo $p['id_inscription']; ?>">
                                <input type="hidden" name="action" value="valider">
                                <button type="submit" class="btn_valider_small"
                                    <?php echo ($nb_valides >= $places_max) ? 'disabled title="Plus de place"' : ''; ?>>
                                    <i class="fa-solid fa-check"></i> Valider
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Refuser cette demande ?');">
                                <input type="hidden" name="id_inscription" value="<?php echo $p['id_inscription']; ?>">
                                <input type="hidden" name="action" value="refuser">
                                <button type="submit" class="btn_refuser_small">
                                    <i class="fa-solid fa-xmark"></i> Refuser
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>

    <div class="retour_accueil">
        <a class="btn" href="<?php echo BASE_URL; ?>/conducteur/messagerie.php">
            <i class="fa-solid fa-comments"></i> Aller à la messagerie
        </a>
    </div>
</div>

<?php require_once '../include/footer.php'; ?>
