<?php

namespace App\Notifications;

use App\Models\PenerimaanBarang;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PenerimaanAwaitingVerification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PenerimaanBarang $penerimaan,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('sipeni.notifications.mail_enabled', false)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Penerimaan barang menunggu verifikasi')
            ->line('Barang dari distribusi menunggu verifikasi di unit Anda.')
            ->action('Buka penerimaan', route('transaction.penerimaan-barang.show', $this->penerimaan->id_penerimaan));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'penerimaan_awaiting_verification',
            'penerimaan_id' => $this->penerimaan->id_penerimaan,
            'url' => route('transaction.penerimaan-barang.show', $this->penerimaan->id_penerimaan),
            'message' => 'Penerimaan barang menunggu verifikasi unit kerja.',
        ];
    }
}
