<section class="hero">
    <div>
        <p class="accroche">Traiteur à Bordeaux depuis 25 ans</p>
        <h1>Ici on met les petits plats dans les grands</h1>
        <p>Julie et José préparent vos repas de fête à Bordeaux et ses alentours depuis 25 ans.
           Choisissez votre menu, nous nous occupons du reste.</p>
        <a class="bouton" href="/menus">Voir nos Menus</a>
    </div>
    <img src="/images/illustrations/illu-hero.png" alt="">
</section>

<section class="presentation" aria-labelledby="qui-titre">
    <img src="/images/illustrations/illu-qui-sommes-nous.png" alt="" width="1116" height="840" loading="lazy">
    <div>
        <h2 id="qui-titre">Qui sommes-nous ?</h2>
        <p>Depuis 25 ans, Julie et José cuisinent pour les fêtes, les mariages et les repas de famille
           à Bordeaux et dans ses alentours.</p>
        <ul class="engagements">
            <li><strong>Fait maison</strong> : tout est préparé dans notre cuisine, le jour même ou la veille.</li>
            <li><strong>Produits du Sud-Ouest</strong> : huîtres du bassin, bœuf de Bazas, cèpes du Médoc.</li>
            <li><strong>Livraison à l'heure</strong> : gratuite dans Bordeaux, et nous prêtons le matériel de service.</li>
            <li><strong>Suivi de A à Z</strong> : vous suivez votre commande étape par étape depuis votre espace.</li>
        </ul>
    </div>
</section>

<?php if ($avis !== []): ?>
    <section aria-labelledby="avis-titre">
        <h2 id="avis-titre">Ils nous ont fait confiance</h2>
        <ul class="avis-liste">
            <?php foreach ($avis as $a): ?>
                <li>
                    <figure class="avis">
                        <p class="etoiles">
                            <span aria-hidden="true"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></span>
                            <span class="visuellement-cache">Note : <?= (int) $a['note'] ?> sur 5</span>
                        </p>
                        <blockquote><p><?= e($a['description']) ?></p></blockquote>
                        <figcaption>
                            <?= e($a['prenom']) ?> <?= e($a['initiale']) ?>., <?= e($a['menu_titre']) ?>,
                            <time datetime="<?= date('Y-m-d', strtotime($a['date_avis'])) ?>"><?= date('m/Y', strtotime($a['date_avis'])) ?></time>
                        </figcaption>
                    </figure>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
