<h1>Menus et horaires</h1>
<p>Le contenu des menus (plats, allergènes, descriptions, images) se modifie pour l'instant dans la base de données :
   son édition depuis cette page est une évolution prévue.</p>

<h2>Menus</h2>
<?php foreach ($menus as $m): ?>
    <article class="ma-commande" aria-labelledby="menu-<?= (int) $m['menu_id'] ?>">
        <h3 id="menu-<?= (int) $m['menu_id'] ?>">
            <?= e($m['titre']) ?>
            <?php if (!$m['actif']): ?><span class="numero">Retiré de la vente</span><?php endif; ?>
        </h3>
        <form class="formulaire" method="post" action="/gestion/menus/modifier">
            <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
            <input type="hidden" name="menu_id" value="<?= (int) $m['menu_id'] ?>">
            <div class="champ">
                <label for="prix-<?= (int) $m['menu_id'] ?>">Prix par personne (€)</label>
                <input type="text" inputmode="decimal" id="prix-<?= (int) $m['menu_id'] ?>" name="prix_par_personne"
                       value="<?= e($m['prix_par_personne']) ?>" pattern="\d{1,4}([.,]\d{1,2})?" required>
            </div>
            <div class="champ">
                <label for="stock-<?= (int) $m['menu_id'] ?>">Commandes encore possibles (stock)</label>
                <input type="number" id="stock-<?= (int) $m['menu_id'] ?>" name="quantite_restante" min="0" max="9999"
                       value="<?= (int) $m['quantite_restante'] ?>" required>
            </div>
            <div class="champ">
                <label class="choix"><input type="checkbox" name="actif" value="1" <?= $m['actif'] ? 'checked' : '' ?>> En vente sur le site</label>
            </div>
            <button class="bouton" type="submit">Enregistrer <span class="visuellement-cache"><?= e($m['titre']) ?></span></button>
        </form>
    </article>
<?php endforeach; ?>

<h2 id="horaires">Horaires d'ouverture</h2>
<form class="formulaire" method="post" action="/gestion/horaires">
    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
    <p>Affichés en bas de chaque page. Laissez les deux heures vides pour un jour de fermeture.</p>
    <?php foreach ($horaires as $h): ?>
        <fieldset>
            <legend><?= e(ucfirst($h['jour'])) ?></legend>
            <div class="champ">
                <label for="ouverture_<?= e($h['jour']) ?>">Ouverture</label>
                <input type="time" id="ouverture_<?= e($h['jour']) ?>" name="ouverture_<?= e($h['jour']) ?>"
                       value="<?= e(substr((string) $h['heure_ouverture'], 0, 5)) ?>">
            </div>
            <div class="champ">
                <label for="fermeture_<?= e($h['jour']) ?>">Fermeture</label>
                <input type="time" id="fermeture_<?= e($h['jour']) ?>" name="fermeture_<?= e($h['jour']) ?>"
                       value="<?= e(substr((string) $h['heure_fermeture'], 0, 5)) ?>">
            </div>
        </fieldset>
    <?php endforeach; ?>
    <button class="bouton" type="submit">Enregistrer les horaires</button>
</form>
