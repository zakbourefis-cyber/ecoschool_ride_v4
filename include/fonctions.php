<?php

// recuperer tous les trajets
function get_all_trajets($pdo) {
    $sql = "SELECT t.*, c.nom, c.prenom, v.capacite_totale
            FROM trajets t
            JOIN conducteurs c ON t.id_conducteur = c.id_conducteur
            JOIN vehicules v ON c.id_vehicule = v.id_vehicule";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// compter les places prises sur un trajet
function get_places_prises($pdo, $id_trajet) {
    $sql = "SELECT COUNT(*) FROM inscriptions WHERE id_trajet = ? AND statut = 'VALIDE'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_trajet]);
    return $stmt->fetchColumn();
}

// recuperer les enfants d'un parent
function get_enfants_parent($pdo, $id_parent) {
    $sql = "SELECT * FROM enfants WHERE id_parent = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_parent]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// recuperer les inscriptions d'un enfant avec tous les details
function get_inscriptions_enfant($pdo, $id_enfant) {
    $sql = "SELECT i.*,
                   t.point_depart, t.destination, t.horaire, t.places_proposees,
                   c.nom AS conducteur_nom, c.prenom AS conducteur_prenom, c.telephone AS conducteur_tel,
                   v.modele AS vehicule_modele, v.capacite_totale
            FROM inscriptions i
            JOIN trajets t ON i.id_trajet = t.id_trajet
            JOIN conducteurs c ON t.id_conducteur = c.id_conducteur
            LEFT JOIN vehicules v ON c.id_vehicule = v.id_vehicule
            WHERE i.id_enfant = ?
            ORDER BY i.date_demande DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_enfant]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// liberer une place quand un enfant se desinscrit
// et passer le premier en attente sur ce trajet en VALIDE automatiquement
function desinscrire_enfant($pdo, $id_inscription, $id_parent) {

    // verif que l inscription appartient bien a un enfant du parent connecte
    $sqlCheck = "SELECT i.id_trajet FROM inscriptions i
                 JOIN enfants e ON i.id_enfant = e.id_enfant
                 WHERE i.id_inscription = ? AND e.id_parent = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$id_inscription, $id_parent]);
    $inscription = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$inscription) {
        return false;
    }

    $id_trajet = $inscription['id_trajet'];

    // on supprime l inscription
    $sqlDel = "DELETE FROM inscriptions WHERE id_inscription = ?";
    $stmtDel = $pdo->prepare($sqlDel);
    $stmtDel->execute([$id_inscription]);

    // on regarde si y a quelqu un en attente sur ce trajet
    // on prend le premier par date de demande
    $sqlAttente = "SELECT id_inscription FROM inscriptions
                   WHERE id_trajet = ? AND statut = 'EN_ATTENTE'
                   ORDER BY date_demande ASC
                   LIMIT 1";
    $stmtAttente = $pdo->prepare($sqlAttente);
    $stmtAttente->execute([$id_trajet]);
    $premierAttente = $stmtAttente->fetch(PDO::FETCH_ASSOC);

    if ($premierAttente) {
        // on valide le premier en attente
        $sqlValide = "UPDATE inscriptions SET statut = 'VALIDE' WHERE id_inscription = ?";
        $stmtValide = $pdo->prepare($sqlValide);
        $stmtValide->execute([$premierAttente['id_inscription']]);
        return "libere";
    }

    return true;
}

// recuperer un parent par son email
function get_parent_by_email($pdo, $email) {
    $sql = "SELECT * FROM parents WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// recuperer tous les conducteurs
function get_all_conducteurs($pdo) {
    $sql = "SELECT c.*, v.modele, v.capacite_totale
            FROM conducteurs c
            LEFT JOIN vehicules v ON c.id_vehicule = v.id_vehicule";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// recuperer tous les vehicules
function get_all_vehicules($pdo) {
    $sql = "SELECT * FROM vehicules";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// recuperer les demandes en attente
function get_demandes_attente($pdo) {
    $sql = "SELECT i.*, e.prenom AS prenom_enfant, p.nom AS nom_parent, p.prenom AS prenom_parent,
                   t.point_depart, t.destination, t.horaire
            FROM inscriptions i
            JOIN enfants e ON i.id_enfant = e.id_enfant
            JOIN parents p ON e.id_parent = p.id_parent
            JOIN trajets t ON i.id_trajet = t.id_trajet
            WHERE i.statut = 'EN_ATTENTE'
            ORDER BY i.date_demande ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// recuperer un trajet par son id
function get_trajet_by_id($pdo, $id_trajet) {
    $sql = "SELECT t.*, c.nom, c.prenom, v.capacite_totale
            FROM trajets t
            JOIN conducteurs c ON t.id_conducteur = c.id_conducteur
            JOIN vehicules v ON c.id_vehicule = v.id_vehicule
            WHERE t.id_trajet = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_trajet]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// auto assigner les enfants en attente sur un nouveau trajet du meme itineraire
// on prend les enfants EN_ATTENTE par ordre de date_demande (premier arrive premier servi)
// et on les bascule sur le nouveau trajet si y a de la place
function auto_assigner_attente($pdo, $id_trajet_nouveau) {

    // on recupere les infos du nouveau trajet
    $nouveau_trajet = get_trajet_by_id($pdo, $id_trajet_nouveau);

    if ($nouveau_trajet == null) {
        return 0;
    }

    $point_depart   = $nouveau_trajet['point_depart'];
    $destination    = $nouveau_trajet['destination'];
    $places_max     = $nouveau_trajet['places_proposees'];

    // on cherche tous les trajets complets avec le meme itineraire (sauf le nouveau)
    $sql_trajets_complets = "SELECT t.id_trajet FROM trajets t
                             WHERE t.point_depart = ?
                             AND t.destination = ?
                             AND t.id_trajet != ?";
    $stmt = $pdo->prepare($sql_trajets_complets);
    $stmt->execute([$point_depart, $destination, $id_trajet_nouveau]);
    $liste_trajets_meme_route = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($liste_trajets_meme_route) == 0) {
        return 0;
    }

    // on construit la liste des id_trajet pour le IN (...)
    $ids_trajets = [];
    for ($i = 0; $i < count($liste_trajets_meme_route); $i++) {
        $ids_trajets[] = $liste_trajets_meme_route[$i]['id_trajet'];
    }

    // on recupere les enfants EN_ATTENTE sur ces trajets, par ordre de date
    // on utilise des ? pour le IN
    $placeholders = implode(',', array_fill(0, count($ids_trajets), '?'));
    $sql_attente = "SELECT i.id_inscription, i.id_enfant
                    FROM inscriptions i
                    WHERE i.id_trajet IN ($placeholders)
                    AND i.statut = 'EN_ATTENTE'
                    ORDER BY i.date_demande ASC";
    $stmt2 = $pdo->prepare($sql_attente);
    $stmt2->execute($ids_trajets);
    $liste_attente = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // on assigne un par un tant qu'il reste de la place
    $nb_assignes = 0;

    for ($j = 0; $j < count($liste_attente); $j++) {

        // on recompte les places a chaque fois car ca change
        $places_prises = get_places_prises($pdo, $id_trajet_nouveau);

        if ($places_prises >= $places_max) {
            // plus de place, on arrete
            break;
        }

        $id_inscription = $liste_attente[$j]['id_inscription'];

        // on bascule l inscription sur le nouveau trajet et on valide
        $sql_update = "UPDATE inscriptions
                       SET id_trajet = ?, statut = 'VALIDE'
                       WHERE id_inscription = ?";
        $stmt3 = $pdo->prepare($sql_update);
        $stmt3->execute([$id_trajet_nouveau, $id_inscription]);

        $nb_assignes++;
    }

    // on retourne le nb d enfants assignes automatiquement
    return $nb_assignes;
}