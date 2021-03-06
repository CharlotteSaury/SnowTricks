<?php

namespace App\Helper;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailSenderHelper
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    const ACCOUNT_CONFIRMATION = 'Welcome to Snowtricks !';
    const PASSWORD_RESET = 'Password reinitialization.';
    const TRICK_REPORT = 'Trick report';

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle mail sending for registration, forgot password and trick report features.
     *
     * @return void
     */
    public function sendMail(string $type, User $user, array $data)
    {
        try {
            $subject = mb_strtoupper($type);
            $message = (new TemplatedEmail())
                ->from(new Address('mailer@snowtricks.com', 'No-reply Snowtricks'))
                ->to(new Address($user->getEmail(), $user->getUsername()))
                ->context($data)
                ->htmlTemplate('email/'.$type.'.html.twig');

            switch ($subject) {
                case 'ACCOUNT_CONFIRMATION':
                    $message->subject(self::ACCOUNT_CONFIRMATION);
                    break;
                case 'PASSWORD_RESET':
                    $message->subject(self::PASSWORD_RESET);
                    break;
                case 'TRICK_REPORT':
                    $message->subject(self::TRICK_REPORT);
                    break;
            }

            $this->mailer->send($message);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
