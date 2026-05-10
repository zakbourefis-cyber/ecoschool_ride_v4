<?php
require_once '../include/header.php';
require_once '../config.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

require_connexion();
require_admin();

$erreur = "";
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $point_depart = trim($_POST['point_depart']);
    $destination = trim($_POST['destination']);
    $horaire = $_POST['horaire'];
    $places_proposees = $_POST['places_proposees'];
    $id_conducteur = $_POST['id_conducteur'];

    if ($point_depart == "" || $destination == "" || $horaire == "" || $id_conducteur == "") {
        $erreur = "Tous les champs sont obligatoires.";
    } else if ($places_proposees <= 0) {
        $erreur = "Le nombre de places doit être supérieur à 0.";
    } else {
        $sql = "INSERT INTO trajets (point_depart, destination, horaire, places_proposees, id_conducteur) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$point_depart, $destination, $horaire, $places_proposees, $id_conducteur]);

        // on recupere l id du trajet qu on vient de creer
        $id_nouveau_trajet = $pdo->lastInsertId();

        // on essaie d assigner automatiquement les enfants en attente sur le meme itineraire
        $nb_assignes = auto_assigner_attente($pdo, $id_nouveau_trajet);

        if ($nb_assignes > 0) {
            $succes = "Trajet créé ! " . $nb_assignes . " enfant(s) en attente ont été automatiquement assignés à ce trajet.";
        } else {
            $succes = "Trajet créé avec succès !";
        }
    }
}

$liste_conducteurs = get_all_conducteurs($pdo);
$tous_trajets = get_all_trajets($pdo);

require_once '../include/menu.php';
?>

<div class="container">
    <h1>🗺️ Gestion des trajets</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>
    <?php if ($succes != ""): ?>
        <p class="message_succes"><?php echo $succes; ?></p>
    <?php endif; ?>

    <!-- liste trajets -->
    <div class="bloc_dashboard">
        <h2>Trajets existants</h2>

        <?php if (count($tous_trajets) == 0): ?>
            <p>Aucun trajet créé.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Départ</th>
                    <th>Destination</th>
                    <th>Horaire</th>
                    <th>Places</th>
                    <th>Conducteur</th>
                </tr>
                <?php for ($i = 0; $i < count($tous_trajets); $i++): ?>
                    <?php
                    $t = $tous_trajets[$i];
                    $places_prises = get_places_prises($pdo, $t['id_trajet']);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['point_depart']); ?></td>
                        <td><?php echo htmlspecialchars($t['destination']); ?></td>
                        <td><?php echo $t['horaire']; ?></td>
                        <td><?php echo $places_prises . "/" . $t['places_proposees']; ?></td>
                        <td><?php echo htmlspecialchars($t['prenom'] . " " . $t['nom']); ?></td>
                    </tr>
                <?php endfor; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- formulaire creation trajet -->
    <div class="bloc_dashboard">
        <h2>Créer un trajet</h2>

        <?php if (count($liste_conducteurs) == 0): ?>
            <p>Vous devez d'abord <a href="conducteurs.php">ajouter un conducteur</a> avant de créer un trajet.</p>
        <?php else: ?>

        <form method="POST" action="">
            <label>Point de départ *</label>
            <input type="text" name="point_depart" placeholder="ex: Quartier Nord" required>

            <label>Destination *</label>
            <input type="text" name="destination" placeholder="ex: École Jules Verne" required>

            <label>Horaire *</label>
            <input type="time" name="horaire" required>

            <label>Nombre de places proposées *</label>
            <input type="number" name="places_proposees" min="1" max="20" required>

            <label>Conducteur *</label>
            <select name="id_conducteur" required>
                <option value="">-- Choisir un conducteur --</option>
                <?php for ($i = 0; $i < count($liste_conducteurs); $i++): ?>
                    <?php $c = $liste_conducteurs[$i]; ?>
                    <option value="<?php echo $c['id_conducteur']; ?>">
                        <?php echo htmlspecialchars($c['prenom'] . " " . $c['nom']); ?>
                        <?php if ($c['modele'] != null): ?>
                            - <?php echo htmlspecialchars($c['modele']); ?> (<?php echo $c['capacite_totale']; ?> places)
                        <?php endif; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit">Créer le trajet</button>
        </form>

        <?php endif; ?>
    </div>

    <a class="btn" href="dashboard.php">← Retour au tableau de bord</a>
</div>

<?php require_once '../include/footer.php'; ?>