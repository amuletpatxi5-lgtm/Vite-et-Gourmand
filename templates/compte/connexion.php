<h1>Connexion</h1>

<?php if ($erreur !== null): ?>
    <div class="alerte" role="alert">
        <p id="erreur-connexion"><?= e($erreur) ?></p>
    </div>
<?php endif; ?>

<form class="formulaire" method="post" action="/connexion">
    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">

    <div class="champ">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" autocomplete="username" value="<?=e($email)?>" required
               <?=$erreur !== null ? 'aria-invalid="true" aria-describedby="erreur-connexion"' : ''?>>
    </div>

    <div class="champ">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="current-password" required>
    </div>

    <button class="bouton" type="submit">Se connecter</button>
</form>

<p><a href="/mot-de-passe-oublie">Mot de passe oublié?</a></p>

<p>Pas encore de compte ? <a href="/inscription">Créer un compte</a></p>
