<h1>Comptes employés</h1>

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

<form id="formulaire" class="formulaire" method="post" action="/admin/employes">
    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
    <h2>Créer un compte employé</h2>
    <p>L'employé recevra un mail avec son identifiant, mais pas le mot de passe : transmettez-le-lui de vive voix.</p>

    <div class="champ">
        <label for="email">Adresse email de l'employé</label>
        <input type="email" id="email" name="email" autocomplete="off" value="<?= e($email) ?>" required
               <?= isset($erreurs['email']) ? 'aria-invalid="true" aria-describedby="email-erreur"' : '' ?>>
        <?php if (isset($erreurs['email'])): ?>
            <p class="erreur" id="email-erreur"><?= e($erreurs['email']) ?></p>
        <?php endif; ?>
    </div>

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

    <button class="bouton" type="submit">Créer le compte</button>
</form>

<h2>Employés</h2>
<?php if ($employes === []): ?>
    <p>Aucun compte employé.</p>
<?php else: ?>
    <table class="stats">
        <caption>Comptes employés et leur état</caption>
        <thead>
            <tr>
                <th scope="col">Email</th>
                <th scope="col">État</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employes as $u): ?>
                <tr>
                    <th scope="row"><?= e($u['email']) ?></th>
                    <td><?= $u['actif'] ? 'Actif' : 'Désactivé' ?></td>
                    <td>
                        <form method="post" action="/admin/employes/activer"
                              <?= $u['actif'] ? 'data-confirmation="Désactiver le compte ' . e($u['email']) . ' ?"' : '' ?>>
                            <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
                            <input type="hidden" name="utilisateur_id" value="<?= (int) $u['utilisateur_id'] ?>">
                            <input type="hidden" name="actif" value="<?= $u['actif'] ? '0' : '1' ?>">
                            <button class="bouton bouton-secondaire" type="submit">
                                <?= $u['actif'] ? 'Désactiver' : 'Réactiver' ?>
                                <span class="visuellement-cache"><?= e($u['email']) ?></span>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
