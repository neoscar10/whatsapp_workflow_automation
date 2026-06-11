<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'media_meta' => 'array',
        'meta_payload' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    /**
     * Get a robust, web-accessible URL for the media attached to this message.
     */
    public function getResolvedMediaUrlAttribute(): ?string
    {
        $url = $this->media_url;

        if (empty($url)) {
            // Check for a locally saved path in meta (both inbound and outbound media)
            $localPath = $this->media_meta['local_path'] ?? null;
            if ($localPath) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($localPath);
            }
            return null;
        }

        // If it's already an absolute URL (e.g. from Meta inbound), return it
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // Handle relative paths (e.g. 'chat_media/filename.jpg') — outbound & inbound media
        return \Illuminate\Support\Facades\Storage::disk('public')->url($url);
    }

    /**
     * Generate a concise preview snippet for the conversation sidebar.
     */
    public function generatePreviewText(): string
    {
        if ($this->message_type === 'text') {
            return mb_substr($this->body ?? '', 0, 50);
        }

        if ($this->message_type === 'template') {
            $templateName = $this->meta_payload['template_name'] ?? $this->body ?? 'Template';
            return 'Template: ' . $templateName;
        }

        $typePrefix = ucfirst($this->message_type);
        return $this->body ? $typePrefix . ': ' . mb_substr($this->body, 0, 30) : $typePrefix;
    }

    /**
     * Boot the model and register lifecycle events.
     */
    protected static function booted(): void
    {
        static::created(function (ConversationMessage $message) {
            $message->conversation->update([
                'last_message_preview' => $message->generatePreviewText(),
                'last_message_at' => $message->sent_at ?? $message->created_at,
            ]);
        });
    }
}
