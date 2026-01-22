<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected Document $document;
    protected string $action;
    protected ?string $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, string $action, ?string $comment = null)
    {
        $this->document = $document;
        $this->action = $action;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->getMessage();

        return (new MailMessage)
            ->subject('[Hukum RSUP Ngoerah] ' . $message['title'])
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line($message['message'])
            ->action('Lihat Dokumen', url(route('documents.show', $this->document)))
            ->line('Terima kasih telah menggunakan Sistem Manajemen Dokumen Hukum.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $message = $this->getMessage();

        return [
            'type' => $message['type'],
            'title' => $message['title'],
            'message' => $message['message'],
            'document_id' => $this->document->id,
            'document_number' => $this->document->document_number,
            'action' => $this->action,
            'action_url' => route('documents.show', $this->document),
        ];
    }

    /**
     * Get the message based on action.
     */
    protected function getMessage(): array
    {
        return match ($this->action) {
            'submitted_for_review' => [
                'type' => 'document',
                'title' => 'Dokumen Perlu Review',
                'message' => "Dokumen \"{$this->document->title}\" telah diajukan untuk review oleh {$this->document->creator->name}.",
            ],
            'submitted_for_approval' => [
                'type' => 'approval',
                'title' => 'Dokumen Perlu Persetujuan',
                'message' => "Dokumen \"{$this->document->title}\" telah diajukan untuk persetujuan.",
            ],
            'approved' => [
                'type' => 'approval',
                'title' => 'Dokumen Disetujui',
                'message' => "Dokumen \"{$this->document->title}\" telah disetujui." . ($this->comment ? " Catatan: {$this->comment}" : ''),
            ],
            'rejected' => [
                'type' => 'rejection',
                'title' => 'Dokumen Ditolak',
                'message' => "Dokumen \"{$this->document->title}\" ditolak." . ($this->comment ? " Alasan: {$this->comment}" : ''),
            ],
            'published' => [
                'type' => 'document',
                'title' => 'Dokumen Diterbitkan',
                'message' => "Dokumen \"{$this->document->title}\" telah diterbitkan dan berlaku.",
            ],
            'archived' => [
                'type' => 'document',
                'title' => 'Dokumen Diarsipkan',
                'message' => "Dokumen \"{$this->document->title}\" telah diarsipkan.",
            ],
            'expiring_soon' => [
                'type' => 'expiry',
                'title' => 'Dokumen Akan Kadaluarsa',
                'message' => "Dokumen \"{$this->document->title}\" akan kadaluarsa dalam waktu dekat. Silakan lakukan review.",
            ],
            'expired' => [
                'type' => 'expiry',
                'title' => 'Dokumen Kadaluarsa',
                'message' => "Dokumen \"{$this->document->title}\" telah kadaluarsa.",
            ],
            default => [
                'type' => 'info',
                'title' => 'Pembaruan Dokumen',
                'message' => "Dokumen \"{$this->document->title}\" telah diperbarui.",
            ],
        };
    }
}
