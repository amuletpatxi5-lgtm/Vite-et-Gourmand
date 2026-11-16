// Sans JavaScript, le formulaire fonctionne quand même : il recharge /menus avec les critères.
const formulaire = document.getElementById('filtres');
const resultats = document.getElementById('resultats');
const nombre = document.getElementById('nombre');
let minuterie;

async function filtrer() {
    const criteres = new URLSearchParams();
    for (const [cle, valeur] of new FormData(formulaire)) {
        if (valeur !== '') {
            criteres.append(cle, valeur);
        }
    }
    const requete = criteres.toString();

    try {
        const reponse = await fetch('/menus/filtrer?' + requete);
        if (!reponse.ok) {
            throw new Error('HTTP ' + reponse.status);
        }
        resultats.innerHTML = await reponse.text();
        const n = resultats.querySelectorAll('.carte').length;
        nombre.textContent = n + (n > 1 ? ' menus' : ' menu');
        history.replaceState(null, '', requete ? '/menus?' + requete : '/menus');
    } catch (erreur) {
        nombre.textContent = 'Le filtrage a échoué. Merci de réessayer.';
    }
}

formulaire.addEventListener('submit', (evenement) => {
    evenement.preventDefault();
    filtrer();
});

formulaire.addEventListener('input', () => {
    clearTimeout(minuterie);
    minuterie = setTimeout(filtrer, 300);
});
