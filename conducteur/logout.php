<?php
session_start();
unset($_SESSION['id_conducteur']);
unset($_SESSION['nom_conducteur']);
unset($_SESSION['prenom_conducteur']);

require_once '../config.php';
header("Location: " . BASE_URL . "/conducteur/login.php");
exit();
