<h1>Mot de passe oublié</h1>

<?php if ($erreur !== null): ?>
    <div class="alerte" role="alert">
        <p><?= e($erreur) ?></p>
    </div>
<?php endif; ?>

<form class="formulaire" method="post" action="/mot-de-passe-oublie">
    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
    <p>Indiquez l'adresse email de votre compte : nous vous enverrons un lien pour choisir un nouveau mot de passe</p>

    <div class="champ">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" autocomplete="email" value="<?= e($email) ?>" required>
    </div>

    <button class="bouton" type="submit">Recevoir le lien</button>
</form>

<p><a href="/connexion">Retour à la connexion</a></p>
