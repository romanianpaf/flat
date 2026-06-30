<?php

namespace App\Notifications;

use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificare către adminii/cex ai unui tenant când cineva cere un cont nou.
 * Canale: database (clopoțel in-app) + mail.
 */
class RegistrationRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public RegistrationRequest $registrationRequest)
    {
    }

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->registrationRequest;
        $name = trim($r->first_name . ' ' . $r->last_name);
        $url = rtrim(config('app.url'), '/') . '/admin/cereri-inregistrare';

        $mail = (new MailMessage())
            ->subject('Cerere nouă de cont — ' . $name)
            ->greeting('Salut!')
            ->line('A fost depusă o cerere de cont care necesită aprobare.')
            ->line('Solicitant: ' . $name)
            ->line('Email: ' . $r->email);

        if ($r->phone) {
            $mail->line('Telefon: ' . $r->phone);
        }

        return $mail
            ->action('Vezi cererile', $url)
            ->line('Te rugăm să o aprobi sau să o respingi din platformă.');
    }

    /** @return array<string,mixed> */
    public function toArray(object $notifiable): array
    {
        $r = $this->registrationRequest;
        $name = trim($r->first_name . ' ' . $r->last_name);

        return [
            'type' => 'registration_request',
            'registration_request_id' => $r->id,
            'tenant_id' => $r->tenant_id,
            'requester_name' => $name,
            'email' => $r->email,
            'phone' => $r->phone,
            'title' => 'Cerere nouă de cont',
            'message' => $name . ' a solicitat un cont.',
            'url' => '/admin/cereri-inregistrare',
        ];
    }
}
