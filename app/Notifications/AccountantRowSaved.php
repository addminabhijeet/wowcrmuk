<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountantRowSaved extends Notification
{
    use Queueable;

    protected $rowData;
    protected $user;

    public function __construct($rowData, $user)
    {
        $this->rowData = $rowData;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database']; // store in DB
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "A row was saved by {$this->user->name} ({$this->user->role})",
            'row_id' => $this->rowData['id'] ?? 'N/A',
            'row_data' => $this->rowData,
        ];
    }
}
