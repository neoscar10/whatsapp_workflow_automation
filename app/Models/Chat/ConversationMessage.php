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
     * Get the fully rendered template body (with parameters resolved) if message_type is template.
     */
    public function getRenderedBodyAttribute(): string
    {
        if ($this->message_type !== 'template') {
            return $this->body ?? '';
        }

        $templateName = $this->meta_payload['template_name'] ?? $this->body;
        if (!$templateName) {
            return $this->body ?? '';
        }

        $template = \App\Models\WhatsApp\WhatsAppTemplate::where('remote_template_name', $templateName)
            ->where('company_id', $this->conversation->company_id)
            ->first();

        if (!$template) {
            $template = \App\Models\WhatsApp\WhatsAppTemplate::where('remote_template_name', $templateName)->first();
            if (!$template) {
                return $this->body ?? '';
            }
        }

        $bodyText = $template->body_text;
        $components = $this->meta_payload['components'] ?? [];
        $bodyParameters = [];

        foreach ($components as $component) {
            if (($component['type'] ?? '') === 'body' && !empty($component['parameters'])) {
                foreach ($component['parameters'] as $param) {
                    if (isset($param['text'])) {
                        $bodyParameters[] = $param['text'];
                    }
                }
            }
        }

        $rendered = $bodyText;
        foreach ($bodyParameters as $index => $value) {
            $placeholder = '{{' . ($index + 1) . '}}';
            $rendered = str_replace($placeholder, $value, $rendered);
        }

        return $rendered;
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
