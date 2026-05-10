<?php
require_once __DIR__ . '/connexion.php';
?>
<nav>
    <a class="nav-logo" href="<?php echo BASE_URL; ?>/index.php">
        <img src="<?php echo BASE_URL; ?>/img/logo.png" alt="EcoSchool Ride" height="38">
    </a>

    <?php if (isConnecte()): ?>
        <?php if (isAdmin()): ?>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php"><i class="fa-solid fa-gauge"></i> Tableau de bord</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/parent/trajets.php"><i class="fa-solid fa-route"></i> Trajets</a>
            <a href="<?php echo BASE_URL; ?>/parent/mes_enfants.php"><i class="fa-solid fa-children"></i> Mes enfants</a>
            <a href="<?php echo BASE_URL; ?>/parent/mes_inscriptions.php"><i class="fa-solid fa-list-check"></i> Mes inscriptions</a>
            <a href="<?php echo BASE_URL; ?>/parent_messagerie.php"><i class="fa-solid fa-comments"></i> Messagerie</a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    <?php else: ?>
        <a href="<?php echo BASE_URL; ?>/login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
        <a class="btn-nav" href="<?php echo BASE_URL; ?>/inscription.php"><i class="fa-solid fa-user-plus"></i> Inscription</a>
    <?php endif; ?>
</nav>