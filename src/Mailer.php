<?php
// Envoi des mails du site : Mailpit en local, Brevo en production.
// Tous les réglages viennent des variables MAIL_* du .env.

declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    /**
     * Envoie un mail en texte brut. Renvoie true si l'envoi a réussi.
     */
    public static function envoyer(
        string $destinataire,
        string $sujet,
        string $texte,
        ?string $repondreA = null
    ): bool {
        // true : PHPMailer lève une exception en cas d'erreur
        $mail = new PHPMailer(true);

        try {
            // Serveur SMTP
            $mail->isSMTP();
            $mail->Host = (string) getenv('MAIL_HOST');
            $mail->Port = (int) getenv('MAIL_PORT');
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            // Chiffrement : vide en local (Mailpit), « tls » en production (Brevo)
            $chiffrement = (string) getenv('MAIL_ENCRYPTION');
            $mail->SMTPSecure = $chiffrement;
            $mail->SMTPAutoTLS = $chiffrement !== '';

            // Identifiants : seulement s'ils sont renseignés (Brevo)
            $utilisateur = (string) getenv('MAIL_USER');
            if ($utilisateur !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $utilisateur;
                $mail->Password = (string) getenv('MAIL_PASSWORD');
            }

            // Expéditeur, destinataire, adresse de réponse
            $mail->setFrom((string) getenv('MAIL_FROM'), (string) getenv('MAIL_FROM_NAME'));
            $mail->addAddress($destinataire);
            if ($repondreA !== null) {
                $mail->addReplyTo($repondreA);
            }

            // Contenu
            $mail->Subject = $sujet;
            $mail->Body = $texte;

            return $mail->send();
        } catch (Exception $e) {
            // Le détail va dans les logs du serveur, jamais à l'écran
            error_log('Envoi du mail impossible : ' . $mail->ErrorInfo);
            return false;
        }
    }
}
