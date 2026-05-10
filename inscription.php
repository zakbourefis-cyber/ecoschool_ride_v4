<?php
require_once 'config.php';
require_once 'include/header.php';
require_once 'include/fonctions.php';
require_once 'include/connexion.php';

if (isConnecte()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$erreur = "";
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $mdp = $_POST['mot_de_passe'];
    $mdp_confirm = $_POST['mot_de_passe_confirm'];

    if ($nom == "" || $prenom == "" || $email == "" || $mdp == "") {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } else if ($mdp !== $mdp_confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else if (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit faire au moins 6 caractères.";
    } else {
        $parentExistant = get_parent_by_email($pdo, $email);

        if ($parentExistant) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
            $sql = "INSERT INTO parents (nom, prenom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $prenom, $email, $telephone, $mdp_hash]);
            $succes = "Compte créé avec succès !";
        }
    }
}
?>

<?php require_once 'include/menu.php'; ?>

<div class="container">
    <h1>Inscription</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>

    <div class="bloc_dashboard">
        <?php if ($succes != ""): ?>
            <p class="message_succes"><?php echo $succes; ?> <a href="<?php echo BASE_URL; ?>/login.php">Se connecter</a></p>
        <?php else: ?>

        <form method="POST" action="">
            <label>Prénom *</label>
            <input type="text" name="prenom" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>

            <label>Nom *</label>
            <input type="text" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>

            <label>Email *</label>
            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

            <label>Téléphone</label>
            <input type="text" name="telephone" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">

            <label>Mot de passe *</label>
            <input type="password" name="mot_de_passe" required>

            <label>Confirmer le mot de passe *</label>
            <input type="password" name="mot_de_passe_confirm" required>

            <button type="submit">Créer mon compte</button>
        </form>

        <p style="margin-top: 16px;">Déjà un compte ? <a href="<?php echo BASE_URL; ?>/login.php">Se connecter</a></p>

        <?php endif; ?>
    </div>
</div>

<?php require_once 'include/footer.php'; ?>