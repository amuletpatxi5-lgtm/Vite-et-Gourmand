<?php $actuel = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); $compte = utilisateur_connecte(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre ?? 'Accueil') ?> | Vite &amp; Gourmand</title>
    <link rel="stylesheet" href="/css/style.css">
    <?php foreach ($scripts ?? [] as $script): ?>
        <script src="<?= e($script) ?>" defer></script>
    <?php endforeach; ?>
</head>
<body>
    <a class="evitement" href="#contenu">Aller au contenu</a>

    <header class="entete">
        <a class="logo" href="/">Vite &amp; Gourmand</a>
        <nav aria-label="Menu principal">
            <ul>
                <li><a href="/" <?= $actuel === '/' ? 'aria-current="page"' : '' ?>>Accueil</a></li>
                <li><a href="/menus" <?= $actuel === '/menus' ? 'aria-current="page"' : '' ?>>Nos menus</a></li>
                <?php if ($compte === null): ?>
                    <li><a href="/connexion" <?= $actuel === '/connexion' ? 'aria-current="page"' : '' ?>>Connexion</a></li>
                <?php else: ?>
                    <li><a href="/mes-commandes" <?= $actuel === '/mes-commandes' ? 'aria-current="page"' : '' ?>>Mes commandes</a></li>
                    <?php if (in_array($compte['role'], ['employe', 'administrateur'], true)): ?>
                    <li><a href="/gestion/commandes" <?= $actuel === '/gestion/commandes' ? 'aria-current="page"' : '' ?>>Gestion</a></li>
                    <li><a href="/gestion/avis" <?= $actuel === '/gestion/avis' ? 'aria-current="page"' : '' ?>>Avis</a></li>
                    <?php endif; ?>
                    <?php if ($compte['role'] === 'administrateur'): ?>
                    <li><a href="/admin/statistiques" <?= $actuel === '/admin/statistiques' ? 'aria-current="page"' : '' ?>>Statistiques</a></li>
                    <?php endif; ?>

                    <li>
                        <form method="post" action="/deconnexion">
                            <input type="hidden" name="csrf" value="<?= e(csrf_jeton()) ?>">
                            <button class="lien-nav" type="submit">Se déconnecter</button>
                        </form>
                    </li>
                <?php endif; ?>    
                <li><a href="/contact" <?= $actuel === '/contact' ? 'aria-current="page"' : '' ?>>Contact</a></li>
            </ul>
        </nav>
    </header>

    <main id="contenu" tabindex="-1">
        <?php $flash = lire_message_flash(); ?>
        <?php if ($flash !== null): ?>
            <p class="flash" role="status"><?= e($flash) ?></p>
        <?php endif; ?>
        <?= $contenu ?>
    </main>

    <footer class="pied">
        <section>
            <h2>Horaires</h2>
            <ul>
                <?php foreach ($horaires as $h): ?>
                    <li>
                        <?= e(ucfirst($h['jour'])) ?> :
                        <?= $h['heure_ouverture'] === null
                            ? 'fermé'
                            : e(substr($h['heure_ouverture'], 0, 5)) . ' – ' . e(substr($h['heure_fermeture'], 0, 5)) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <nav aria-label="Informations légales">
            <a href="/mentions-legales">Mentions légales</a>
            <a href="/cgv">Conditions générales de vente</a>
        </nav>
    </footer>
</body>
</html>
