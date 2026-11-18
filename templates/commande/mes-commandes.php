<h1>Mes commandes</h1>

<?php if ($commandes === []): ?>
    <p>Vous n'avez pas encore passé de commande.</p>
    <p><a class="bouton" href="/menus">Découvrir nos menus</a></p>
<?php endif; ?>

<?php foreach ($commandes as $c): ?>
    <article class="ma-commande" aria-labelledby="commande-<?= e($c['numero_commande']) ?>">
        <h2 id="commande-<?= e($c['numero_commande']) ?>">
            <?= e($c['menu_titre']) ?>
            <span class="numero">Commande <?= e($c['numero_commande']) ?>, passée le <?= date('d/m/Y', strtotime($c['date_commande'])) ?></span>
        </h2>
        <p class="statut statut-<?= e($c['statut']) ?>"><?= e(libelle_statut($c['statut'])) ?></p>

        <dl class="details">
            <dt>Prestation</dt>
            <dd>le <?= date('d/m/Y', strtotime($c['date_prestation'])) ?> à <?= e(substr($c['heure_livraison'], 0, 5)) ?></dd>
            <dt>Adresse</dt>
            <dd><?= e($c['adresse_prestation']) ?>, <?= e($c['code_postal_prestation']) ?> <?= e($c['ville_prestation']) ?></dd>
            <dt>Personnes</dt>
            <dd><?= (int) $c['nombre_personne'] ?></dd>
            <dt>Menu</dt>
            <dd><?= prix($c['prix_menu']) ?></dd>
            <?php if ((float) $c['remise'] > 0): ?>
                <dt>Remise de 10 %</dt>
                <dd>−<?= prix($c['remise']) ?></dd>
            <?php endif; ?>
            <dt>Livraison</dt>
            <dd><?= prix($c['prix_livraison']) ?></dd>
            <dt>Total</dt>
            <dd class="total"><?= prix($c['prix_total']) ?></dd>
        </dl>

        <h3>Suivi</h3>
        <ol class="suivi">
            <?php foreach ($suivis[$c['numero_commande']] ?? [] as $etape): ?>
                <li>
                    <?= e(libelle_statut($etape['statut'])) ?> :
                    <time datetime="<?= date('c', strtotime($etape['date_changement'])) ?>">
                        le <?= date('d/m/Y à H:i', strtotime($etape['date_changement'])) ?>
                    </time>
                </li>
            <?php endforeach; ?>
        </ol>

        <?php if ($c['statut'] === 'en_attente'): ?>
            <form method="post" action="/mes-commandes/annuler"
                  data-confirmation="Annuler la commande <?= e($c['numero_commande']) ?> ? Cette action est définitive.">
                <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
                <input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                <button class="bouton bouton-secondaire" type="submit">Annuler la commande <?= e($c['numero_commande']) ?></button>
            </form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
