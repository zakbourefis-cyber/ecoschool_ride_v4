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

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $id_vehicule = $_POST['id_vehicule'];

    if ($nom == "" || $prenom == "" || $telephone == "") {
        $erreur = "Les champs nom, prénom et téléphone sont obligatoires.";
    } else {
        // si pas de vehicule selectionne on met null
        $vehicule_val = ($id_vehicule == "") ? null : $id_vehicule;

        $sql = "INSERT INTO conducteurs (nom, prenom, telephone, email, id_vehicule) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prenom, $telephone, $email, $vehicule_val]);
        $succes = "Conducteur ajouté avec succès !";
    }
}

$liste_conducteurs = get_all_conducteurs($pdo);
$liste_vehicules = get_all_vehicules($pdo);

require_once '../include/menu.php';
?>

<div class="container">
    <h1>👤 Gestion des conducteurs</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>
    <?php if ($succes != ""): ?>
        <p class="message_succes"><?php echo $succes; ?></p>
    <?php endif; ?>

    <!-- liste conducteurs -->
    <div class="bloc_dashboard">
        <h2>Liste des conducteurs</h2>

        <?php if (count($liste_conducteurs) == 0): ?>
            <p>Aucun conducteur enregistré.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Véhicule</th>
                </tr>
                <?php for ($i = 0; $i < count($liste_conducteurs); $i++): ?>
                    <?php $c = $liste_conducteurs[$i]; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['nom']); ?></td>
                        <td><?php echo htmlspecialchars($c['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($c['telephone']); ?></td>
                        <td><?php echo htmlspecialchars($c['email'] ?? "—"); ?></td>
                        <td>
                            <?php if ($c['modele'] != null): ?>
                                <?php echo htmlspecialchars($c['modele']); ?> (<?php echo $c['capacite_totale']; ?> places)
                            <?php else: ?>
                                <span class="badge_orange">Aucun véhicule</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- formulaire ajout conducteur -->
    <div class="bloc_dashboard">
        <h2>Ajouter un conducteur</h2>
        <form method="POST" action="">
            <label>Nom *</label>
            <input type="text" name="nom" required>

            <label>Prénom *</label>
            <input type="text" name="prenom" required>

            <label>Téléphone *</label>
            <input type="text" name="telephone" required>

            <label>Email</label>
            <input type="email" name="email">

            <label>Véhicule associé</label>
            <select name="id_vehicule">
                <option value="">-- Aucun véhicule --</option>
                <?php for ($i = 0; $i < count($liste_vehicules); $i++): ?>
                    <option value="<?php echo $liste_vehicules[$i]['id_vehicule']; ?>">
                        <?php echo htmlspecialchars($liste_vehicules[$i]['modele']); ?> - <?php echo $liste_vehicules[$i]['immatriculation']; ?> (<?php echo $liste_vehicules[$i]['capacite_totale']; ?> places)
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit">Ajouter le conducteur</button>
        </form>
    </div>

    <a class="btn" href="dashboard.php">← Retour au tableau de bord</a>
</div>

<?php require_once '../include/footer.php'; ?>
