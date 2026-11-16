<h1>Nos menus</h1>

<form id="filtres" class="filtres" method="get" action="/menus">
    <div>
        <label for="prix_min">Prix minimum (€ / pers.)</label>
        <input type="number" id="prix_min" name="prix_min" min="0" step="1"
               value="<?= e((string) ($filtres['prix_min'] ?? '')) ?>">
    </div>
    <div>
        <label for="prix_max">Prix maximum (€ / pers.)</label>
        <input type="number" id="prix_max" name="prix_max" min="0" step="1"
               value="<?= e((string) ($filtres['prix_max'] ?? '')) ?>">
    </div>
    <div>
        <label for="theme">Thème</label>
        <select id="theme" name="theme">
            <option value="">Tous les thèmes</option>
            <?php foreach ($themes as $t): ?>
                <option value="<?= (int) $t['theme_id'] ?>" <?= $filtres['theme'] === (int) $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="regime">Régime</label>
        <select id="regime" name="regime">
            <option value="">Tous les régimes</option>
            <?php foreach ($regimes as $r): ?>
                <option value="<?= (int) $r['regime_id'] ?>" <?= $filtres['regime'] === (int) $r['regime_id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="personnes">Nombre de personnes</label>
        <input type="number" id="personnes" name="personnes" min="1" step="1"
               value="<?= e((string) ($filtres['personnes'] ?? '')) ?>">
    </div>
    <div class="actions">
        <button class="bouton" type="submit">Filtrer</button>
        <a class="bouton bouton-secondaire" href="/menus">Réinitialiser</a>
    </div>
</form>

<p id="nombre" role="status"><?= count($menus) ?> menu<?= count($menus) > 1 ? 's' : '' ?></p>

<div id="resultats">
    <?php require __DIR__ . '/_cartes.php'; ?>
</div>
