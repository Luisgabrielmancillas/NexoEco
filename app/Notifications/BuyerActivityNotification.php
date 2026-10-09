<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BuyerActivityNotification extends Notification
{
    public function __construct(public string $titulo, public string $mensaje, public string $url, public array $context = []) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return ['titulo' => $this->titulo, 'mensaje' => $this->mensaje, 'url' => $this->url] + $this->context;
    }
}
