<h1>Contact</h1>

<?php if ($erreurs !== []): ?>
    <div class="alerte" role="alert">
        <p>Le formulaire contient <?= count($erreurs) ?> erreur<?= count($erreurs) > 1 ? 's' : '' ?> :</p>
        <ul>
            <?php foreach ($erreurs as $champ => $message): ?>
                <li><a href="#<?= e($champ) ?>"><?= e($message) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form id="formulaire" class="formulaire" method="post" action="/contact">
    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
    <p>Une question, un événement à préparer ? Écrivez-nous, nous vous répondons par email. Tous les champs sont obligatoires.</p>

    <div class="champ">
        <label for="titre">Titre de votre demande</label>
        <input type="text" id="titre" name="titre" maxlength="150" value="<?= e($valeurs['titre']) ?>" required
               <?= isset($erreurs['titre']) ? 'aria-invalid="true" aria-describedby="titre-erreur"' : '' ?>>
        <?php if (isset($erreurs['titre'])): ?>
            <p class="erreur" id="titre-erreur"><?= e($erreurs['titre']) ?></p>
        <?php endif; ?>
    </div>

    <div class="champ">
        <label for="email">Votre adresse email</label>
        <input type="email" id="email" name="email" autocomplete="email" value="<?= e($valeurs['email']) ?>" required
               <?= isset($erreurs['email']) ? 'aria-invalid="true" aria-describedby="email-erreur"' : '' ?>>
        <?php if (isset($erreurs['email'])): ?>
            <p class="erreur" id="email-erreur"><?= e($erreurs['email']) ?></p>
        <?php endif; ?>
    </div>

    <div class="champ">
        <label for="message">Votre message</label>
        <textarea id="message" name="message" rows="8" minlength="10" maxlength="3000" required
                  <?= isset($erreurs['message']) ? 'aria-invalid="true" aria-describedby="message-erreur"' : '' ?>><?= e($valeurs['message']) ?></textarea>
        <?php if (isset($erreurs['message'])): ?>
            <p class="erreur" id="message-erreur"><?= e($erreurs['message']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Pot de miel anti-robots : hors écran, ignoré au clavier et par les lecteurs d'écran -->
    <div class="pot-de-miel" aria-hidden="true">
        <label for="site_web">Ne pas remplir ce champ</label>
        <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
    </div>

    <button class="bouton" type="submit">Envoyer le message</button>
</form>
