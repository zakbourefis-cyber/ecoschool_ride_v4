<?php
require_once 'config.php';
require_once 'include/header.php';
require_once 'include/fonctions.php';
require_once 'include/connexion.php';
require_once 'include/menu.php';
?>

<div class="container">

    <div class="hero">
        <i class="fa-solid fa-bus hero_icone"></i>
        <h1>Bienvenue sur <span class="text_vert">EcoSchool Ride</span></h1>
        <p>La solution de transport scolaire écologique et sécurisée pour vos enfants. Réservez facilement des trajets avec nos conducteurs partenaires certifiés.</p>

        <?php if (!isConnecte()): ?>
            <a href="<?php echo BASE_URL; ?>/inscription.php" class="btn"><i class="fa-solid fa-leaf"></i> Commencer maintenant</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/parent/trajets.php" class="btn"><i class="fa-solid fa-magnifying-glass"></i> Voir les trajets</a>
        <?php endif; ?>
    </div>

    <div class="cartes_features">
        <div class="carte_feature">
            <i class="fa-solid fa-shield-halved carte_icone"></i>
            <h3>100% Sécurisé</h3>
            <p>Tous nos conducteurs sont vérifiés et formés pour le transport d'enfants</p>
        </div>
        <div class="carte_feature">
            <i class="fa-solid fa-leaf carte_icone"></i>
            <h3>Écologique</h3>
            <p>Véhicules propres et covoiturage pour réduire l'empreinte carbone</p>
        </div>
        <div class="carte_feature">
            <i class="fa-solid fa-clock carte_icone"></i>
            <h3>Ponctuel</h3>
            <p>Suivi en temps réel et notifications pour une tranquillité d'esprit totale</p>
        </div>
        <div class="carte_feature">
            <i class="fa-solid fa-euro-sign carte_icone"></i>
            <h3>Économique</h3>
            <p>Tarifs compétitifs grâce à l'optimisation des trajets partagés</p>
        </div>
    </div>

    <!-- Accès espace conducteur -->
    <div class="section_conducteur">
        <div class="section_conducteur_contenu">
            <div>
                <i class="fa-solid fa-steering-wheel section_conducteur_icone"></i>
                <h2>Vous êtes conducteur ?</h2>
                <p>Accédez à votre espace dédié pour gérer vos trajets et vos passagers.</p>
            </div>
            <div class="section_conducteur_btns">
                <a href="<?php echo BASE_URL; ?>/conducteur/login.php" class="btn_conducteur_login">
                    <i class="fa-solid fa-right-to-bracket"></i> Connexion conducteur
                </a>
                <a href="<?php echo BASE_URL; ?>/conducteur/inscription.php" class="btn_conducteur_inscription">
                    <i class="fa-solid fa-user-plus"></i> Devenir conducteur
                </a>
            </div>
        </div>
    </div>

    <div class="section_chiffres">
        <h2><i class="fa-solid fa-chart-line"></i> Nos chiffres</h2>
        <div class="grille_chiffres">
            <div class="carte_chiffre">
                <div class="chiffre_icone" style="background-color: #2d6a4f;"><i class="fa-solid fa-people-group" style="color:white;"></i></div>
                <div>
                    <strong>2 500+</strong>
                    <span>Familles inscrites</span>
                </div>
            </div>
            <div class="carte_chiffre">
                <div class="chiffre_icone" style="background-color: #0077b6;"><i class="fa-solid fa-bus" style="color:white;"></i></div>
                <div>
                    <strong>150</strong>
                    <span>Trajets quotidiens</span>
                </div>
            </div>
            <div class="carte_chiffre">
                <div class="chiffre_icone" style="background-color: #f77f00;"><i class="fa-solid fa-car" style="color:white;"></i></div>
                <div>
                    <strong>45</strong>
                    <span>Conducteurs certifiés</span>
                </div>
            </div>
            <div class="carte_chiffre">
                <div class="chiffre_icone" style="background-color: #1b4332;"><i class="fa-solid fa-seedling" style="color:white;"></i></div>
                <div>
                    <strong>12T</strong>
                    <span>CO2 économisé/an</span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'include/footer.php'; ?>