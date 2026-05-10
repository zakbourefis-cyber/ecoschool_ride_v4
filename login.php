<?php
require_once 'config.php';
require_once 'include/header.php';
require_once 'include/fonctions.php';
require_once 'include/connexion.php';

// si deja connecte on redirige
if (isConnecte()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $mdp = $_POST['mot_de_passe'];

    if ($email == "" || $mdp == "") {
        $erreur = "Veuillez remplir tous les champs.";
    } else {

        $parent = get_parent_by_email($pdo, $email);

        if ($parent && password_verify($mdp, $parent['mot_de_passe'])) {

            // on stocke les infos dans la session
            $_SESSION['id_parent'] = $parent['id_parent'];
            $_SESSION['nom'] = $parent['nom'];
            $_SESSION['prenom'] = $parent['prenom'];
            $_SESSION['est_admin'] = $parent['est_admin'];

            if ($parent['est_admin'] == 1) {
                header("Location: " . BASE_URL . "/admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "/index.php");
            }
            exit();

        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<?php require_once 'include/menu.php'; ?>

<div class="container">
    <h1>Connexion</h1>

    <?php if ($erreur != ""): ?>
        <p class="message_erreur"><?php echo $erreur; ?></p>
    <?php endif; ?>

    <div class="bloc_dashboard">
        <form method="POST" action="">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" required>

            <button type="submit">Se connecter</button>
        </form>

        <p style="margin-top: 16px;">Pas encore de compte ? <a href="<?php echo BASE_URL; ?>/inscription.php">S'inscrire</a></p>
    </div>
</div>

<?php require_once 'include/footer.php'; ?>