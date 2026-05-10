<?php
require_once '../config.php';
require_once '../include/header.php';
require_once '../include/fonctions.php';
require_once '../include/connexion.php';

// Si déjà connecté en tant que conducteur, rediriger vers le tableau de bord
if (isset($_SESSION['id_conducteur'])) {
    header("Location: " . BASE_URL . "/conducteur/dashboard.php");
    exit();
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des entrées
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // 1. On cherche le conducteur uniquement par son email
        $sql = "SELECT * FROM conducteurs WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $conducteur = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Vérification du mot de passe avec password_verify
        if ($conducteur && password_verify($password, $conducteur['mot_de_passe'])) {
            // Création de la session
            $_SESSION['id_conducteur'] = $conducteur['id_conducteur'];
            $_SESSION['nom_conducteur'] = $conducteur['nom'];
            $_SESSION['prenom_conducteur'] = $conducteur['prenom'];
            
            // Redirection
            header("Location: " . BASE_URL . "/conducteur/dashboard.php");
            exit();
        } else {
            // Message générique pour la sécurité
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<?php require_once '../include/menu.php'; ?>

<div class="container">
    <h1><i class="fa-solid fa-steering-wheel"></i> Espace Conducteur</h1>

    <?php if ($erreur !== ""): ?>
        <p class="message_erreur" style="color: red; background: #fee; padding: 10px; border-radius: 5px; text-align: center;">
            <?php echo $erreur; ?>
        </p>
    <?php endif; ?>

    <div class="bloc_dashboard" style="max-width: 460px; margin: 0 auto;">
        <p class="detail_label" style="margin-bottom: 20px;">
            Identifiez-vous pour gérer vos trajets et vos passagers.
        </p>
        
        <form method="POST" action="">
            <label>Email professionnel / personnel</label>
            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="votre@email.fr" required>

            <label style="margin-top: 15px; display: block;">Mot de passe</label>
            <input type="password" name="password" placeholder="Votre mot de passe" required>

            <button type="submit" style="width: 100%; margin-top: 20px;">
                <i class="fa-solid fa-right-to-bracket"></i> Se connecter
            </button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 0.9em;">
            Pas encore de compte ? <a href="inscription.php">Inscrivez-vous ici</a>
        </p>
    </div>
</div>

<?php require_once '../include/footer.php'; ?>