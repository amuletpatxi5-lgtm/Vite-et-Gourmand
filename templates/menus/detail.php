<p><a href="/menus">← Retour aux menus</a></p>

<article>
    <h1><?= e($menu['titre']) ?></h1>
    <p class="etiquettes"><?= e($menu['theme']) ?> · <?= e($menu['regime']) ?></p>

    <?php if ($images !== []): ?>
        <div class="galerie">
            <?php foreach ($images as $image): ?>
                <img src="<?= e($image['chemin']) ?>" alt="<?= e($image['texte_alternatif']) ?>">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p><?= e($menu['description']) ?></p>

    <section class="conditions" aria-labelledby="titre-conditions">
        <h2 id="titre-conditions">Conditions</h2>
        <p><?= nl2br(e($menu['conditions'])) ?></p>
    </section>

    <section aria-labelledby="titre-composition">
        <h2 id="titre-composition">Composition</h2>
        <?php foreach (['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'] as $type => $libelle): ?>
            <?php if ($plats[$type] !== []): ?>
                <h3><?= $libelle ?></h3>
                <ul class="plats">
                    <?php foreach ($plats[$type] as $plat): ?>
                        <li>
                            <?= e($plat['titre_plat']) ?>
                            <span class="allergenes">Allergènes : <?= e($plat['allergenes'] ?? 'aucun') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    </section>

    <section class="commande" aria-labelledby="titre-commande">
        <h2 id="titre-commande">Commander</h2>
        <p class="prix"><?= prix($menu['prix_par_personne']) ?> <span>/ personne</span></p>
        <p>Minimum <?= (int) $menu['nombre_de_personne_minimum'] ?> personnes</p>
        <?php if ($menu['quantite_restante'] > 0): ?>
            <p>Stock restant : <?= (int) $menu['quantite_restante'] ?></p>
            <a class="bouton" href="/commander?menu=<?= (int) $menu['menu_id'] ?>">Commander ce menu</a>
        <?php else: ?>
            <p><strong>Ce menu n'est plus disponible pour le moment.</strong></p>
        <?php endif; ?>
    </section>
</article>
