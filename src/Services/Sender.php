<?php

namespace App\Services;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Sender
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendEmail(string $subject, string $text, string $dest) {
        $email = new Email();
        $email->text($text)
            ->to($dest)
            ->subject($subject)
            ->from('no-reply@bucketlist.com');

        $this->mailer->send($email);
    }

}