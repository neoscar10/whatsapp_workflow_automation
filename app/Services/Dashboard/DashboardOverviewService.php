<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Chat\ConversationMessage;
use App\Models\Chat\Conversation;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\Campaign\Campaign;
use App\Models\Contact\Contact;
use Illuminate\Support\Carbon;

class DashboardOverviewService
{
    /**
     * Get the dashboard overview data.
     *
     * @param User|null $user
     * @return array
     */
    public function getOverviewData(?User $user = null): array
    {
        $displayName = $user ? $user->name : 'Admin';
        $companyId = $user ? $user->company_id : null;
        
        $walletBalance = $user && $user->wallet ? number_format($user->wallet->balance, 2) : '0.00';

        // 1. Fetch Stats
        $totalMessages = $companyId ? ConversationMessage::whereHas('conversation', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->count() : 0;

        $activeChats = $companyId ? Conversation::where('company_id', $companyId)
            ->whereNotNull('last_customer_message_at')
            ->where('last_customer_message_at', '>=', now()->subHours(24))
            ->count() : 0;

        $totalContacts = $companyId ? Contact::where('company_id', $companyId)->count() : 0;

        $templatesCount = $companyId ? WhatsAppTemplate::where('company_id', $companyId)
            ->where('status', 'APPROVED')
            ->count() : 0;

        // 2. Chart (Last 7 days messages)
        $chartDays = [];
        $messagesPerDay = [];
        $totalMessagesLast7Days = 0;
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartDays[] = strtoupper($date->format('D'));
            
            $count = $companyId ? ConversationMessage::whereHas('conversation', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->whereDate('created_at', $date)->count() : 0;
            
            $messagesPerDay[] = $count;
            $totalMessagesLast7Days += $count;
        }

        // 3. Campaigns Widget
        $recentCampaigns = $companyId ? Campaign::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get() : collect();
            
        $formattedCampaigns = $recentCampaigns->map(function($c) {
            $statusClass = match(strtolower($c->status)) {
                'active', 'running' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-500',
                'scheduled' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-500',
                'draft', 'paused' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                default => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-500',
            };
            
            $dotClass = match(strtolower($c->status)) {
                'active', 'running' => 'bg-green-500',
                'scheduled' => 'bg-amber-500',
                'draft', 'paused' => 'bg-slate-400',
                default => 'bg-blue-500',
            };

            return [
                'name' => $c->name,
                'category' => $c->type ?? 'Marketing',
                'status' => ucfirst(strtolower($c->status)),
                'status_class' => $statusClass,
                'dot_class' => $dotClass,
                'sent' => number_format($c->total_recipients ?? 0),
                'opened' => ($c->total_recipients > 0 ? round(($c->total_read / $c->total_recipients) * 100) : 0) . '%',
            ];
        })->toArray();

        // 4. Activities Feed
        $activities = [];
        if ($companyId) {
            $latestContacts = Contact::where('company_id', $companyId)->orderBy('created_at', 'desc')->take(2)->get();
            $latestTemplates = WhatsAppTemplate::where('company_id', $companyId)->orderBy('created_at', 'desc')->take(2)->get();
            $latestCampaigns = Campaign::where('company_id', $companyId)->orderBy('created_at', 'desc')->take(2)->get();
            
            $mixed = collect();
            foreach ($latestContacts as $c) {
                $mixed->push([
                    'title' => trim($c->first_name . ' ' . $c->last_name) ?: $c->phone_number,
                    'description' => 'was added as a new contact',
                    'time' => $c->created_at,
                    'icon' => 'person_add',
                    'icon_bg_class' => 'bg-emerald-50 dark:bg-emerald-900/30',
                    'icon_text_class' => 'text-emerald-600',
                ]);
            }
            foreach ($latestTemplates as $t) {
                $mixed->push([
                    'title' => 'Template "' . $t->name . '"',
                    'description' => 'was created',
                    'time' => $t->created_at,
                    'icon' => 'fact_check',
                    'icon_bg_class' => 'bg-amber-50 dark:bg-amber-900/30',
                    'icon_text_class' => 'text-amber-600',
                ]);
            }
            foreach ($latestCampaigns as $c) {
                $mixed->push([
                    'title' => 'Campaign "' . $c->name . '"',
                    'description' => 'was created',
                    'time' => $c->created_at,
                    'icon' => 'send',
                    'icon_bg_class' => 'bg-blue-50 dark:bg-blue-900/30',
                    'icon_text_class' => 'text-primary',
                ]);
            }
            
            $activities = $mixed->sortByDesc('time')->take(5)->map(function($item) {
                $item['time'] = $item['time']->diffForHumans();
                return $item;
            })->values()->toArray();
        }

        if (empty($activities)) {
            $activities = [
                [
                    'title' => 'Welcome!',
                    'description' => 'Your workspace is ready. Start by adding contacts or templates.',
                    'time' => 'Just now',
                    'icon' => 'celebration',
                    'icon_bg_class' => 'bg-purple-50 dark:bg-purple-900/30',
                    'icon_text_class' => 'text-purple-600',
                ]
            ];
        }

        return [
            'heading' => 'Dashboard Overview',
            'subheading' => "Welcome back, {$displayName}. Here's what's happening with your WhatsApp campaigns.",
            'storage' => [
                'percent' => 0,
                'label' => 'Storage tracking disabled',
                'hide' => true,
            ],
            'topbarUser' => [
                'name' => $displayName,
                'role_label' => 'Wallet: ₹' . $walletBalance,
                'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=random',
            ],
            'stats' => [
                [
                    'label' => 'Total Messages',
                    'value' => number_format($totalMessages),
                    'icon' => 'send',
                    'icon_bg_class' => 'bg-blue-50 dark:bg-blue-900/30',
                    'icon_text_class' => 'text-primary',
                    'badge' => '',
                    'badge_class' => 'hidden',
                ],
                [
                    'label' => 'Active Chats',
                    'value' => number_format($activeChats),
                    'icon' => 'forum',
                    'icon_bg_class' => 'bg-emerald-50 dark:bg-emerald-900/30',
                    'icon_text_class' => 'text-emerald-600',
                    'badge' => '24h window',
                    'badge_class' => 'bg-emerald-50 text-emerald-500 dark:bg-emerald-900/20',
                ],
                [
                    'label' => 'Total Contacts',
                    'value' => number_format($totalContacts),
                    'icon' => 'contacts',
                    'icon_bg_class' => 'bg-purple-50 dark:bg-purple-900/30',
                    'icon_text_class' => 'text-purple-600',
                    'badge' => '',
                    'badge_class' => 'hidden',
                ],
                [
                    'label' => 'Approved Templates',
                    'value' => number_format($templatesCount),
                    'icon' => 'fact_check',
                    'icon_bg_class' => 'bg-amber-50 dark:bg-amber-900/30',
                    'icon_text_class' => 'text-amber-600',
                    'badge' => '',
                    'badge_class' => 'hidden',
                ],
            ],
            'chart' => [
                'title' => 'Message Activity',
                'subtitle' => 'Total volume in the last 7 days',
                'total' => number_format($totalMessagesLast7Days),
                'change' => '',
                'change_label' => '',
                'days' => $chartDays,
                'data' => $messagesPerDay,
            ],
            'activities' => $activities,
            'campaigns' => $formattedCampaigns,
        ];
    }
}

