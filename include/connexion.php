<?php

// verif si le user est connecte
function isConnecte() {
    return isset($_SESSION['id_parent']);
}

// verif si le user est admin
function isAdmin() {
    return isset($_SESSION['est_admin']) && $_SESSION['est_admin'] == 1;
}

// rediriger si pas connecte
function require_connexion() {
    if (!isConnecte()) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

// rediriger si pas admin
function require_admin() {
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}