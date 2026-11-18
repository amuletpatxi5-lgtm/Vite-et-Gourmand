// Demande de confirmation avant l'envoie d'un formulaire marqué data-confirmation
document.querySelectorAll('form[data-confirmation]').forEach((formulaire) => {
    formulaire.addEventListener('submit', (evenement) => {
        if (!window.confirm(formulaire.dataset.confirmation)) {
            evenement.preventDefault();
        }
    });
});
