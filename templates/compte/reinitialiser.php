<?php if ($jeton === null): ?>
    <h1>Lien expiré</h1>
    <p>Ce lien de réinitialisation n'est plus valable : il a déjà servi, ou il a dépassé sa durée de validité d'une heure.</p>
    <p><a class="bouton" href="/mot-de-passe-oublie">Faire une nouvelle demande</a></p>
<?php else: ?>
    <h1>Nouveau mot de passe</h1>

    <?php if ($erreurs !== []): ?>
        <div class="alerte" role="alert">
            <p>Le formulaire contient <?= count($erreurs) ?> erreur<?= count($erreurs) > 1 ? 's' : '' ?> : </p>
            <ul>
                <?php foreach ($erreurs as $champ => $message): ?>
                    <li><a href="#<?= e($champ) ?>"><?= e($message) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="formulaire" class="formulaire" method="post" action="/reinitialiser">
        <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
        <input type="hidden" name="jeton" value="<?= e($jeton) ?>">

    <div class="champ">
        <label for="mot_de_passe">Mot de passe</label>
        <p class="aide" id="mot_de_passe-aide">10 caractères minimum, dont une majuscule, une minuscule, un chiffre et un caractère spécial.</p>
        <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="new-password" required minlength="10"
               aria-describedby="mot_de_passe-aide<?= isset($erreurs['mot_de_passe']) ? ' mot_de_passe-erreur' : '' ?>"
               <?= isset($erreurs['mot_de_passe']) ? 'aria-invalid="true"' : '' ?>>
        <?php if (isset($erreurs['mot_de_passe'])): ?>
            <p class="erreur" id="mot_de_passe-erreur"><?= e($erreurs['mot_de_passe']) ?></p>
        <?php endif; ?>
    </div>

    <div class="champ">
        <label for="confirmation">Confirmation du mot de passe</label>
        <input type="password" id="confirmation" name="confirmation" autocomplete="new-password" required
               <?= isset($erreurs['confirmation']) ? 'aria-invalid="true" aria-describedby="confirmation-erreur"' : '' ?>>
        <?php if (isset($erreurs['confirmation'])): ?>
            <p class="erreur" id="confirmation-erreur"><?= e($erreurs['confirmation']) ?></p>
        <?php endif; ?>
    </div>

        <button class="bouton" type="submit">Enregistrer le mot de passe</button>
    </form>
<?php endif; ?>
