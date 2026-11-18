<h1>Gestion des commandes</h1>

<form class="filtres" method="get" action="/gestion/commandes" role="search">
    <div>
        <label for="statut">Statut</label>
        <select id="statut" name="statut">
            <option value="">Tous les statuts</option>
            <?php foreach ($statuts as $s): ?>
                <option value="<?= e($s) ?>" <?= $s === $statut ? 'selected' : '' ?>><?= e(libelle_statut($s)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="client">Client (nom, prénom ou email)</label>
        <input id="client" name="client" type="search" value="<?= e($client) ?>">
    </div>
    <button class="bouton" type="submit">Filtrer</button>
</form>

<p><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></p>

<?php foreach ($commandes as $c): ?>
    <article class="ma-commande" aria-labelledby="commande-<?= e($c['numero_commande']) ?>">
        <h2 id="commande-<?= e($c['numero_commande']) ?>">
            <?= e($c['menu_titre']) ?>
            <span class="numero">Commande <?= e($c['numero_commande']) ?></span>
        </h2>
        <p class="statut statut-<?= e($c['statut']) ?>"><?= e(libelle_statut($c['statut'])) ?></p>

        <dl class="details">
            <dt>Client</dt>
            <dd>
                <?= e(trim($c['prenom'] . ' ' . $c['nom'])) ?><br>
                <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a>
                <?php if ($c['telephone'] !== null): ?>
                    · <a href="tel:<?= e($c['telephone']) ?>"><?= e($c['telephone']) ?></a>
                <?php endif; ?>
            </dd>
            <dt>Prestation</dt>
            <dd>le <?= date('d/m/Y', strtotime($c['date_prestation'])) ?> à <?= e(substr($c['heure_livraison'], 0, 5)) ?>, <?= e($c['ville_prestation']) ?></dd>
            <dt>Personnes</dt>
            <dd><?= (int) $c['nombre_personne'] ?></dd>
            <dt>Total</dt>
            <dd class="total"><?= prix($c['prix_total']) ?></dd>
            <?php if ($c['motif_annulation'] !== null): ?>
                <dt>Motif d'annulation</dt>
                <dd><?= e($c['motif_annulation']) ?></dd>
            <?php endif; ?>
        </dl>

        <?php if ($c['suivant'] !== null): ?>
            <form method="post" action="/gestion/commandes/avancer<?= e($filtres) ?>">
                <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
                <input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                <input type="hidden" name="statut_vu" value="<?= e($c['statut']) ?>">
                <button class="bouton" type="submit">
                    Passer à : <?= e(libelle_statut($c['suivant'])) ?>
                    <span class="visuellement-cache">(commande <?= e($c['numero_commande']) ?>)</span>
                </button>
            </form>
        <?php endif; ?>

        <?php if ($c['annulable']): ?>
            <details class="annulation">
                <summary>Annuler la commande <?= e($c['numero_commande']) ?></summary>
                <form method="post" action="/gestion/commandes/annuler<?= e($filtres) ?>"
                      data-confirmation="Annuler définitivement la commande <?= e($c['numero_commande']) ?> ?">
                    <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
                    <input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                    <fieldset>
                        <legend>Client contacté par</legend>
                        <label class="choix"><input type="radio" name="mode_contact" value="telephone" required> Téléphone</label>
                        <label class="choix"><input type="radio" name="mode_contact" value="email"> Email</label>
                    </fieldset>
                    <label for="motif-<?= e($c['numero_commande']) ?>">Motif de l'annulation</label>
                    <textarea id="motif-<?= e($c['numero_commande']) ?>" name="motif" rows="3" maxlength="500" required></textarea>
                    <button class="bouton bouton-secondaire" type="submit">Confirmer l'annulation</button>
                </form>
            </details>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
