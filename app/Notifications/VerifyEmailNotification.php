<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifica tu correo electrónico — ' . config('app.name'))
            ->greeting('¡Hola!')
            ->line('Gracias por registrarte en ' . config('app.name') . '. Haz clic en el botón de abajo para verificar tu dirección de correo electrónico.')
            ->action('Verificar correo electrónico', $url)
            ->line('Este enlace expirará en ' . config('auth.verification.expire', 60) . ' minutos.')
            ->line('Si no creaste una cuenta, puedes ignorar este correo.')
            ->salutation('El equipo de ' . config('app.name'));
    }
}
