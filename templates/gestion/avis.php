<h1>Avis à valider</h1>

<?php if ($avis === []): ?>
    <p>Aucun avis en attente.</p>
<?php endif; ?>

<?php foreach ($avis as $a): ?>
    <article class="ma-commande" aria-labelledby="avis-<?= (int) $a['avis_id'] ?>">
        <h2 id="avis-<?= (int) $a['avis_id'] ?>">
            <?= e($a['menu_titre']) ?>
            <span class="numero">Commande <?= e($a['numero_commande']) ?>, avis du <?= date('d/m/Y', strtotime($a['date_avis'])) ?></span>
        </h2>
        <p class="etoiles">
            <span aria-hidden="true"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></span>
            <span class="visuellement-cache">Note : <?= (int) $a['note'] ?> sur 5</span>
        </p>
        <blockquote><p><?= e($a['description']) ?></p></blockquote>
        <p>Client : <?= e(trim($a['prenom'] . ' ' . $a['nom'])) ?></p>

        <div class="actions">
            <?php foreach (['valide' => 'Publier', 'refuse' => 'Refuser'] as $decision => $libelle): ?>
                <form method="post" action="/gestion/avis/moderer">
                    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
                    <input type="hidden" name="avis_id" value="<?= (int) $a['avis_id'] ?>">
                    <input type="hidden" name="decision" value="<?= $decision ?>">
                    <button class="bouton<?= $decision === 'refuse' ? ' bouton-secondaire' : '' ?>" type="submit">
                        <?= $libelle ?> <span class="visuellement-cache">l'avis sur la commande <?= e($a['numero_commande']) ?></span>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </article>
<?php endforeach; ?>
