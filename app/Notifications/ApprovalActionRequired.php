<?php

namespace App\Notifications;

use App\Models\ApprovalLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalActionRequired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ApprovalLog $approvalLog,
        public readonly string $permintaanNo,
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
            ->subject('Approval permintaan menunggu tindakan')
            ->line('Permintaan '.$this->permintaanNo.' memerlukan persetujuan Anda.')
            ->action('Buka approval', route('transaction.approval.show', $this->approvalLog->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_action_required',
            'approval_log_id' => $this->approvalLog->id,
            'permintaan_no' => $this->permintaanNo,
            'step' => $this->approvalLog->approvalFlow?->nama_step,
            'url' => route('transaction.approval.show', $this->approvalLog->id),
            'message' => 'Permintaan '.$this->permintaanNo.' menunggu approval Anda.',
        ];
    }
}
