<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Alerta;

class AlertaGenerada extends Notification
{
    use Queueable;

    public $alerta;

    public function __construct(Alerta $alerta)
    {
        $this->alerta = $alerta;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Nueva Alerta de Consumo de Agua')
            ->line("Tipo: {$this->alerta->tipo}")
            ->line("Mensaje: {$this->alerta->mensaje}")
            ->line("Fecha: {$this->alerta->fecha}")
            ->line('Por favor revise su instalación de agua.');
    }
}
