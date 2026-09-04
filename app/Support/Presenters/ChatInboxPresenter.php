<?php

namespace App\Support\Presenters;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class ChatInboxPresenter
{
    /**
     * Format conversation data for the web UI.
     */
    public function formatConversations(Collection $conversations): Collection
    {
        return $conversations->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->contact_name,
            'phone' => $c->contact_phone,
            'avatar_url' => $c->contact_avatar_url,
            'preview' => $c->last_message_preview ?? 'No messages yet',
            'time_label' => $c->last_message_at ? $c->last_message_at->diffForHumans(short: true) : '',
            'unread_count' => $c->unread_count,
            'is_active' => true,
            'is_session_active' => $c->is_session_active,
        ]);
    }

    /**
     * Format a single conversation for the web UI.
     */
    public function formatActiveConversation($conversation): ?array
    {
        if (!$conversation) {
            return null;
        }

        return [
            'id' => $conversation->id,
            'name' => $conversation->contact_name,
            'phone' => $conversation->contact_phone,
            'avatar_url' => $conversation->contact_avatar_url,
            'location' => $conversation->contact_location,
            'is_active' => true,
            'is_session_active' => $conversation->is_session_active,
        ];
    }

    /**
     * Format messages for the web UI.
     */
    public function formatMessages(Collection $messages): Collection
    {
        return $messages->map(function ($m) {
            return [
                'id' => $m->id,
                'direction' => $m->direction,
                'message_type' => $m->message_type,
                'body' => $m->message_type === 'template' ? ($m->rendered_body ?? $m->body) : $m->body,
                'media_url' => $m->media_url,
                'resolved_media_url' => $m->resolved_media_url,
                'status' => $m->status,
                'status_icon' => $this->getStatusIcon($m->status),
                'status_color' => $this->getStatusColor($m->status),
                'failure_message' => $m->failure_message,
                'time_label' => ($m->sent_at ?? $m->created_at)?->setTimezone(config('app.timezone', 'Asia/Kolkata'))->format('H:i') ?? '',
                'iso_time' => ($m->sent_at ?? $m->created_at)?->toIso8601String(),
                'card_title' => $m->media_meta['title'] ?? null,
                'card_heading' => $m->media_meta['heading'] ?? null,
                'card_subtext' => $m->media_meta['subtext'] ?? null,
                'card_button_text' => $m->media_meta['button_text'] ?? null,
            ];
        });
    }

    /**
     * Format sidebar data for the web UI.
     */
    public function formatSidebarData($sidebarData): array
    {
        if (empty($sidebarData)) {
            return [];
        }

        // Add any UI specific labels if needed.
        // Currently labels come from DB, but we could add CSS classes here.
        if (!empty($sidebarData['labels'])) {
            $sidebarData['labels'] = array_map(function($label) {
                if (is_array($label) && !isset($label['class'])) {
                    $label['class'] = 'bg-primary/10 text-primary';
                }
                return $label;
            }, $sidebarData['labels']);
        }

        return $sidebarData;
    }

    private function getStatusIcon(?string $status): string
    {
        return match($status) {
            'read' => 'done_all',
            'delivered' => 'done_all',
            'sent' => 'check',
            'failed' => 'error',
            'pending' => 'schedule',
            default => 'schedule',
        };
    }

    private function getStatusColor(?string $status): string
    {
        return match($status) {
            'read' => 'text-sky-400',
            'delivered' => 'text-slate-400',
            'sent' => 'text-slate-400',
            'failed' => 'text-red-500',
            'pending' => 'text-slate-400',
            default => 'text-slate-400',
        };
    }
}
