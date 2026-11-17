<?php

$champs = [
    'nom' => ['Nom', 'text', 'family-name'],
    'prenom' => ['Prénom', 'text', 'given-name'],
    'email' => ['Adresse email', 'email', 'email'],
    'telephone' => ['Téléphone portable', 'tel', 'tel'],
    'adresse_postale' => ['Adresse (numéro et rue)', 'text', 'street-address'],
    'code_postal' => ['Code postal', 'text', 'postal-code'],
    'ville' => ['Ville', 'text', 'address-level2'],
    'pays' => ['Pays', 'text', 'country-name'],
];
?>
<h1>Créer un compte</h1>

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

<form id="formulaire" class="formulaire" method="post" action="/inscription">
    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
    <p>Tous les champs sont obligatoires.</p>

    <?php foreach ($champs as $nom => [$libelle, $type, $autocomplete]): ?>
        <div class="champ">
            <label for="<?= $nom ?>"><?= $libelle ?></label>
            <input type="<?= $type ?>" id="<?= $nom ?>" name="<?= $nom ?>" autocomplete="<?= $autocomplete ?>"
                   value="<?= e($valeurs[$nom]) ?>" required
                   <?= isset($erreurs[$nom]) ? 'aria-invalid="true" aria-describedby="' . $nom . '-erreur"' : '' ?>>
            <?php if (isset($erreurs[$nom])): ?>
                <p class="erreur" id="<?= $nom ?>-erreur"><?= e($erreurs[$nom]) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

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

    <button class="bouton" type="submit">Créer mon compte</button>
</form>
