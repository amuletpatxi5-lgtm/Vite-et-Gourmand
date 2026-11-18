<h1>Statistiques des commandes</h1>
<p>Nombre de commandes et chiffre d'affaires par menu, calculés dans la base NoSQL (MongoDB).
   Les commandes annulées n'y figurent pas.</p>

<form class="filtres" method="get" action="/admin/statistiques">
    <div>
        <label for="menu">Menu</label>
        <select id="menu" name="menu">
            <option value="">Tous les menus</option>
            <?php foreach ($menus as $m): ?>
                <option value="<?= (int) $m['menu_id'] ?>" <?= (int) $m['menu_id'] === $filtres['menu'] ? 'selected' : '' ?>><?= e($m['titre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="du">Du</label>
        <input type="date" id="du" name="du" value="<?= e($filtres['du']) ?>">
    </div>
    <div>
        <label for="au">Au (inclus)</label>
        <input type="date" id="au" name="au" value="<?= e($filtres['au']) ?>">
    </div>
    <button class="bouton" type="submit">Afficher</button>
</form>

<?php if ($erreur !== null): ?>
    <div class="alerte" role="alert"><p><?= e($erreur) ?></p></div>
<?php elseif ($lignes === []): ?>
    <p>Aucune commande pour ces critères.</p>
<?php else: ?>
    <table class="stats">
        <caption>Commandes et chiffre d'affaires par menu, du plus commandé au moins commandé</caption>
        <thead>
            <tr>
                <th scope="col">Menu</th>
                <th scope="col">Commandes</th>
                <th scope="col">Chiffre d'affaires</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $l): ?>
                <tr>
                    <th scope="row"><?= e($l['menu_titre']) ?></th>
                    <td>
                        <span class="barre" style="width: <?= round(100 * $l['nombre_commandes'] / $maxCommandes) ?>%" aria-hidden="true"></span>
                        <?= (int) $l['nombre_commandes'] ?>
                    </td>
                    <td>
                        <span class="barre barre-ca" style="width: <?= round(100 * $l['chiffre_affaires_centimes'] / $maxCa) ?>%" aria-hidden="true"></span>
                        <?= prix($l['chiffre_affaires_centimes'] / 100) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="row">Total</th>
                <td><?= array_sum(array_column($lignes, 'nombre_commandes')) ?></td>
                <td><?= prix(array_sum(array_column($lignes, 'chiffre_affaires_centimes')) / 100) ?></td>
            </tr>
        </tfoot>
    </table>
<?php endif; ?>
