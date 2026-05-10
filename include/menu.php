<header style="display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
    <a href="<?php echo BASE_URL; ?>/index.php" style="text-decoration: none; color: #2d6a4f; font-size: 1.4em; font-weight: 800; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-bus-simple"></i> EcoSchool Ride
    </a>
    
    <nav style="display: flex; gap: 20px; align-items: center;">
        
        <?php if(isset($_SESSION['id_conducteur'])): ?>
            <span style="font-size: 0.9em; color: #666;">Conducteur : <strong><?= htmlspecialchars($_SESSION['prenom_conducteur']) ?></strong></span>
            <a href="<?= BASE_URL ?>/conducteur/dashboard.php" style="text-decoration: none; color: #333; font-weight: 500;">Dashboard</a>
            <a href="<?= BASE_URL ?>/conducteur/messagerie.php" style="text-decoration: none; color: #333; font-weight: 500;">Messagerie</a>
            <a href="<?= BASE_URL ?>/conducteur/logout.php" style="color: #d90429; font-weight: bold; text-decoration: none; padding: 6px 12px; border: 1px solid #d90429; border-radius: 4px;">Déconnexion</a>

        <?php elseif(isset($_SESSION['id_parent'])): ?>
            <span style="font-size: 0.9em; color: #666;">Bonjour, <strong><?= htmlspecialchars($_SESSION['prenom']) ?></strong></span>
            
            <div style="display: flex; gap: 18px; border-right: 1px solid #eee; padding-right: 20px;">
                <a href="<?= BASE_URL ?>/parent/trajets.php" style="text-decoration: none; color: #333; font-weight: 500;">Trajets</a>
                <a href="<?= BASE_URL ?>/parent/mes_enfants.php" style="text-decoration: none; color: #333; font-weight: 500;">Enfants</a>
                <a href="<?= BASE_URL ?>/parent/mes_inscriptions.php" style="text-decoration: none; color: #333; font-weight: 500;">Suivi</a>
                <a href="<?= BASE_URL ?>/parent_messagerie.php" style="text-decoration: none; color: #2d6a4f; font-weight: 600;">Messagerie</a>
            </div>

            <?php if(isset($_SESSION['est_admin']) && $_SESSION['est_admin'] == 1): ?>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" style="text-decoration: none; background: #f77f00; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.85em; font-weight: bold;">ADMIN</a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>/logout.php" style="color: #d90429; font-weight: bold; text-decoration: none; margin-left: 10px;">Se déconnecter</a>

        <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" style="text-decoration: none; color: #333; font-weight: 500;">Connexion</a>
            <a href="<?= BASE_URL ?>/inscription.php" style="text-decoration: none; background: #2d6a4f; color: white; padding: 8px 18px; border-radius: 6px; font-weight: bold;">S'inscrire</a>
        <?php endif; ?>
        
    </nav>
</header>