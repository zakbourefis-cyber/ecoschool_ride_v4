// On stocke l'id du conducteur actuellement ouvert,
// pour que le rafraîchissement automatique sache quelle conversation recharger.
let idConducteurOuvert = null;
 
// Contiendra l'identifiant du timer une fois lancé,
// pour pouvoir l'annuler plus tard avec clearInterval
let timerRefresh = null;
 
 
function selectionnerConducteur(id, nom) {
 
    // On mémorise le conducteur ouvert (utilisé par rafraichirMessages ci-dessous)
    idConducteurOuvert = id;
 
    // On met à jour le champ caché du formulaire pour que l'envoi sache à qui envoyer
    document.getElementById('id_destinataire').value = id;
 
    // On enlève la classe CSS active sur tous les contacts, puis on la met sur celui cliqué
    // querySelectorAll retourne une NodeList, forEach permet d'itérer dessus
    document.querySelectorAll('.contact_item').forEach(function(el) {
        el.classList.remove('contact_actif');
    });
    document.querySelector('.contact_item[data-id="' + id + '"]').classList.add('contact_actif');
 
    // On affiche le header de la conversation et un texte de chargement
    document.getElementById('chat_ecran').innerHTML =
        '<div class="chat_header">Discussion avec <strong>' + nom + '</strong></div>' +
        '<div id="liste_messages" class="messages_container">Chargement...</div>';
 
    // On annule l'ancien timer avant d'en créer un nouveau,
    // sinon plusieurs timers tourneraient en parallèle
    if (timerRefresh !== null) {
        clearInterval(timerRefresh);
    }
 
    // Premier chargement immédiat, puis toutes les 5 secondes
    rafraichirMessages();
    timerRefresh = setInterval(rafraichirMessages, 5000);
}
 
 
function rafraichirMessages() {
 
    // fetch envoie une requête HTTP GET vers get_messages.php
    // C'est l'équivalent JS d'un appel réseau, il retourne une Promise
    fetch('get_messages.php?id_conducteur=' + idConducteurOuvert)
        .then(function(response) {
            // response.json() lit le corps de la réponse et le convertit en tableau JS
            return response.json();
        })
        .then(function(messages) {
 
            var container = document.getElementById('liste_messages');
 
            // Cas d'erreur renvoyé par le PHP
            if (messages.length > 0 && messages[0].message && messages[0].message.startsWith('Erreur')) {
                container.innerHTML = '<p class="message_erreur">' + messages[0].message + '</p>';
                return;
            }
 
            if (messages.length === 0) {
                container.innerHTML = '<p class="info_bulle">Aucun message. Envoyez le premier !</p>';
                return;
            }
 
            // On reconstruit tout le HTML des bulles à chaque appel
            var html = '';
            for (var i = 0; i < messages.length; i++) {
                var msg = messages[i];
 
                // Le replace remplace l'espace entre date et heure par un T
                // ex: "2024-05-01 14:30:00" → "2024-05-01T14:30:00" (format ISO standard)
                // Safari ne comprend pas le format avec espace
                var dateTexte = msg.date_envoi.replace(' ', 'T');
                var dateHeure = new Date(dateTexte).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
 
                // Si id_parent_expediteur est non nul, c'est le parent qui a envoyé → bulle verte à droite
                // Sinon c'est le conducteur → bulle blanche à gauche
                var cote = msg.id_parent_expediteur ? 'parent' : 'conducteur';
 
                html += `<div class="message_bulle ${cote}">
                    <p>${msg.message}</p>
                    <span class="date">${dateHeure}</span>
                </div>`;
            }
 
            container.innerHTML = html;
 
            // Scroll vers le bas pour voir le dernier message
            container.scrollTop = container.scrollHeight;
        })
        .catch(function(err) {
            console.error(err);
        });
}

document.querySelector('.chat_form').addEventListener('submit', function(e) {

    // Empêche le rechargement de page par défaut du formulaire
    e.preventDefault();

    var champ = document.querySelector('.chat_form input[name="message"]');
    var contenu = champ.value.trim();

    if (contenu === '' || idConducteurOuvert === null) return;

    // FormData récupère automatiquement tous les champs du formulaire
    var donnees = new FormData(this);

    fetch('send_message.php', {
        method: 'POST',
        body: donnees
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(resultat) {
        if (resultat.succes) {
            champ.value = '';
            rafraichirMessages();
        }
    });
});