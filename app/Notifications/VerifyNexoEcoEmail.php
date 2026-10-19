<?php

namespace App\Notifications;

use App\Services\EmailVerificationCodeService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyNexoEcoEmail extends Notification
{
    public function __construct(public readonly string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $seller = $notifiable->solicitudVendedor()->exists();

        return (new MailMessage)
            ->from(config('mail.from.address'), 'NexoEco')
            ->subject($seller ? 'Verifica tu correo para vender en NexoEco' : 'Bienvenido a NexoEco · Verifica tu correo')
            ->view('emails.verify-email', [
                'name' => $notifiable->nombre_completo ?: $notifiable->name,
                'seller' => $seller,
                'code' => $this->code,
                'expires' => EmailVerificationCodeService::EXPIRES_MINUTES,
            ]);
    }
}
