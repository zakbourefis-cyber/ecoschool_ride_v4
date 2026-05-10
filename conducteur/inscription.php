<?php
require_once '../config.php';
require_once '../include/header.php';
require_once '../include/connexion.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = trim($_POST['nom']);
    $prenom    = trim($_POST['prenom']);
    $email     = trim($_POST['email']);
    $tel       = trim($_POST['telephone']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id_conducteur FROM conducteurs WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        $message = "<p class='message_erreur'>Cet email est déjà utilisé.</p>";
    } else {
        $sql = "INSERT INTO conducteurs (nom, prenom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nom, $prenom, $email, $tel, $password])) {
            $message = "<p class='message_succes'>Inscription réussie ! <a href='login.php'>Connectez-vous ici</a></p>";
        }
    }
}
?>

<div class="container">
    <h1><i class="fa-solid fa-user-plus"></i> Inscription Conducteur</h1>
    <?php echo $message; ?>

    <div class="bloc_dashboard" style="max-width: 500px; margin: 0 auto;">
        <form method="POST">
            <label>Prénom</label>
            <input type="text" name="prenom" required>
            
            <label>Nom</label>
            <input type="text" name="nom" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Téléphone</label>
            <input type="tel" name="telephone" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <button type="submit">Créer mon compte conducteur</button>
        </form>
        <p style="margin-top: 15px; font-size: 0.9em;">
            Déjà inscrit ? <a href="login.php">Se connecter</a>
        </p>
    </div>
</div>
<?php require_once '../include/footer.php'; ?>