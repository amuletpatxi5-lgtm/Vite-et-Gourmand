<?php

declare(strict_types=1);

namespace App\Controller;

use App\Mailer;

// US-14 : formulaire de contact, le message part dans la boîte mail de l'entreprise (MAIL_CONTACT)
final class ContactController extends Controller
{
    public function contact(): void
    {
        $valeurs = ['titre' => '', 'email' => '', 'message' => ''];
        $erreurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach (array_keys($valeurs) as $champ) {
                $valeur = $_POST[$champ] ?? '';
                $valeurs[$champ] = is_string($valeur) && mb_check_encoding($valeur, 'UTF-8') ? trim($valeur) : '';
            }
            $erreurs = $this->verifier($valeurs);

            if ($erreurs === []) {
                // Pot de miel : champ invisible pour un humain, rempli par les robots. On fait comme si c'était envoyé.
                $robot = ($_POST['site_web'] ?? '') !== '';

                $envoye = $robot || Mailer::envoyer(
                    (string) getenv('MAIL_CONTACT'),
                    'Contact site : ' . $valeurs['titre'],
                    "Message envoyé depuis le formulaire de contact par {$valeurs['email']} :\n\n{$valeurs['message']}",
                    $valeurs['email']
                );

                if ($envoye) {
                    message_flash('Merci, votre message a bien été envoyé. Nous vous répondrons rapidement.');
                    rediriger('/contact');
                }
                $erreurs['formulaire'] = "Votre message n'a pas pu être envoyé. Réessayez dans quelques minutes ou appelez-nous.";
            }
        }

        $this->render('contact', [
            'titre' => 'Contact',
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
        ]);
    }

    private function verifier(array $v): array
    {
        $erreurs = [];

        if (!csrf_valide()) {
            $erreurs['formulaire'] = 'Votre session a expiré. Merci de renvoyer le formulaire.';
        }
        // \p{Cc} : caractères de contrôle (dont les retours à la ligne), interdits car le titre devient l'objet du mail
        if ($v['titre'] === '' || mb_strlen($v['titre']) > 150 || preg_match('/\p{Cc}/u', $v['titre'])) {
            $erreurs['titre'] = 'Indiquez un titre sur une seule ligne (150 caractères maximum).';
        }
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = "L'adresse email n'est pas valide.";
        }
        if (mb_strlen($v['message']) < 10 || mb_strlen($v['message']) > 3000) {
            $erreurs['message'] = 'Votre message doit contenir entre 10 et 3000 caractères.';
        }

        return $erreurs;
    }
}
