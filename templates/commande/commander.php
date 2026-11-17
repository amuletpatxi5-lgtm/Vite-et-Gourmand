<?php
// Champs de la prestation : nom => [libellé, type, autocomplete]
$champs = [
    'adresse' => ['Adresse de la prestation (numéro et rue)', 'text', 'street-address'],
    'code_postal' => ['Code postal', 'text', 'postal-code'],
    'ville' => ['Ville', 'text', 'address-level2'],
    'date_prestation' => ['Date de la prestation', 'date', 'off'],
    'heure_livraison' => ['Heure de livraison souhaitée', 'time', 'off'],
];
?>
<h1>Commander un menu</h1>

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

<form id="formulaire" class="formulaire" method="post" action="/commander">
    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">

    <fieldset>
        <legend>Vos coordonnées</legend>
        <p>
            <?= e($compte['prenom'] . ' ' . $compte['nom']) ?><br>
            <?= e($compte['email']) ?><br>
            <?= e($compte['telephone']) ?>
        </p>
        <p class="aide">Reprises de votre compte client.</p>
    </fieldset>

    <fieldset>
        <legend>La prestation</legend>
        <?php foreach ($champs as $nom => [$libelle, $type, $autocomplete]): ?>
            <div class="champ">
                <label for="<?= $nom ?>"><?= $libelle ?></label>
                <input type="<?= $type ?>" id="<?= $nom ?>" name="<?= $nom ?>" autocomplete="<?= $autocomplete ?>"
                       value="<?= e($valeurs[$nom]) ?>" required
                       <?= $type === 'date' ? 'min="' . date('Y-m-d', strtotime('tomorrow')) . '"' : '' ?>
                       <?= isset($erreurs[$nom]) ? 'aria-invalid="true" aria-describedby="' . $nom . '-erreur"' : '' ?>>
                <?php if (isset($erreurs[$nom])): ?>
                    <p class="erreur" id="<?= $nom ?>-erreur"><?= e($erreurs[$nom]) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <fieldset>
        <legend>Le menu</legend>
        <div class="champ">
            <label for="menu_id">Menu choisi</label>
            <select id="menu_id" name="menu_id" required
                    <?= isset($erreurs['menu_id']) ? 'aria-invalid="true" aria-describedby="menu_id-erreur"' : '' ?>>
                <option value="">Choisissez un menu</option>
                <?php foreach ($menus as $m): ?>
                    <option value="<?= (int) $m['menu_id'] ?>" <?= (string) $m['menu_id'] === $valeurs['menu_id'] ? 'selected' : '' ?>>
                        <?= e($m['titre']) ?> : <?= prix($m['prix_par_personne']) ?> par personne, <?= (int) $m['nombre_de_personne_minimum'] ?> personnes minimum
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erreurs['menu_id'])): ?>
                <p class="erreur" id="menu_id-erreur"><?= e($erreurs['menu_id']) ?></p>
            <?php endif; ?>
        </div>

        <div class="champ">
            <label for="nombre_personne">Nombre de personnes</label>
            <p class="aide" id="nombre_personne-aide">Remise de 10 % à partir de 5 personnes de plus que le minimum du menu.</p>
            <input type="number" id="nombre_personne" name="nombre_personne" min="1" max="500" required
                   value="<?= e($valeurs['nombre_personne']) ?>"
                   aria-describedby="nombre_personne-aide<?= isset($erreurs['nombre_personne']) ? ' nombre_personne-erreur' : '' ?>"
                   <?= isset($erreurs['nombre_personne']) ? 'aria-invalid="true"' : '' ?>>
            <?php if (isset($erreurs['nombre_personne'])): ?>
                <p class="erreur" id="nombre_personne-erreur"><?= e($erreurs['nombre_personne']) ?></p>
            <?php endif; ?>
        </div>
    </fieldset>

    <?php if ($recap !== null): ?>
        <section class="recap" aria-labelledby="recap-titre">
            <h2 id="recap-titre">Récapitulatif</h2>
            <dl>
                <dt><?= e($recap['menu']['titre']) ?> × <?= (int) $valeurs['nombre_personne'] ?> personnes</dt>
                <dd><?= prix($recap['prix']['menu'] / 100) ?></dd>
                <?php if ($recap['prix']['remise'] > 0): ?>
                    <dt>Remise de 10 %</dt>
                    <dd>−<?= prix($recap['prix']['remise'] / 100) ?></dd>
                <?php endif; ?>
                <dt>Livraison <?= $recap['distance']['dans_bordeaux']
                    ? '(Bordeaux)'
                    : '(' . number_format($recap['distance']['distance_km'], 1, ',', '') . ' km)' ?></dt>
                <dd><?= prix($recap['prix']['livraison'] / 100) ?></dd>
                <dt>Total</dt>
                <dd class="total"><?= prix($recap['prix']['total'] / 100) ?></dd>
            </dl>
            <p class="aide">Adresse reconnue : <?= e($recap['distance']['adresse']) ?></p>
            <input type="hidden" name="total_vu" value="<?= (int) $recap['prix']['total'] ?>">
        </section>
    <?php endif; ?>

    <button class="bouton bouton-secondaire" type="submit" name="action" value="apercu">
        <?= $recap === null ? 'Voir le récapitulatif' : 'Recalculer le prix' ?>
    </button>
    <?php if ($recap !== null): ?>
        <button class="bouton" type="submit" name="action" value="valider">Valider la commande</button>
    <?php endif; ?>
</form>
