<?php
require_once '../include/header.php';
require_once '../config.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

// faut etre connecte pour acceder
require_connexion();

$id_parent = $_SESSION['id_parent'];
$erreur = "";
$succes = "";

// ajout d un enfant
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'];

    if ($prenom == "") {
        $erreur = "Le prénom est obligatoire.";
    } else {
        $sql = "INSERT INTO enfants (prenom, date_naissance, id_parent) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$prenom, $date_naissance, $id_parent]);
        $succes = "Enfant ajouté avec succès !";
    }
}

// on recupere les enfants du parent
$liste_enfants = get_enfants_parent($pdo, $id_parent);

require_once '../include/menu.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-children"></i> Mes enfants</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>

    <?php if ($succes != ""): ?>
        <p class="message_succes"><?php echo $succes; ?></p>
    <?php endif; ?>

    <!-- liste des enfants -->
    <?php if (count($liste_enfants) == 0): ?>
        <p>Vous n'avez pas encore ajouté d'enfant.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Prénom</th>
                <th>Date de naissance</th>
                <th>Actions</th>
            </tr>
            <?php for ($i = 0; $i < count($liste_enfants); $i++): ?>
                <tr>
                    <td><?php echo htmlspecialchars($liste_enfants[$i]['prenom']); ?></td>
                    <td>
                        <?php
                        // on formate la date en francais
                        if ($liste_enfants[$i]['date_naissance'] != null) {
                            $date = $liste_enfants[$i]['date_naissance'];
                            $tableau_date = explode("-", $date);
                            echo $tableau_date[2] . "/" . $tableau_date[1] . "/" . $tableau_date[0];
                        } else {
                            echo "Non renseignée";
                        }
                        ?>
                    </td>
                    <td>
                        <a class="btn" href="mes_inscriptions.php?id_enfant=<?php echo $liste_enfants[$i]['id_enfant']; ?>"><i class="fa-solid fa-list"></i> Voir inscriptions</a>
                        <a class="btn" href="trajets.php?id_enfant=<?php echo $liste_enfants[$i]['id_enfant']; ?>"><i class="fa-solid fa-plus"></i> Inscrire à un trajet</a>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>
    <?php endif; ?>

    <!-- formulaire ajout enfant -->
    <h2><i class="fa-solid fa-plus"></i> Ajouter un enfant</h2>
    <form method="POST" action="">
        <label>Prénom *</label>
        <input type="text" name="prenom" required>

        <label>Date de naissance</label>
        <input type="date" name="date_naissance">

        <button type="submit"><i class="fa-solid fa-plus"></i> Ajouter</button>
    </form>
</div>

<?php require_once '../include/footer.php'; ?>