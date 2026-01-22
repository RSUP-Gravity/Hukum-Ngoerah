<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentAccessGranted extends Notification implements ShouldQueue
{
    use Queueable;

    protected Document $document;
    protected string $accessLevel;
    protected ?string $expiresAt;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, string $accessLevel, ?string $expiresAt = null)
    {
        $this->document = $document;
        $this->accessLevel = $accessLevel;
        $this->expiresAt = $expiresAt;
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
        $levelLabel = $this->getLevelLabel();

        return (new MailMessage)
            ->subject('[Hukum RSUP Ngoerah] Akses Dokumen Diberikan')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Anda telah diberikan akses \"{$levelLabel}\" untuk dokumen:")
            ->line("\"{$this->document->title}\" ({$this->document->document_number})")
            ->action('Lihat Dokumen', url(route('documents.show', $this->document)))
            ->line($this->expiresAt ? "Akses berlaku sampai: {$this->expiresAt}" : 'Akses tidak memiliki batas waktu.')
            ->line('Terima kasih telah menggunakan Sistem Manajemen Dokumen Hukum.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'document',
            'title' => 'Akses Dokumen Diberikan',
            'message' => "Anda diberikan akses \"{$this->getLevelLabel()}\" untuk dokumen \"{$this->document->title}\".",
            'document_id' => $this->document->id,
            'document_number' => $this->document->document_number,
            'access_level' => $this->accessLevel,
            'expires_at' => $this->expiresAt,
            'action_url' => route('documents.show', $this->document),
        ];
    }

    /**
     * Get human-readable access level label.
     */
    protected function getLevelLabel(): string
    {
        return match ($this->accessLevel) {
            'full' => 'Akses Penuh',
            'download' => 'Baca & Download',
            default => 'Baca Saja',
        };
    }
}
