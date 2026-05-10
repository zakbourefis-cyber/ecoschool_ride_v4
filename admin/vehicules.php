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

    $modele = trim($_POST['modele']);
    $immatriculation = trim($_POST['immatriculation']);
    $capacite = $_POST['capacite_totale'];

    if ($modele == "" || $capacite == "") {
        $erreur = "Le modèle et la capacité sont obligatoires.";
    } else if ($capacite <= 0) {
        $erreur = "La capacité doit être supérieure à 0.";
    } else {
        $immat_val = ($immatriculation == "") ? null : $immatriculation;

        $sql = "INSERT INTO vehicules (modele, immatriculation, capacite_totale) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$modele, $immat_val, $capacite]);
        $succes = "Véhicule ajouté avec succès !";
    }
}

$liste_vehicules = get_all_vehicules($pdo);

require_once '../include/menu.php';
?>

<div class="container">
    <h1>🚗 Gestion des véhicules</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>
    <?php if ($succes != ""): ?>
        <p class="message_succes"><?php echo $succes; ?></p>
    <?php endif; ?>

    <!-- liste vehicules -->
    <div class="bloc_dashboard">
        <h2>Liste des véhicules</h2>

        <?php if (count($liste_vehicules) == 0): ?>
            <p>Aucun véhicule enregistré.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Modèle</th>
                    <th>Immatriculation</th>
                    <th>Capacité</th>
                </tr>
                <?php for ($i = 0; $i < count($liste_vehicules); $i++): ?>
                    <?php $v = $liste_vehicules[$i]; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($v['modele']); ?></td>
                        <td><?php echo htmlspecialchars($v['immatriculation'] ?? "—"); ?></td>
                        <td><?php echo $v['capacite_totale']; ?> places</td>
                    </tr>
                <?php endfor; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- formulaire ajout vehicule -->
    <div class="bloc_dashboard">
        <h2>Ajouter un véhicule</h2>
        <form method="POST" action="">
            <label>Modèle *</label>
            <input type="text" name="modele" placeholder="ex: Renault Kangoo" required>

            <label>Immatriculation</label>
            <input type="text" name="immatriculation" placeholder="ex: AB-123-CD">

            <label>Capacité totale *</label>
            <input type="number" name="capacite_totale" min="1" max="20" required>

            <button type="submit">Ajouter le véhicule</button>
        </form>
    </div>

    <a class="btn" href="dashboard.php">← Retour au tableau de bord</a>
</div>

<?php require_once '../include/footer.php'; ?>
