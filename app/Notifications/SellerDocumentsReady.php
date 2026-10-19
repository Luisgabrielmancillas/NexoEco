<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerDocumentsReady extends Notification
{
    public function __construct(public int $applicationId, public string $sellerName, public bool $correction, public string $submittedAt) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), 'NexoEco')
            ->subject($this->correction ? 'Documentos corregidos listos para revisión · NexoEco' : 'Nueva solicitud de vendedor por revisar · NexoEco')
            ->view('emails.seller-documents-ready', [
                'moderatorName' => $notifiable->nombre_completo ?: $notifiable->name,
                'sellerName' => $this->sellerName, 'applicationId' => $this->applicationId,
                'correction' => $this->correction, 'submittedAt' => $this->submittedAt,
                'reviewUrl' => route($notifiable->tieneTipo('administrador') ? 'admin.solicitudes.show' : 'moderador.solicitudes.show', $this->applicationId),
            ]);
    }
}
