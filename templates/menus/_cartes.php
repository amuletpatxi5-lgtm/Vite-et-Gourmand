<?php if ($menus === []): ?>
    <p>Aucun menu ne correspond à vos critères. Essayez d'élargir votre recherche.</p>
<?php else: ?>
    <ul class="cartes">
        <?php foreach ($menus as $m): ?>
            <li class="carte">
                <h2><?= e($m['titre']) ?></h2>
                <p class="etiquettes"><?= e($m['theme']) ?> · <?= e($m['regime']) ?></p>
                <p><?= e(extrait($m['description'])) ?></p>
                <p>À partir de <?= (int) $m['nombre_de_personne_minimum'] ?> personnes,
                   soit <?= prix((float) $m['prix_par_personne'] * $m['nombre_de_personne_minimum']) ?></p>

                <p class="prix"><?= prix($m['prix_par_personne']) ?> <span>/ personne</span></p>
                <a class="bouton" href="/menu?id=<?= (int) $m['menu_id'] ?>">Détail du <?= e($m['titre']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
